<?php

use App\Enums\AssetCategory;
use App\Services\AssetLifespanDefaults;

it('returns the correct lifespan for each category', function (AssetCategory $category, int $expectedYears) {
    expect(AssetLifespanDefaults::forCategory($category))->toBe($expectedYears);
})->with([
    'Appliance' => [AssetCategory::Appliance, 10],
    'Hvac' => [AssetCategory::Hvac, 15],
    'Plumbing' => [AssetCategory::Plumbing, 20],
    'Electrical' => [AssetCategory::Electrical, 25],
    'Roofing' => [AssetCategory::Roofing, 20],
    'Flooring' => [AssetCategory::Flooring, 25],
    'Exterior' => [AssetCategory::Exterior, 15],
]);

it('returns null for AssetCategory::Other', function () {
    expect(AssetLifespanDefaults::forCategory(AssetCategory::Other))->toBeNull();
});
