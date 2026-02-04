<?php

namespace App\Enums;

enum AssetCategory: string
{
    case Appliance = 'appliance';
    case Hvac = 'hvac';
    case Plumbing = 'plumbing';
    case Electrical = 'electrical';
    case Roofing = 'roofing';
    case Flooring = 'flooring';
    case Exterior = 'exterior';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Appliance => 'Appliance',
            self::Hvac => 'HVAC',
            self::Plumbing => 'Plumbing',
            self::Electrical => 'Electrical',
            self::Roofing => 'Roofing',
            self::Flooring => 'Flooring',
            self::Exterior => 'Exterior',
            self::Other => 'Other',
        };
    }
}
