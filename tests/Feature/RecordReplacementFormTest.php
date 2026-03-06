<?php

use App\Enums\AssetCategory;
use App\Enums\ReplacementAlertType;
use App\Livewire\ReplacementTracking\RecordReplacementForm;
use App\Models\Asset;
use App\Models\AssetReplacementAlert;
use App\Models\AssetReplacementEvent;
use App\Models\User;
use Livewire\Livewire;

// ─── Save: persistence ────────────────────────────────────────────────────

test('save creates a new AssetReplacementEvent with the correct fields', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->for($user)->create([
        'category' => AssetCategory::Appliance,
        'install_date' => now()->subYears(5),
        'expected_lifespan_years' => 10,
    ]);

    $this->actingAs($user);

    Livewire::test(RecordReplacementForm::class, ['asset' => $asset])
        ->set('installedAt', '2024-03-01')
        ->set('cost', '1200.50')
        ->set('notes', 'Replaced with new model')
        ->call('save')
        ->assertHasNoErrors();

    $event = AssetReplacementEvent::first();
    expect($event)->not->toBeNull();
    expect($event->asset_id)->toBe($asset->id);
    expect($event->installed_at->format('Y-m-d'))->toBe('2024-03-01');
    expect((float) $event->cost)->toBe(1200.50);
    expect($event->notes)->toBe('Replaced with new model');
});

test('save updates the asset install_date to the submitted installedAt value', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->for($user)->create([
        'category' => AssetCategory::Appliance,
        'install_date' => now()->subYears(5),
        'expected_lifespan_years' => 10,
    ]);

    $this->actingAs($user);

    Livewire::test(RecordReplacementForm::class, ['asset' => $asset])
        ->set('installedAt', '2024-06-15')
        ->call('save')
        ->assertHasNoErrors();

    expect($asset->fresh()->install_date->format('Y-m-d'))->toBe('2024-06-15');
});

test('save updates expected_lifespan_years when changed from pre-filled value', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->for($user)->create([
        'category' => AssetCategory::Appliance,
        'install_date' => now()->subYears(5),
        'expected_lifespan_years' => 10,
    ]);

    $this->actingAs($user);

    Livewire::test(RecordReplacementForm::class, ['asset' => $asset])
        ->set('installedAt', '2024-06-15')
        ->set('expectedLifespanYears', 15)
        ->call('save')
        ->assertHasNoErrors();

    expect($asset->fresh()->expected_lifespan_years)->toBe(15);
});

test('save deletes all existing AssetReplacementAlert rows for the asset', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->for($user)->create([
        'category' => AssetCategory::Appliance,
        'install_date' => now()->subYears(5),
        'expected_lifespan_years' => 10,
    ]);

    AssetReplacementAlert::factory()->for($asset)->create([
        'alert_type' => ReplacementAlertType::TwoYear,
        'sent_at' => now()->subWeek(),
    ]);
    AssetReplacementAlert::factory()->for($asset)->create([
        'alert_type' => ReplacementAlertType::OneYear,
        'sent_at' => now()->subDay(),
    ]);

    $this->actingAs($user);

    Livewire::test(RecordReplacementForm::class, ['asset' => $asset])
        ->set('installedAt', '2024-06-15')
        ->call('save');

    expect(AssetReplacementAlert::where('asset_id', $asset->id)->count())->toBe(0);
});

test('save dispatches the replacement-recorded event', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->for($user)->create([
        'category' => AssetCategory::Appliance,
        'install_date' => now()->subYears(5),
        'expected_lifespan_years' => 10,
    ]);

    $this->actingAs($user);

    Livewire::test(RecordReplacementForm::class, ['asset' => $asset])
        ->set('installedAt', '2024-06-15')
        ->call('save')
        ->assertDispatched('replacement-recorded');
});

test('save succeeds when cost and notes are omitted', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->for($user)->create([
        'category' => AssetCategory::Appliance,
        'install_date' => now()->subYears(5),
        'expected_lifespan_years' => 10,
    ]);

    $this->actingAs($user);

    Livewire::test(RecordReplacementForm::class, ['asset' => $asset])
        ->set('installedAt', '2024-06-15')
        ->set('cost', null)
        ->set('notes', null)
        ->call('save')
        ->assertHasNoErrors();

    $event = AssetReplacementEvent::first();
    expect($event->cost)->toBeNull();
    expect($event->notes)->toBeNull();
});

// ─── Validation ─────────────────────────────────────────────────────────

test('save fails validation when installedAt is a future date', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->for($user)->create([
        'category' => AssetCategory::Appliance,
    ]);

    $this->actingAs($user);

    Livewire::test(RecordReplacementForm::class, ['asset' => $asset])
        ->set('installedAt', now()->addDay()->format('Y-m-d'))
        ->call('save')
        ->assertHasErrors(['installedAt']);
});

// ─── Authorization ──────────────────────────────────────────────────────

test('save is rejected with 403 when asset does not belong to authenticated user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $asset = Asset::factory()->for($other)->create([
        'category' => AssetCategory::Appliance,
    ]);

    $this->actingAs($user);

    Livewire::test(RecordReplacementForm::class, ['asset' => $asset])
        ->set('installedAt', '2024-06-15')
        ->call('save')
        ->assertForbidden();
});

// ─── Replacement history ─────────────────────────────────────────────────

test('replacement history displays events ordered by installed_at descending', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->for($user)->create([
        'category' => AssetCategory::Appliance,
        'install_date' => now()->subYears(2),
        'expected_lifespan_years' => 10,
    ]);

    $older = AssetReplacementEvent::factory()->for($asset)->create([
        'installed_at' => '2018-01-01',
    ]);
    $newer = AssetReplacementEvent::factory()->for($asset)->create([
        'installed_at' => '2022-06-15',
    ]);

    $this->actingAs($user);

    $events = $asset->replacementEvents()->latest('installed_at')->get();

    expect($events->first()->id)->toBe($newer->id);
    expect($events->last()->id)->toBe($older->id);
});
