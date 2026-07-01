<?php

namespace App\Enums;

/**
 * The three patient categories used by the High Cost Section.
 *
 * Billing uses this value to decide whose balance should be charged:
 * the patient, the principal member, or a company account.
 */
enum PatientType: string
{
    case Member = 'member';
    case Dependant = 'dependant';
    case Company = 'company';

    public function label(): string
    {
        return match ($this) {
            self::Member => 'Individual Member',
            self::Dependant => 'Dependant',
            self::Company => 'Company Patient',
        };
    }
}
