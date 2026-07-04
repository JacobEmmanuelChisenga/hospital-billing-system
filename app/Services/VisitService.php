<?php

namespace App\Services;

use App\Enums\AuditActionType;
use App\Enums\PatientStatus;
use App\Enums\VisitStatus;
use App\Enums\VisitType;
use App\Models\BillableService;
use App\Models\ChargeLine;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Registry Clerk workflow: open visits, record charges, post bills, finish visits.
 */
class VisitService
{
    public function __construct(
        private BillService $billService,
    ) {}

    public function open(Patient $patient, array $data, User $user): Visit
    {
        if ($patient->openVisit()) {
            throw new InvalidArgumentException('This patient already has an open visit.');
        }

        if ($patient->status !== PatientStatus::Active) {
            throw new InvalidArgumentException('Cannot open a visit for an inactive patient.');
        }

        // Dependants are funded by their principal — load that account before
        // deciding whether payment is still outstanding.
        $patient->loadMissing(['principalMember.membership', 'membership', 'company']);

        $visit = Visit::query()->create([
            'patient_id' => $patient->id,
            'visit_date' => $data['visit_date'],
            'visit_type' => $data['visit_type'],
            'ward_bed' => $data['ward_bed'] ?? null,
            'status' => $this->initialStatus($patient),
            'opened_by' => $user->id,
            'notes' => $data['notes'] ?? null,
        ]);

        AuditLogger::log(
            AuditActionType::VisitOpened,
            "Opened {$visit->visit_type->label()} visit for {$patient->name}.",
            $visit,
            ['patient_id' => $patient->id],
        );

        return $visit->load(['patient', 'openedBy']);
    }

    public function addCharge(Visit $visit, array $data, User $user): ChargeLine
    {
        if (! $visit->canAddCharges()) {
            throw new InvalidArgumentException('Charges can only be added after the nurse completes clinical notes.');
        }

        $service = BillableService::query()
            ->where('is_active', true)
            ->findOrFail($data['billable_service_id']);

        $line = ChargeLine::query()->create([
            'visit_id' => $visit->id,
            'billable_service_id' => $service->id,
            'category' => $service->category,
            'description' => $service->name,
            'amount' => $service->price,
            'recorded_by' => $user->id,
        ]);

        AuditLogger::log(
            AuditActionType::ChargeLineAdded,
            "Added K {$service->price} {$service->name} charge to visit #{$visit->id}.",
            $line,
            ['visit_id' => $visit->id, 'billable_service_id' => $service->id, 'amount' => $service->price],
        );

        return $line;
    }

    public function removeCharge(ChargeLine $line): void
    {
        $visit = $line->visit;

        if (! $visit || ! $visit->isOpen()) {
            throw new InvalidArgumentException('Charges can only be removed from open visits.');
        }

        AuditLogger::log(
            AuditActionType::ChargeLineRemoved,
            "Removed K {$line->amount} charge from visit #{$visit->id}.",
            $line,
            ['visit_id' => $visit->id],
        );

        $line->delete();
    }

    /**
     * Generate a bill from visit charge lines, deduct balance, and complete the visit.
     */
    public function postBill(Visit $visit, User $user, bool $confirmInsufficientBalance = false): Visit
    {
        if (! $visit->canAddCharges()) {
            throw new InvalidArgumentException('Only visits awaiting billing can be billed.');
        }

        $visit->load(['patient.company', 'patient.principalMember', 'chargeLines']);

        if ($visit->chargeLines->isEmpty()) {
            throw new InvalidArgumentException('Add at least one charge before posting the bill.');
        }

        $chargeData = $this->aggregateCharges($visit);
        $total = BillService::calculateTotal($chargeData);

        $available = (float) $visit->patient->effectiveBalance();
        if ($total > $available && ! $confirmInsufficientBalance) {
            throw new InvalidArgumentException('Insufficient balance. Confirm to proceed.');
        }

        return DB::transaction(function () use ($visit, $user, $chargeData, $total): Visit {
            $bill = $this->billService->post(
                $visit->patient,
                array_merge($chargeData, [
                    'visit_date' => Carbon::parse($visit->visit_date)->toDateString(),
                    'visit_type' => $visit->visit_type->value,
                    'ward_bed' => $visit->ward_bed,
                    'notes' => $visit->notes,
                    'visit_id' => $visit->id,
                ]),
                $user,
            );

            $visit->update([
                'status' => VisitStatus::Completed,
                'bill_id' => $bill->id,
                'completed_at' => now(),
            ]);

            AuditLogger::log(
                AuditActionType::VisitCompleted,
                "Completed visit #{$visit->id} with bill K {$total} for {$visit->patient->name}.",
                $visit,
                ['bill_id' => $bill->id, 'total' => $total],
            );

            return $visit->fresh(['patient', 'bill', 'chargeLines', 'clinicalNote']);
        });
    }

    public function cancel(Visit $visit, User $user): Visit
    {
        if (! $visit->isOpen()) {
            throw new InvalidArgumentException('Only active visits can be cancelled.');
        }

        $visit->update(['status' => VisitStatus::Cancelled]);

        AuditLogger::log(
            AuditActionType::VisitCancelled,
            "Cancelled visit #{$visit->id} for {$visit->patient->name}.",
            $visit,
        );

        return $visit->fresh();
    }

    /**
     * @return array<string, float>
     */
    private function aggregateCharges(Visit $visit): array
    {
        $totals = [
            'consultation_amount' => 0.0,
            'pharmacy_amount' => 0.0,
            'lab_amount' => 0.0,
            'ward_amount' => 0.0,
            'other_amount' => 0.0,
        ];

        foreach ($visit->chargeLines as $line) {
            $column = $line->category->billColumn();
            $totals[$column] += (float) $line->amount;
        }

        return $totals;
    }

    /**
     * Move a visit out of Awaiting Payment when coverage is already satisfied
     * (e.g. dependant covered by an active principal with balance).
     */
    public function releaseIfPaymentSatisfied(Visit $visit): Visit
    {
        if ($visit->status !== VisitStatus::AwaitingPayment) {
            return $visit;
        }

        $visit->loadMissing(['patient.principalMember.membership', 'patient.membership', 'patient.company']);

        if ($this->requiresPaymentBeforeConsultation($visit->patient)) {
            return $visit;
        }

        $visit->update(['status' => VisitStatus::ReadyForConsultation]);

        return $visit->fresh([
            'patient.principalMember.membership',
            'patient.membership',
            'patient.company',
            'chargeLines.billableService',
            'chargeLines.recordedBy',
            'clinicalNote.recordedBy',
            'bill',
            'openedBy',
        ]);
    }

    /**
     * Release every open Awaiting Payment visit funded by this member account,
     * including visits for their dependants.
     */
    public function releaseCoveredVisitsForAccount(Patient $account): void
    {
        if (! $account->isMember()) {
            return;
        }

        $account->loadMissing('membership');

        $patientIds = $account->dependants()->pluck('id')->push($account->id);

        Visit::query()
            ->whereIn('patient_id', $patientIds)
            ->where('status', VisitStatus::AwaitingPayment)
            ->get()
            ->each(fn (Visit $visit) => $this->releaseIfPaymentSatisfied($visit));
    }

    private function initialStatus(Patient $patient): VisitStatus
    {
        if ($this->requiresPaymentBeforeConsultation($patient)) {
            return VisitStatus::AwaitingPayment;
        }

        return VisitStatus::ReadyForConsultation;
    }

    /**
     * Whether the visit must wait for Accounts before the nurse can see the patient.
     *
     * Members and dependants are evaluated on the billable account (principal for
     * dependants). A dependant's own pending membership fee does not block care
     * when the principal membership is active and funded.
     */
    private function requiresPaymentBeforeConsultation(Patient $patient): bool
    {
        if ($patient->isCompanyPatient()) {
            return (float) $patient->effectiveBalance() <= 0;
        }

        $account = $patient->billableAccountPatient();

        if (! $account) {
            return true;
        }

        $account->loadMissing('membership');

        if (! $account->membershipIsActive()) {
            return true;
        }

        return (float) $patient->effectiveBalance() <= 0;
    }
}
