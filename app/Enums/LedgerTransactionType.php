<?php

namespace App\Enums;

enum LedgerTransactionType: string
{
    case OpeningBalance = 'opening_balance';
    case Deposit = 'deposit';
    case Bill = 'bill';
    case Refund = 'refund';
    case Reversal = 'reversal';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::OpeningBalance => 'Opening Balance',
            self::Deposit => 'Deposit',
            self::Bill => 'Bill',
            self::Refund => 'Refund',
            self::Reversal => 'Reversal',
            self::Adjustment => 'Adjustment',
        };
    }
}
