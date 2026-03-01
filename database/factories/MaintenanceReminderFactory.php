<?php

namespace Database\Factories;

use App\Enums\ReminderType;
use App\Models\MaintenanceOccurrence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaintenanceReminder>
 */
class MaintenanceReminderFactory extends Factory
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
            'maintenance_occurrence_id' => MaintenanceOccurrence::factory(),
            'reminder_type' => fake()->randomElement(ReminderType::cases()),
            'sent_at' => null,
            'snoozed_until' => null,
            'snooze_count' => 0,
        ];
    }

    /**
     * Indicate that the reminder has been sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'sent_at' => now(),
        ]);
    }

    /**
     * Indicate that the reminder has been snoozed.
     */
    public function snoozed(int $days = 7): static
    {
        return $this->state(fn (array $attributes) => [
            'sent_at' => null,
            'snoozed_until' => today()->addDays($days),
            'snooze_count' => fake()->numberBetween(1, 3),
        ]);
    }

    /**
     * Indicate that the reminder is pending (not sent, not snoozed).
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'sent_at' => null,
            'snoozed_until' => null,
            'snooze_count' => 0,
        ]);
    }
}
