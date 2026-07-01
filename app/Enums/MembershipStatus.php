<?php

namespace App\Enums;

/**
 * Scheme membership standing for members and dependants.
 *
 * Registry Clerk registers with PendingPayment; Accounts activates on fee receipt.
 */
enum MembershipStatus: string
{
    case PendingPayment = 'pending_payment';
    case Active = 'active';
    case Expired = 'expired';
    case NotApplicable = 'not_applicable';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Pending Payment',
            self::Active => 'Active',
            self::Expired => 'Expired',
            self::NotApplicable => 'N/A',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PendingPayment => 'bg-amber-100 text-amber-800',
            self::Active => 'bg-green-100 text-green-800',
            self::Expired => 'bg-red-100 text-red-800',
            self::NotApplicable => 'bg-gray-100 text-gray-700',
        };
    }
}
