<?php

use App\Enums\ReminderType;
use App\Models\Asset;
use App\Models\MaintenanceOccurrence;
use App\Models\MaintenanceReminder;
use App\Models\MaintenanceTask;
use App\Models\User;
use Illuminate\Support\Facades\URL;

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Build a signed snooze URL for a given reminder and number of days.
 */
function signedSnoozeUrl(MaintenanceReminder $reminder, int $days): string
{
    return URL::signedRoute('maintenance.reminder.snooze', [
        'reminder' => $reminder->id,
        'days' => $days,
    ]);
}

/**
 * Create a sent reminder with all relationships for snooze testing.
 *
 * @return array{user: User, occurrence: MaintenanceOccurrence, reminder: MaintenanceReminder}
 */
function makeSentReminder(int $daysUntilDue = 30): array
{
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->active()->create(['user_id' => $user->id, 'asset_id' => $asset->id]);
    $occurrence = MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->addDays($daysUntilDue),
    ]);
    $reminder = MaintenanceReminder::factory()->sent()->create([
        'user_id' => $user->id,
        'maintenance_occurrence_id' => $occurrence->id,
        'reminder_type' => ReminderType::ThirtyDay,
    ]);

    return compact('user', 'occurrence', 'reminder');
}

// ─── T052 ──────────────────────────────────────────────────────────────────────

test('it snoozes a reminder for 7 days via signed URL and redirects to confirmation page', function () {
    ['reminder' => $reminder] = makeSentReminder(30);

    $url = signedSnoozeUrl($reminder, 7);

    $this->get($url)
        ->assertRedirectToRoute('maintenance.reminder.snoozed');

    $reminder->refresh();
    expect($reminder->snoozed_until->equalTo(today()->addDays(7)))->toBeTrue();
    expect($reminder->sent_at)->toBeNull();
});

// ─── T053 ──────────────────────────────────────────────────────────────────────

test('it snoozes a reminder for 3 days via signed URL', function () {
    ['reminder' => $reminder] = makeSentReminder(30);

    $this->get(signedSnoozeUrl($reminder, 3))
        ->assertRedirectToRoute('maintenance.reminder.snoozed');

    $reminder->refresh();
    expect($reminder->snoozed_until->equalTo(today()->addDays(3)))->toBeTrue();
});

// ─── T054 ──────────────────────────────────────────────────────────────────────

test('it rejects an invalid or expired signed URL with 403', function () {
    ['reminder' => $reminder] = makeSentReminder(30);

    // Tamper with the URL by adding an extra parameter after signing
    $url = signedSnoozeUrl($reminder, 7).'&tampered=1';

    $this->get($url)->assertForbidden();
});

// ─── T055 ──────────────────────────────────────────────────────────────────────

test('it rejects snooze when resulting date would be on or after due date', function () {
    // Due in 3 days; snoozing for 7 days would put snoozed_until after due date
    ['reminder' => $reminder] = makeSentReminder(3);

    $this->get(signedSnoozeUrl($reminder, 7))
        ->assertRedirect();

    // Reminder should NOT have been snoozed
    $reminder->refresh();
    expect($reminder->snoozed_until)->toBeNull();
});

// ─── T056 ──────────────────────────────────────────────────────────────────────

test('it increments snooze_count and clears sent_at on snooze', function () {
    ['reminder' => $reminder] = makeSentReminder(30);

    expect($reminder->snooze_count)->toBe(0);
    expect($reminder->sent_at)->not->toBeNull();

    $this->get(signedSnoozeUrl($reminder, 7))
        ->assertRedirectToRoute('maintenance.reminder.snoozed');

    $reminder->refresh();
    expect($reminder->snooze_count)->toBe(1);
    expect($reminder->sent_at)->toBeNull();
});
