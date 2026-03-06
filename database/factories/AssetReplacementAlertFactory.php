<?php

namespace Database\Factories;

use App\Enums\ReplacementAlertType;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssetReplacementAlert>
 */
class AssetReplacementAlertFactory extends Factory
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
            'alert_type' => ReplacementAlertType::TwoYear,
            'sent_at' => null,
            'dismissed_at' => null,
        ];
    }

    /**
     * Indicate the alert has been sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'sent_at' => now(),
        ]);
    }

    /**
     * Indicate the alert has been sent and dismissed by the user.
     */
    public function dismissed(): static
    {
        return $this->state(fn (array $attributes) => [
            'sent_at' => now(),
            'dismissed_at' => now(),
        ]);
    }

    /**
     * Indicate the alert is an overdue replacement alert.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'alert_type' => ReplacementAlertType::Overdue,
        ]);
    }
}
