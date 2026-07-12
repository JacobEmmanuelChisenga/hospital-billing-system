<?php

namespace App\Enums;

/**
 * Staff roles for the Ronald Ross High Cost workflow.
 *
 * Registry Clerk owns patient records and visit billing.
 * Consultant owns clinical notes. Accounts owns money. Administrator owns the system.
 */
enum UserRole: string
{
    case Administrator = 'administrator';
    case Accounts = 'accounts';
    case Registry = 'registry';
    case Consultant = 'consultant';

    /** @deprecated Use Registry — kept only for migration compatibility */
    case Nursing = 'nursing';

    public function label(): string
    {
        return match ($this) {
            self::Administrator => 'Administrator',
            self::Accounts => 'Accounts Officer',
            self::Registry, self::Nursing => 'Registry Clerk',
            self::Consultant => 'Consultant',
        };
    }

    /** Roles that can perform financial transactions (deposits, membership payments). */
    public static function financialOperations(): array
    {
        return [self::Accounts];
    }

    /** Roles that can view financial records and reports. */
    public static function financialViewAccess(): array
    {
        return [self::Administrator, self::Accounts];
    }

    /** Roles that can search and view patient profiles. */
    public static function patientViewAccess(): array
    {
        return [self::Administrator, self::Accounts, self::Registry, self::Consultant, self::Nursing];
    }

    /** Roles that can register and edit patient demographics. */
    public static function patientManageAccess(): array
    {
        return [self::Registry, self::Nursing];
    }

    /** Roles that can open visits, record charges, and post bills. */
    public static function visitManageAccess(): array
    {
        return [self::Registry, self::Nursing];
    }

    /** Roles that can record clinical notes on visits. */
    public static function clinicalAccess(): array
    {
        return [self::Consultant];
    }
}
