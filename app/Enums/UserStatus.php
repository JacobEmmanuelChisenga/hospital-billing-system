<?php

namespace App\Enums;

/**
 * Whether a staff account is allowed to sign in.
 *
 * Inactive users remain in the database for audit history but cannot log in.
 */
enum UserStatus: string
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
