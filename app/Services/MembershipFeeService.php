<?php

namespace App\Services;

use App\Enums\AuditActionType;
use App\Enums\MembershipStatus;
use App\Enums\VisitStatus;
use App\Models\Membership;
use App\Models\MembershipFee;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Records scheme membership payments (subscription / registration fees).
 *
 * Unlike deposits, membership payments do not add spendable balance — they
 * activate or renew a patient's membership and extend its validity date.
 */
class MembershipFeeService
{
    /**
     * Record a membership payment for a holder (member or dependant).
     */
    public function record(Patient $holder, array $data, User $user): MembershipFee
    {
        return DB::transaction(function () use ($holder, $data, $user): MembershipFee {
            // A dependant's membership is tracked under their principal member.
            $principal = $holder->isDependant() ? $holder->principalMember : null;

            $fee = MembershipFee::query()->create([
                'patient_id' => $holder->id,
                'principal_patient_id' => $principal?->id,
                'dependant_patient_id' => $holder->isDependant() ? $holder->id : null,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference' => $data['reference'] ?? null,
                'payment_date' => $data['payment_date'],
                'expiry_date' => $data['expiry_date'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            $this->extendMembership($holder, $data['expiry_date']);

            AuditLogger::log(
                AuditActionType::MembershipFeeRecorded,
                "Recorded K {$data['amount']} membership payment for {$holder->name}"
                    .($principal ? " (dependant of {$principal->name})" : '')
                    .". Membership valid until {$data['expiry_date']}.",
                $fee,
                [
                    'patient_id' => $holder->id,
                    'principal_patient_id' => $principal?->id,
                    'amount' => $data['amount'],
                    'expiry_date' => $data['expiry_date'],
                ],
            );

            return $fee;
        });
    }

    /**
     * Push the holder's membership validity forward, never backwards.
     */
    private function extendMembership(Patient $holder, string $expiryDate): void
    {
        $newExpiry = Carbon::parse($expiryDate);
        $current = $holder->membership_valid_until ? Carbon::parse($holder->membership_valid_until) : null;

        if ($current === null || $newExpiry->greaterThan($current)) {
            $holder->forceFill([
                'membership_valid_until' => $newExpiry,
                'membership_status' => MembershipStatus::Active,
            ])->save();
        } else {
            $holder->forceFill(['membership_status' => MembershipStatus::Active])->save();
        }

        if ($holder->isMember()) {
            Membership::query()->updateOrCreate(
                ['patient_id' => $holder->id],
                [
                    'membership_number' => $holder->membership?->membership_number
                        ?? $holder->hc_number
                        ?? 'HC-'.str_pad((string) $holder->id, 6, '0', STR_PAD_LEFT),
                    'status' => MembershipStatus::Active,
                    'start_date' => now()->toDateString(),
                    'expiry_date' => $newExpiry->toDateString(),
                ],
            );
        }

        $holder->visits()
            ->where('status', VisitStatus::AwaitingPayment->value)
            ->update(['status' => VisitStatus::ReadyForConsultation->value]);
    }
}
