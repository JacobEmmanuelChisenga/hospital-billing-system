<?php

namespace App\Enums;

/**
 * How money was received for a deposit or membership payment.
 *
 * Stored on the transaction and printed on the receipt so the cash
 * book can be reconciled by payment channel.
 */
enum PaymentMethod: string
{
    case Cash = 'cash';
    case MobileMoney = 'mobile_money';
    case BankTransfer = 'bank_transfer';
    case Cheque = 'cheque';
    case Card = 'card';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::MobileMoney => 'Mobile Money',
            self::BankTransfer => 'Bank Transfer',
            self::Cheque => 'Cheque',
            self::Card => 'Card / POS',
        };
    }

    /** Options for select inputs, keyed by stored value. */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            static function (array $carry, self $method): array {
                $carry[$method->value] = $method->label();

                return $carry;
            },
            [],
        );
    }
}
