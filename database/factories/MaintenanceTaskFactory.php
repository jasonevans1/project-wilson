<?php

namespace Database\Factories;

use App\Enums\RecurrenceUnit;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaintenanceTask>
 */
class MaintenanceTaskFactory extends Factory
{
    /** @var list<string> */
    protected static array $taskNames = [
        'Change HVAC filter', 'Clean gutters', 'Inspect roof', 'Service water heater',
        'Test smoke detectors', 'Flush water heater', 'Check weatherstripping',
        'Clean dryer vent', 'Inspect sump pump', 'Lubricate garage door',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'asset_id' => Asset::factory(),
            'name' => fake()->randomElement(self::$taskNames),
            'description' => fake()->boolean() ? fake()->sentence() : null,
            'recurrence_unit' => fake()->randomElement(RecurrenceUnit::cases()),
            'recurrence_count' => fake()->numberBetween(1, 6),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the task is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the task is deactivated.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
