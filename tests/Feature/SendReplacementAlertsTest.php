<?php

use App\Enums\AssetCategory;
use App\Enums\ReplacementAlertType;
use App\Models\Asset;
use App\Models\AssetReplacementAlert;
use App\Models\User;
use App\Notifications\ReplacementAlertNotification;
use Illuminate\Support\Facades\Notification;

// Helper to create a user with verified email and alerts enabled
function alertUser(array $attrs = []): User
{
    return User::factory()->create(array_merge([
        'email_verified_at' => now(),
        'replacement_alerts_enabled' => true,
    ], $attrs));
}

// Helper to create a tracked asset with a specific days_remaining
function trackedAsset(User $user, int $daysRemaining, array $attrs = []): Asset
{
    $lifespan = 10;
    $installDate = now()->subYears($lifespan)->addDays($daysRemaining);

    return Asset::factory()->for($user)->create(array_merge([
        'category' => AssetCategory::Appliance,
        'install_date' => $installDate,
        'expected_lifespan_years' => $lifespan,
        'replacement_alerts_enabled' => true,
    ], $attrs));
}

// ─── Alert sending ──────────────────────────────────────────────────────────

test('command sends a TwoYear alert for an asset with 730 days remaining', function () {
    Notification::fake();

    $user = alertUser();
    trackedAsset($user, 730);

    $this->artisan('replacement:send-alerts')->assertSuccessful();

    Notification::assertSentTo($user, ReplacementAlertNotification::class, function ($notification) {
        return $notification->alertType === ReplacementAlertType::TwoYear;
    });
});

test('command sends a OneYear alert for an asset with 365 days remaining', function () {
    Notification::fake();

    $user = alertUser();
    trackedAsset($user, 365);

    $this->artisan('replacement:send-alerts')->assertSuccessful();

    Notification::assertSentTo($user, ReplacementAlertNotification::class, function ($notification) {
        return $notification->alertType === ReplacementAlertType::OneYear;
    });
});

test('command sends an Overdue alert for an asset whose replacement date is yesterday', function () {
    Notification::fake();

    $user = alertUser();
    trackedAsset($user, -1);

    $this->artisan('replacement:send-alerts')->assertSuccessful();

    Notification::assertSentTo($user, ReplacementAlertNotification::class, function ($notification) {
        return $notification->alertType === ReplacementAlertType::Overdue;
    });
});

// ─── Deduplication ─────────────────────────────────────────────────────────

test('command does not send a duplicate TwoYear alert when one is already sent and not dismissed', function () {
    Notification::fake();

    $user = alertUser();
    $asset = trackedAsset($user, 730);

    AssetReplacementAlert::factory()->for($asset)->sent()->create([
        'alert_type' => ReplacementAlertType::TwoYear,
    ]);

    $this->artisan('replacement:send-alerts')->assertSuccessful();

    Notification::assertNothingSent();
});

// ─── Opt-out: asset disabled ───────────────────────────────────────────────

test('command does not send alerts for assets where replacement_alerts_enabled is false', function () {
    Notification::fake();

    $user = alertUser();
    trackedAsset($user, 730, ['replacement_alerts_enabled' => false]);

    $this->artisan('replacement:send-alerts')->assertSuccessful();

    Notification::assertNothingSent();
});

// ─── Opt-out: user disabled ────────────────────────────────────────────────

test('command does not send alerts for users where replacement_alerts_enabled is false', function () {
    Notification::fake();

    $user = alertUser(['replacement_alerts_enabled' => false]);
    trackedAsset($user, 730);

    $this->artisan('replacement:send-alerts')->assertSuccessful();

    Notification::assertNothingSent();
});

// ─── Opt-out: unverified email ─────────────────────────────────────────────

test('command does not send alerts for users without a verified email', function () {
    Notification::fake();

    $user = alertUser(['email_verified_at' => null]);
    trackedAsset($user, 730);

    $this->artisan('replacement:send-alerts')->assertSuccessful();

    Notification::assertNothingSent();
});

// ─── Untracked assets ──────────────────────────────────────────────────────

test('command does not send alerts for assets missing install_date or expected_lifespan_years', function () {
    Notification::fake();

    $user = alertUser();

    Asset::factory()->for($user)->create([
        'category' => AssetCategory::Appliance,
        'install_date' => null,
        'expected_lifespan_years' => null,
        'replacement_alerts_enabled' => true,
    ]);

    $this->artisan('replacement:send-alerts')->assertSuccessful();

    Notification::assertNothingSent();
});

// ─── Dismissed overdue ─────────────────────────────────────────────────────

test('command does not send an Overdue alert when dismissed_at is already set', function () {
    Notification::fake();

    $user = alertUser();
    $asset = trackedAsset($user, -1);

    AssetReplacementAlert::factory()->for($asset)->dismissed()->overdue()->create();

    $this->artisan('replacement:send-alerts')->assertSuccessful();

    Notification::assertNothingSent();
});
