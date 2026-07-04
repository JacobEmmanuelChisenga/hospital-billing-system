<?php

namespace App\Enums;

enum LedgerAccountType: string
{
    case Member = 'member';
    case Company = 'company';

    public function label(): string
    {
        return match ($this) {
            self::Member => 'Member Account',
            self::Company => 'Company Account',
        };
    }
}
