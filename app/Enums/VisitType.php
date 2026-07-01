<?php

namespace App\Enums;

/**
 * Visit categories used in billing reports.
 */
enum VisitType: string
{
    case Opd = 'OPD';
    case Ipd = 'IPD';
    case Emergency = 'Emergency';

    public function label(): string
    {
        return $this->value;
    }
}
