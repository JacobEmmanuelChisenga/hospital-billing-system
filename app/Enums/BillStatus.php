<?php

namespace App\Enums;

/**
 * Bills are posted when they affect balances, and voided when reversed.
 */
enum BillStatus: string
{
    case Posted = 'posted';
    case Voided = 'voided';

    public function label(): string
    {
        return match ($this) {
            self::Posted => 'Posted',
            self::Voided => 'Voided',
        };
    }
}
