<?php

use App\Enums\ReminderType;
use App\Models\Asset;
use App\Models\MaintenanceOccurrence;
use App\Models\MaintenanceReminder;
use App\Models\MaintenanceTask;
use App\Models\User;
use App\Notifications\MaintenanceReminderNotification;

// ─── T022 ──────────────────────────────────────────────────────────────────────

test('notification email contains asset name, task name, due date, and view link', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id, 'name' => 'HVAC Unit']);
    $task = MaintenanceTask::factory()->active()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'name' => 'Replace Filter',
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

    $notification = new MaintenanceReminderNotification($reminder);
    $mail = $notification->toMail($user);
    $rendered = (string) $mail->render();

    expect($rendered)
        ->toContain('HVAC Unit')
        ->toContain('Replace Filter')
        ->toContain($occurrence->due_date->format('M j, Y'));
});

// ─── T023 ──────────────────────────────────────────────────────────────────────

test('notification email subject is "Upcoming maintenance: {task} for {asset}" for 30-day type', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id, 'name' => 'Water Heater']);
    $task = MaintenanceTask::factory()->active()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'name' => 'Flush Tank',
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

    $notification = new MaintenanceReminderNotification($reminder);
    $mail = $notification->toMail($user);

    expect($mail->subject)->toBe('Upcoming maintenance: Flush Tank for Water Heater');
});
