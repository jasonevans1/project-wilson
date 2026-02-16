<?php

namespace Database\Factories;

use App\Enums\AssetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssetTemplate>
 */
class AssetTemplateFactory extends Factory
{
    /** @var list<string> */
    protected static array $names = [
        'Refrigerator', 'Dishwasher', 'Oven/Range', 'Microwave',
        'Water Heater', 'Furnace', 'Central Air Conditioner', 'Thermostat',
        'Garage Door Opener', 'Washing Machine', 'Dryer', 'Toilet',
    ];

    /** @var list<string> */
    protected static array $locations = [
        'Kitchen', 'Bathroom', 'Basement', 'Garage', 'Attic',
        'Living Room', 'Laundry Room', 'Bedroom', 'Exterior',
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
            'description' => fake()->boolean() ? fake()->sentence() : null,
            'category' => fake()->randomElement(AssetCategory::cases()),
            'location' => fake()->randomElement(self::$locations),
            'display_order' => 0,
        ];
    }
}
