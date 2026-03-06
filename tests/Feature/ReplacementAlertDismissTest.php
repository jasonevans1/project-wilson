<?php

use App\Enums\AssetCategory;
use App\Models\Asset;
use App\Models\AssetReplacementAlert;
use App\Models\User;
use Illuminate\Support\Facades\URL;

test('valid signed dismiss URL sets dismissed_at and redirects with success message', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $asset = Asset::factory()->for($user)->create(['category' => AssetCategory::Appliance]);

    $alert = AssetReplacementAlert::factory()->for($asset)->sent()->overdue()->create();

    $url = URL::signedRoute('replacement.alert.dismiss', ['alert' => $alert->id]);

    $this->get($url)
        ->assertRedirectToRoute('replacement-tracking')
        ->assertSessionHas('status');

    expect($alert->fresh()->dismissed_at)->not->toBeNull();
});

test('dismiss URL with invalid signature returns 403', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->for($user)->create(['category' => AssetCategory::Appliance]);
    $alert = AssetReplacementAlert::factory()->for($asset)->sent()->overdue()->create();

    $url = route('replacement.alert.dismiss', ['alert' => $alert->id]);

    $this->get($url)->assertForbidden();
});

test('dismissing an already-dismissed alert is idempotent and succeeds', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $asset = Asset::factory()->for($user)->create(['category' => AssetCategory::Appliance]);

    $dismissedAt = now()->subHour();
    $alert = AssetReplacementAlert::factory()->for($asset)->dismissed()->overdue()->create([
        'dismissed_at' => $dismissedAt,
    ]);

    $url = URL::signedRoute('replacement.alert.dismiss', ['alert' => $alert->id]);

    $this->get($url)
        ->assertRedirectToRoute('replacement-tracking');

    // dismissed_at should remain unchanged (not updated to now())
    expect($alert->fresh()->dismissed_at->toDateTimeString())
        ->toBe($dismissedAt->toDateTimeString());
});
