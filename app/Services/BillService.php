<?php

namespace App\Services;

use App\Enums\AuditActionType;
use App\Enums\BillStatus;
use App\Enums\PatientStatus;
use App\Models\Bill;
use App\Models\Company;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Posts visit bills and voids them with correct balance adjustments.
 *
 * Members are charged on their own account. Dependants charge the principal
 * member. Company patients charge the company deposit pool.
 */
class BillService
{
    public function __construct(
        private LedgerService $ledgerService,
    ) {}

    /**
     * Sum itemised charge fields into a bill total.
     */
    public static function calculateTotal(array $data): float
    {
        return round(
            (float) ($data['consultation_amount'] ?? 0)
            + (float) ($data['pharmacy_amount'] ?? 0)
            + (float) ($data['lab_amount'] ?? 0)
            + (float) ($data['ward_amount'] ?? 0)
            + (float) ($data['other_amount'] ?? 0),
            2
        );
    }

    public function post(Patient $patient, array $data, User $user): Bill
    {
        $total = self::calculateTotal($data);

        if ($total <= 0) {
            throw new InvalidArgumentException('Bill total must be greater than zero.');
        }

        return DB::transaction(function () use ($patient, $data, $user, $total): Bill {
            $lockedPatient = Patient::query()
                ->with(['company', 'principalMember'])
                ->lockForUpdate()
                ->findOrFail($patient->id);

            if ($lockedPatient->status !== PatientStatus::Active) {
                throw new InvalidArgumentException('Cannot bill an inactive patient.');
            }

            $accountPatientId = null;
            $companyId = null;

            if ($lockedPatient->isCompanyPatient()) {
                if (! $lockedPatient->company_id) {
                    throw new InvalidArgumentException('Company patient is not linked to a company account.');
                }

                $company = Company::query()->lockForUpdate()->findOrFail($lockedPatient->company_id);
                $company->decrement('balance', $total);
                $companyId = $company->id;
            } else {
                $accountPatient = $lockedPatient->billableAccountPatient();

                if (! $accountPatient) {
                    throw new InvalidArgumentException('No billable member account found for this patient.');
                }

                $lockedAccount = Patient::query()->lockForUpdate()->findOrFail($accountPatient->id);
                $lockedAccount->decrement('balance', $total);
                $accountPatientId = $lockedAccount->id;
            }

            $bill = Bill::query()->create([
                'patient_id' => $lockedPatient->id,
                'account_patient_id' => $accountPatientId,
                'company_id' => $companyId,
                'visit_id' => $data['visit_id'] ?? null,
                'visit_date' => $data['visit_date'],
                'visit_type' => $data['visit_type'],
                'ward_bed' => $data['ward_bed'] ?? null,
                'consultation_amount' => $data['consultation_amount'] ?? 0,
                'pharmacy_amount' => $data['pharmacy_amount'] ?? 0,
                'lab_amount' => $data['lab_amount'] ?? 0,
                'ward_amount' => $data['ward_amount'] ?? 0,
                'other_amount' => $data['other_amount'] ?? 0,
                'total_amount' => $total,
                'notes' => $data['notes'] ?? null,
                'status' => BillStatus::Posted,
                'created_by' => $user->id,
            ]);

            $bill = $bill->load(['patient', 'accountPatient', 'company', 'createdBy']);
            $this->ledgerService->recordBill($bill, $user);

            AuditLogger::log(
                AuditActionType::BillCreated,
                "Posted K {$total} bill for {$lockedPatient->name} ({$data['visit_type']}).",
                $bill,
                ['patient_id' => $lockedPatient->id, 'total' => $total],
            );

            return $bill;
        });
    }

    public function void(Bill $bill, string $reason, User $user): Bill
    {
        if ($bill->isVoided()) {
            throw new InvalidArgumentException('This bill has already been voided.');
        }

        return DB::transaction(function () use ($bill, $reason, $user): Bill {
            $lockedBill = Bill::query()->lockForUpdate()->findOrFail($bill->id);

            if ($lockedBill->isVoided()) {
                throw new InvalidArgumentException('This bill has already been voided.');
            }

            if ($lockedBill->company_id) {
                $company = Company::query()->lockForUpdate()->findOrFail($lockedBill->company_id);
                $company->increment('balance', $lockedBill->total_amount);
            } elseif ($lockedBill->account_patient_id) {
                $accountPatient = Patient::query()->lockForUpdate()->findOrFail($lockedBill->account_patient_id);
                $accountPatient->increment('balance', $lockedBill->total_amount);
            }

            $lockedBill->update([
                'status' => BillStatus::Voided,
                'void_reason' => $reason,
                'voided_at' => now(),
                'voided_by' => $user->id,
            ]);

            $voided = $lockedBill->fresh(['patient', 'accountPatient', 'company', 'createdBy', 'voidedBy']);
            $this->ledgerService->recordBillVoid($voided, $user, $reason);

            $patientName = $voided->patient?->name ?? 'Unknown patient';

            AuditLogger::log(
                AuditActionType::BillVoided,
                "Voided K {$voided->total_amount} bill for {$patientName}. Reason: {$reason}",
                $voided,
                ['bill_id' => $voided->id, 'total' => $voided->total_amount],
            );

            return $voided;
        });
    }
}
