<?php

namespace App\Services;

use App\Enums\AuditActionType;
use App\Enums\VisitStatus;
use App\Models\Company;
use App\Models\CompanyDeposit;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Handles company pool deposits and reversals.
 *
 * Company patients share one balance per company — deposits increase that pool.
 */
class CompanyDepositService
{
    public function __construct(
        private LedgerService $ledgerService,
    ) {}

    public function record(Company $company, array $data, User $user): CompanyDeposit
    {
        return DB::transaction(function () use ($company, $data, $user): CompanyDeposit {
            $lockedCompany = Company::query()->lockForUpdate()->findOrFail($company->id);

            $deposit = CompanyDeposit::query()->create([
                'company_id' => $lockedCompany->id,
                'amount' => $data['amount'],
                'deposit_date' => $data['deposit_date'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            $lockedCompany->increment('balance', $data['amount']);

            $deposit = $deposit->load(['company', 'createdBy']);
            $this->ledgerService->recordCompanyDeposit($deposit, $user);

            $lockedCompany->patients()
                ->each(fn (Patient $patient) => $patient->visits()
                    ->where('status', VisitStatus::AwaitingPayment->value)
                    ->update(['status' => VisitStatus::ReadyForConsultation->value]));

            AuditLogger::log(
                AuditActionType::CompanyDepositCreated,
                "Loaded K {$data['amount']} into {$lockedCompany->name} company account.",
                $deposit,
                ['company_id' => $lockedCompany->id, 'amount' => $data['amount']],
            );

            return $deposit;
        });
    }

    public function reverse(CompanyDeposit $deposit, string $reason, User $user): CompanyDeposit
    {
        if ($deposit->isReversed()) {
            throw new InvalidArgumentException('This deposit has already been reversed.');
        }

        return DB::transaction(function () use ($deposit, $reason, $user): CompanyDeposit {
            $lockedDeposit = CompanyDeposit::query()->lockForUpdate()->findOrFail($deposit->id);

            if ($lockedDeposit->isReversed()) {
                throw new InvalidArgumentException('This deposit has already been reversed.');
            }

            $lockedCompany = Company::query()->lockForUpdate()->findOrFail($lockedDeposit->company_id);

            $lockedDeposit->update([
                'reversed_at' => now(),
                'reversed_by' => $user->id,
                'reversal_reason' => $reason,
            ]);

            $lockedCompany->decrement('balance', (float) $lockedDeposit->amount);

            $reversed = $lockedDeposit->fresh(['company', 'createdBy', 'reversedBy']);
            $this->ledgerService->recordCompanyDepositReversal($reversed, $user, $reason);

            AuditLogger::log(
                AuditActionType::CompanyDepositReversed,
                "Reversed K {$reversed->amount} from {$lockedCompany->name}. Reason: {$reason}",
                $reversed,
                ['company_id' => $lockedCompany->id, 'amount' => $reversed->amount],
            );

            return $reversed;
        });
    }
}
