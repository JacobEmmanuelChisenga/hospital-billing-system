<?php

namespace App\Enums;

/**
 * Patient records are kept even after deactivation so old bills,
 * deposits, and audit entries still point to a real patient record.
 */
enum PatientStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        };
    }
}
