<?php

namespace Database\Factories;

use App\Enums\AssetCategory;
use App\Enums\AssetStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    /** @var list<string> */
    protected static array $names = [
        'Refrigerator', 'HVAC Unit', 'Water Heater', 'Roof Shingles',
        'Dishwasher', 'Electrical Panel', 'Garage Door Opener', 'Bathroom Tiles',
    ];

    /** @var list<string> */
    protected static array $locations = [
        'Kitchen', 'Bathroom', 'Basement', 'Garage', 'Attic', 'Living Room',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(self::$names),
            'category' => fake()->randomElement(AssetCategory::cases()),
            'location' => fake()->randomElement(self::$locations),
            'purchase_date' => fake()->boolean() ? fake()->dateTimeBetween('-10 years')->format('Y-m-d') : null,
            'install_date' => fake()->boolean() ? fake()->dateTimeBetween('-10 years')->format('Y-m-d') : null,
            'warranty_expiration_date' => fake()->boolean() ? fake()->dateTimeBetween('-10 years', '+5 years')->format('Y-m-d') : null,
            'notes' => fake()->boolean() ? fake()->paragraph() : null,
            'status' => AssetStatus::Active,
        ];
    }

    /**
     * Indicate that the asset is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AssetStatus::Archived,
        ]);
    }
}
