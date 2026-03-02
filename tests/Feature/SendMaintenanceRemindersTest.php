<?php

use App\Enums\RecurrenceUnit;
use App\Enums\ReminderType;
use App\Models\Asset;
use App\Models\MaintenanceOccurrence;
use App\Models\MaintenanceReminder;
use App\Models\MaintenanceTask;
use App\Models\User;
use App\Notifications\MaintenanceDigestNotification;
use App\Notifications\MaintenanceReminderNotification;
use App\Services\MaintenanceScheduler;
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

// ─── T029 ──────────────────────────────────────────────────────────────────────

test('it sends a digest email when user has 3 reminders on the same day', function () {
    Notification::fake();

    $user = User::factory()->create();

    // Three separate assets/tasks/occurrences all due in 30 days for the same user
    foreach (range(1, 3) as $i) {
        $asset = Asset::factory()->create(['user_id' => $user->id]);
        $task = MaintenanceTask::factory()->active()->create(['user_id' => $user->id, 'asset_id' => $asset->id]);
        MaintenanceOccurrence::factory()->pending()->create([
            'maintenance_task_id' => $task->id,
            'due_date' => today()->addDays(30),
        ]);
    }

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    Notification::assertSentTo($user, MaintenanceDigestNotification::class);
    Notification::assertNotSentTo($user, MaintenanceReminderNotification::class);
});

// ─── T030 ──────────────────────────────────────────────────────────────────────

test('it sends a single-task email when user has only 1 reminder', function () {
    Notification::fake();

    ['user' => $user] = makeOccurrence(30);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    Notification::assertSentTo($user, MaintenanceReminderNotification::class);
    Notification::assertNotSentTo($user, MaintenanceDigestNotification::class);
});

// ─── T037 ──────────────────────────────────────────────────────────────────────

test('it sends a reminder for the next pending occurrence of a recurring task after the previous occurrence is completed', function () {
    Notification::fake();

    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->active()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'recurrence_unit' => RecurrenceUnit::Monthly,
        'recurrence_count' => 3,
    ]);

    // Simulate completed occurrence — the scheduler creates the next one
    $scheduler = new MaintenanceScheduler;
    $completedOccurrence = MaintenanceOccurrence::factory()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->subDays(5),
        'completed_at' => null,
    ]);
    $nextOccurrence = $scheduler->completeOccurrence($completedOccurrence);

    // Manually set next occurrence due_date to 30 days from now
    $nextOccurrence->update(['due_date' => today()->addDays(30)]);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    Notification::assertSentTo($user, MaintenanceReminderNotification::class, function ($notification) use ($nextOccurrence) {
        return $notification->reminder->maintenance_occurrence_id === $nextOccurrence->id;
    });
});

// ─── T038 ──────────────────────────────────────────────────────────────────────

test('it does not send reminders for inactive recurring tasks', function () {
    Notification::fake();

    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->inactive()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'recurrence_unit' => RecurrenceUnit::Monthly,
        'recurrence_count' => 1,
    ]);
    MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->addDays(30),
    ]);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    Notification::assertNothingSent();
});

// ─── T041 ──────────────────────────────────────────────────────────────────────

test('it sends a 7-day escalation reminder for an uncompleted occurrence due in 7 days', function () {
    Notification::fake();

    ['user' => $user, 'occurrence' => $occurrence] = makeOccurrence(7);

    // ThirtyDay already sent so only SevenDay is pending
    MaintenanceReminder::factory()->sent()->create([
        'user_id' => $user->id,
        'maintenance_occurrence_id' => $occurrence->id,
        'reminder_type' => ReminderType::ThirtyDay,
    ]);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    Notification::assertSentTo($user, MaintenanceReminderNotification::class, function ($notification) {
        return $notification->reminder->reminder_type === ReminderType::SevenDay;
    });
});

// ─── T042 ──────────────────────────────────────────────────────────────────────

test('it sends a 1-day escalation reminder for an uncompleted occurrence due in 1 day', function () {
    Notification::fake();

    ['user' => $user, 'occurrence' => $occurrence] = makeOccurrence(1);

    // ThirtyDay and SevenDay already sent so only OneDay is pending
    MaintenanceReminder::factory()->sent()->create([
        'user_id' => $user->id,
        'maintenance_occurrence_id' => $occurrence->id,
        'reminder_type' => ReminderType::ThirtyDay,
    ]);
    MaintenanceReminder::factory()->sent()->create([
        'user_id' => $user->id,
        'maintenance_occurrence_id' => $occurrence->id,
        'reminder_type' => ReminderType::SevenDay,
    ]);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    Notification::assertSentTo($user, MaintenanceReminderNotification::class, function ($notification) {
        return $notification->reminder->reminder_type === ReminderType::OneDay;
    });
});

// ─── T043 ──────────────────────────────────────────────────────────────────────

test('it does not send escalation reminders for completed occurrences', function () {
    Notification::fake();

    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->active()->create(['user_id' => $user->id, 'asset_id' => $asset->id]);
    MaintenanceOccurrence::factory()->completed()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->addDays(7),
    ]);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    Notification::assertNothingSent();
});

// ─── T046 ──────────────────────────────────────────────────────────────────────

test('it sends separate reminder records for 30-day and 7-day on different days for the same occurrence', function () {
    Notification::fake();

    ['user' => $user, 'occurrence' => $occurrence] = makeOccurrence(7);

    // Simulate ThirtyDay reminder having been sent 23 days ago
    MaintenanceReminder::factory()->sent()->create([
        'user_id' => $user->id,
        'maintenance_occurrence_id' => $occurrence->id,
        'reminder_type' => ReminderType::ThirtyDay,
    ]);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    // Both records should exist now
    expect(MaintenanceReminder::where('maintenance_occurrence_id', $occurrence->id)->count())->toBe(2);

    // SevenDay record should have been created and marked sent
    $sevenDayReminder = MaintenanceReminder::where('maintenance_occurrence_id', $occurrence->id)
        ->where('reminder_type', ReminderType::SevenDay)
        ->first();

    expect($sevenDayReminder)->not->toBeNull();
    expect($sevenDayReminder->sent_at)->not->toBeNull();
});

// ─── T057 ──────────────────────────────────────────────────────────────────────

test('the daily command resends a reminder when snoozed_until date arrives', function () {
    Notification::fake();

    ['user' => $user, 'occurrence' => $occurrence] = makeOccurrence(30);

    // Reminder was previously sent, then snoozed; snoozed_until is today
    MaintenanceReminder::factory()->create([
        'user_id' => $user->id,
        'maintenance_occurrence_id' => $occurrence->id,
        'reminder_type' => ReminderType::ThirtyDay,
        'sent_at' => null,
        'snoozed_until' => today(),
        'snooze_count' => 1,
    ]);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    Notification::assertSentTo($user, MaintenanceReminderNotification::class);
});

// ─── T058 ──────────────────────────────────────────────────────────────────────

test('the daily command does not resend a snoozed reminder if snoozed_until is on or after due date', function () {
    Notification::fake();

    // Occurrence due in 15 days (only in ThirtyDay window); snoozed until the due date itself
    ['user' => $user, 'occurrence' => $occurrence] = makeOccurrence(15);

    MaintenanceReminder::factory()->create([
        'user_id' => $user->id,
        'maintenance_occurrence_id' => $occurrence->id,
        'reminder_type' => ReminderType::ThirtyDay,
        'sent_at' => null,
        'snoozed_until' => today()->addDays(15),
        'snooze_count' => 1,
    ]);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    Notification::assertNothingSent();
});

// ─── T069 ──────────────────────────────────────────────────────────────────────

test('an occurrence due within 7 days receives all applicable interval reminders on first run', function () {
    Notification::fake();

    // Due in 5 days — falls within ThirtyDay and SevenDay windows, but not OneDay
    ['user' => $user, 'occurrence' => $occurrence] = makeOccurrence(5);

    $this->artisan('maintenance:send-reminders')->assertSuccessful();

    // Two reminders for one user → digest
    Notification::assertSentTo($user, MaintenanceDigestNotification::class);

    expect(MaintenanceReminder::where('maintenance_occurrence_id', $occurrence->id)->count())->toBe(2);

    $sevenDay = MaintenanceReminder::where('maintenance_occurrence_id', $occurrence->id)
        ->where('reminder_type', ReminderType::SevenDay)
        ->first();

    expect($sevenDay)->not->toBeNull();
    expect($sevenDay->sent_at)->not->toBeNull();
});
