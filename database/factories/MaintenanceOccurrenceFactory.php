<?php

namespace Database\Factories;

use App\Models\MaintenanceTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaintenanceOccurrence>
 */
class MaintenanceOccurrenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'maintenance_task_id' => MaintenanceTask::factory(),
            'due_date' => fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
            'completed_at' => null,
            'notes' => null,
        ];
    }

    /**
     * Indicate that the occurrence is pending (not yet completed).
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the occurrence has been completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the occurrence is overdue (past due, not completed).
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-3 months', '-1 day')->format('Y-m-d'),
            'completed_at' => null,
        ]);
    }
}
