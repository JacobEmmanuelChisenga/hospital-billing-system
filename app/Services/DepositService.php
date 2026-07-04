<?php

namespace App\Services;

use App\Enums\AuditActionType;
use App\Models\Deposit;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Handles member deposit loading and reversals.
 *
 * Every deposit updates the member balance inside a database transaction
 * so the balance and deposit record always stay in sync.
 */
class DepositService
{
    public function __construct(
        private VisitService $visitService,
        private LedgerService $ledgerService,
    ) {}

    public function record(Patient $patient, array $data, User $user): Deposit
    {
        if (! $patient->isMember()) {
            throw new InvalidArgumentException('Deposits can only be loaded into member accounts.');
        }

        return DB::transaction(function () use ($patient, $data, $user): Deposit {
            $lockedPatient = Patient::query()->lockForUpdate()->findOrFail($patient->id);

            $deposit = Deposit::query()->create([
                'patient_id' => $lockedPatient->id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? null,
                'deposit_date' => $data['deposit_date'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            $lockedPatient->increment('balance', $data['amount']);

            $deposit = $deposit->load(['patient', 'createdBy']);
            $this->ledgerService->recordMemberDeposit($deposit, $user);

            $this->visitService->releaseCoveredVisitsForAccount($lockedPatient->fresh(['membership']));

            AuditLogger::log(
                AuditActionType::DepositCreated,
                "Loaded K {$data['amount']} deposit for {$lockedPatient->name}.",
                $deposit,
                ['patient_id' => $lockedPatient->id, 'amount' => $data['amount']],
            );

            return $deposit;
        });
    }

    public function reverse(Deposit $deposit, string $reason, User $user): Deposit
    {
        if ($deposit->isReversed()) {
            throw new InvalidArgumentException('This deposit has already been reversed.');
        }

        return DB::transaction(function () use ($deposit, $reason, $user): Deposit {
            $lockedDeposit = Deposit::query()->lockForUpdate()->findOrFail($deposit->id);

            if ($lockedDeposit->isReversed()) {
                throw new InvalidArgumentException('This deposit has already been reversed.');
            }

            $lockedPatient = Patient::query()->lockForUpdate()->findOrFail($lockedDeposit->patient_id);

            $lockedDeposit->update([
                'reversed_at' => now(),
                'reversed_by' => $user->id,
                'reversal_reason' => $reason,
            ]);

            $lockedPatient->decrement('balance', (float) $lockedDeposit->amount);

            $reversed = $lockedDeposit->fresh(['patient', 'createdBy', 'reversedBy']);
            $this->ledgerService->recordMemberDepositReversal($reversed, $user, $reason);

            AuditLogger::log(
                AuditActionType::DepositReversed,
                "Reversed K {$reversed->amount} deposit for {$lockedPatient->name}. Reason: {$reason}",
                $reversed,
                ['patient_id' => $lockedPatient->id, 'amount' => $reversed->amount],
            );

            return $reversed;
        });
    }
}
