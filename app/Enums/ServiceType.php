<?php

namespace App\Enums;

enum ServiceType: string
{
    case Maintenance = 'maintenance';
    case Repair = 'repair';
    case Inspection = 'inspection';
    case Replacement = 'replacement';

    public function label(): string
    {
        return match ($this) {
            self::Maintenance => 'Maintenance',
            self::Repair => 'Repair',
            self::Inspection => 'Inspection',
            self::Replacement => 'Replacement',
        };
    }
}
