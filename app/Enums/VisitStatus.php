<?php

namespace App\Enums;

enum VisitStatus: string
{
    case Registered = 'registered';
    case AwaitingPayment = 'awaiting_payment';
    case ReadyForConsultation = 'ready_for_consultation';
    case SeenByNurse = 'seen_by_nurse';
    case AwaitingBilling = 'awaiting_billing';
    case Billed = 'billed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Registered => 'Registered',
            self::AwaitingPayment => 'Awaiting Payment',
            self::ReadyForConsultation => 'Ready for Consultation',
            self::SeenByNurse => 'Seen by Nurse',
            self::AwaitingBilling => 'Awaiting Billing',
            self::Billed => 'Billed',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Registered => 'bg-blue-100 text-blue-800',
            self::AwaitingPayment => 'bg-amber-100 text-amber-800',
            self::ReadyForConsultation => 'bg-indigo-100 text-indigo-800',
            self::SeenByNurse => 'bg-purple-100 text-purple-800',
            self::AwaitingBilling => 'bg-orange-100 text-orange-800',
            self::Billed => 'bg-amber-100 text-amber-800',
            self::Completed => 'bg-green-100 text-green-800',
            self::Cancelled => 'bg-gray-100 text-gray-800',
        };
    }

    public function isActive(): bool
    {
        return ! in_array($this, [self::Completed, self::Cancelled], true);
    }
}
