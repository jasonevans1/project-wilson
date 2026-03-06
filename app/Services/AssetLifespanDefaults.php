<?php

namespace App\Services;

use App\Enums\AssetCategory;

class AssetLifespanDefaults
{
    /**
     * Return the default expected lifespan in years for the given asset category,
     * or null if no industry standard applies.
     */
    public static function forCategory(AssetCategory $category): ?int
    {
        return match ($category) {
            AssetCategory::Appliance => 10,
            AssetCategory::Hvac => 15,
            AssetCategory::Plumbing => 20,
            AssetCategory::Electrical => 25,
            AssetCategory::Roofing => 20,
            AssetCategory::Flooring => 25,
            AssetCategory::Exterior => 15,
            AssetCategory::Other => null,
        };
    }
}
