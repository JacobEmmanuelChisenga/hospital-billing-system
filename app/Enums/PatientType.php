<?php

namespace App\Enums;

/**
 * Patient categories used by the High Cost Section.
 *
 * Billing uses this value to decide whose balance should be charged:
 * the patient, the principal member, a company account, or immediate cash.
 */
enum PatientType: string
{
    case Member = 'member';
    case Dependant = 'dependant';
    case Company = 'company';
    case CashPatient = 'cash_patient';

    public function label(): string
    {
        return match ($this) {
            self::Member => 'Individual Member',
            self::Dependant => 'Dependant',
            self::Company => 'Company Patient',
            self::CashPatient => 'Casual Caller',
        };
    }

    /** Whether this patient type maintains a prepaid deposit balance. */
    public function hasDepositBalance(): bool
    {
        return $this === self::Member;
    }

    /** Whether bills must be settled in cash at the point of payment. */
    public function paysImmediately(): bool
    {
        return $this === self::CashPatient;
    }
}
