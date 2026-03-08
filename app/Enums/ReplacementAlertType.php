<?php

namespace App\Enums;

enum ReplacementAlertType: string
{
    case TwoYear = 'two_year';
    case OneYear = 'one_year';
    case Overdue = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::TwoYear => '2-Year Replacement Alert',
            self::OneYear => '1-Year Replacement Alert',
            self::Overdue => 'Overdue Replacement Alert',
        };
    }
}
