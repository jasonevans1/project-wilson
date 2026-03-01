<?php

namespace Database\Factories;

use App\Enums\ServiceType;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceRecord>
 */
class ServiceRecordFactory extends Factory
{
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
            'service_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'service_type' => fake()->randomElement(ServiceType::cases())->value,
            'description' => fake()->paragraph(),
            'provider_name' => fake()->optional()->company(),
            'cost' => fake()->optional()->randomFloat(2, 0, 5000),
            'under_warranty' => false,
            'warranty_expires_on' => null,
        ];
    }

    /**
     * Indicate that the service was performed under warranty.
     */
    public function underWarranty(): static
    {
        return $this->state(fn (array $attributes) => [
            'under_warranty' => true,
            'warranty_expires_on' => fake()->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
        ]);
    }
}
