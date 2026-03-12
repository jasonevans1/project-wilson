<?php

namespace Database\Seeders;

use App\Enums\RecurrenceUnit;
use App\Models\Asset;
use App\Models\MaintenanceOccurrence;
use App\Models\MaintenanceTask;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dishwasher = Asset::where('name', 'Dishwasher')->first();

        if (! $dishwasher) {
            return;
        }

        $task = MaintenanceTask::factory()->create([
            'user_id' => $dishwasher->user_id,
            'asset_id' => $dishwasher->id,
            'name' => 'Cleaning',
            'recurrence_unit' => RecurrenceUnit::Monthly,
            'recurrence_count' => 3,
            'is_active' => true,
        ]);

        MaintenanceOccurrence::factory()->create([
            'maintenance_task_id' => $task->id,
            'due_date' => '2026-03-05',
            'completed_at' => null,
        ]);
    }
}
