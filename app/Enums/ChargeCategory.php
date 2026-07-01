<?php

namespace App\Enums;

enum ChargeCategory: string
{
    case Consultation = 'consultation';
    case Lab = 'lab';
    case Pharmacy = 'pharmacy';
    case Procedure = 'procedure';
    case Ward = 'ward';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Consultation => 'Consultation',
            self::Lab => 'Laboratory',
            self::Pharmacy => 'Pharmacy / Medicine',
            self::Procedure => 'Procedure',
            self::Ward => 'Ward / Bed',
            self::Other => 'Other',
        };
    }

    /** Map charge category to the legacy bill amount column name. */
    public function billColumn(): string
    {
        return match ($this) {
            self::Consultation => 'consultation_amount',
            self::Lab => 'lab_amount',
            self::Pharmacy => 'pharmacy_amount',
            self::Procedure => 'other_amount',
            self::Ward => 'ward_amount',
            self::Other => 'other_amount',
        };
    }

    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            static function (array $carry, self $category): array {
                $carry[$category->value] = $category->label();

                return $carry;
            },
            [],
        );
    }
}
