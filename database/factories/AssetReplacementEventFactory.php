<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssetReplacementEvent>
 */
class AssetReplacementEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'installed_at' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'cost' => null,
            'expected_lifespan_years' => null,
            'notes' => null,
        ];
    }

    /**
     * Indicate that the replacement event has a recorded cost.
     */
    public function withCost(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost' => fake()->randomFloat(2, 500, 15000),
        ]);
    }

    /**
     * Indicate that the replacement event has notes recorded.
     */
    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => fake()->sentence(),
        ]);
    }
}
