<?php

use App\Enums\ReminderType;
use App\Models\Asset;
use App\Models\MaintenanceOccurrence;
use App\Models\MaintenanceReminder;
use App\Models\MaintenanceTask;
use App\Models\User;
use App\Notifications\MaintenanceReminderNotification;
use Illuminate\Support\Facades\Notification;

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Create a verified user with an asset, active task, and pending occurrence.
 *
 * @return array{user: User, asset: Asset, task: MaintenanceTask, occurrence: MaintenanceOccurrence}
 */
function makeOccurrence(int $daysUntilDue, bool $taskActive = true, bool $emailVerified = true): array
{
    $user = User::factory()->when(! $emailVerified, fn ($f) => $f->unverified())->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'is_active' => $taskActive,
    ]);
    $occurrence = MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->addDays($daysUntilDue),
    ]);

    return compact('user', 'asset', 'task', 'occurrence');
}

// ─── T015 ──────────────────────────────────────────────────────────────────────

test('it sends a 30-day reminder for an active occurrence due in exactly 30 days', function () {
    Notification::fake();

    ['user' => $user] = makeOccurrence(30);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    Notification::assertSentTo($user, MaintenanceReminderNotification::class);
});

// ─── T016 ──────────────────────────────────────────────────────────────────────

test('it skips completed occurrences', function () {
    Notification::fake();

    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->active()->create(['user_id' => $user->id, 'asset_id' => $asset->id]);
    MaintenanceOccurrence::factory()->completed()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->addDays(30),
    ]);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    Notification::assertNothingSent();
});

// ─── T017 ──────────────────────────────────────────────────────────────────────

test('it skips occurrences due in 31 days', function () {
    Notification::fake();

    makeOccurrence(31);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    Notification::assertNothingSent();
});

// ─── T018 ──────────────────────────────────────────────────────────────────────

test('it skips occurrences for inactive maintenance tasks', function () {
    Notification::fake();

    makeOccurrence(30, taskActive: false);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    Notification::assertNothingSent();
});

// ─── T019 ──────────────────────────────────────────────────────────────────────

test('it skips users without verified email', function () {
    Notification::fake();

    makeOccurrence(30, emailVerified: false);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    Notification::assertNothingSent();
});

// ─── T020 ──────────────────────────────────────────────────────────────────────

test('it does not send duplicate reminders for the same occurrence and interval', function () {
    Notification::fake();

    ['user' => $user, 'occurrence' => $occurrence] = makeOccurrence(30);

    // Pre-existing sent reminder record for this occurrence + type
    MaintenanceReminder::factory()->sent()->create([
        'user_id' => $user->id,
        'maintenance_occurrence_id' => $occurrence->id,
        'reminder_type' => ReminderType::ThirtyDay,
    ]);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    Notification::assertNothingSent();
});

// ─── T021 ──────────────────────────────────────────────────────────────────────

test('it catches up on missed notifications when due_date minus 30 was yesterday', function () {
    Notification::fake();

    // due_date - 30 = yesterday, so the notification window was yesterday
    ['user' => $user] = makeOccurrence(29);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    Notification::assertSentTo($user, MaintenanceReminderNotification::class);
});
