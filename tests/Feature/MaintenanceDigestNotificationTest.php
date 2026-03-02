<?php

use App\Enums\ReminderType;
use App\Models\Asset;
use App\Models\MaintenanceOccurrence;
use App\Models\MaintenanceReminder;
use App\Models\MaintenanceTask;
use App\Models\User;
use App\Notifications\MaintenanceDigestNotification;

// ─── T031 ──────────────────────────────────────────────────────────────────────

test('digest notification contains all task names, asset names, and due dates', function () {
    $user = User::factory()->create();

    $reminders = collect();

    foreach (['Boiler Service', 'Gutter Clean', 'Roof Inspect'] as $taskName) {
        $asset = Asset::factory()->create(['user_id' => $user->id, 'name' => "Asset for {$taskName}"]);
        $task = MaintenanceTask::factory()->active()->create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'name' => $taskName,
        ]);
        $occurrence = MaintenanceOccurrence::factory()->pending()->create([
            'maintenance_task_id' => $task->id,
            'due_date' => today()->addDays(30),
        ]);
        $reminder = MaintenanceReminder::factory()->pending()->create([
            'user_id' => $user->id,
            'maintenance_occurrence_id' => $occurrence->id,
            'reminder_type' => ReminderType::ThirtyDay,
        ]);
        $reminder->load('occurrence.task.asset');
        $reminders->push($reminder);
    }

    $notification = new MaintenanceDigestNotification($reminders);
    $mail = $notification->toMail($user);
    $rendered = (string) $mail->render();

    expect($rendered)
        ->toContain('Boiler Service')
        ->toContain('Gutter Clean')
        ->toContain('Roof Inspect')
        ->toContain('Asset for Boiler Service')
        ->toContain($reminders->first()->occurrence->due_date->format('M j, Y'));
});

// ─── T032 ──────────────────────────────────────────────────────────────────────

test('digest email subject is "Wilson: You have {count} upcoming maintenance tasks"', function () {
    $user = User::factory()->create();

    $reminders = collect();

    foreach (range(1, 3) as $i) {
        $asset = Asset::factory()->create(['user_id' => $user->id]);
        $task = MaintenanceTask::factory()->active()->create(['user_id' => $user->id, 'asset_id' => $asset->id]);
        $occurrence = MaintenanceOccurrence::factory()->pending()->create([
            'maintenance_task_id' => $task->id,
            'due_date' => today()->addDays(30),
        ]);
        $reminder = MaintenanceReminder::factory()->pending()->create([
            'user_id' => $user->id,
            'maintenance_occurrence_id' => $occurrence->id,
            'reminder_type' => ReminderType::ThirtyDay,
        ]);
        $reminder->load('occurrence.task.asset');
        $reminders->push($reminder);
    }

    $notification = new MaintenanceDigestNotification($reminders);
    $mail = $notification->toMail($user);

    expect($mail->subject)->toBe('Wilson: You have 3 upcoming maintenance tasks');
});
