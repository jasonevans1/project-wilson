<?php

use App\Enums\AssetCategory;
use App\Livewire\ReplacementTracking\ReplacementSetupForm;
use App\Models\Asset;
use App\Models\User;
use App\Services\AssetLifespanDefaults;
use Livewire\Livewire;

// ─── Mount pre-filling ─────────────────────────────────────────────────────

test('mount pre-fills expectedLifespanYears from asset when already set', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->for($user)->create([
        'category' => AssetCategory::Appliance,
        'expected_lifespan_years' => 12,
        'install_date' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(ReplacementSetupForm::class, ['asset' => $asset])
        ->assertSet('expectedLifespanYears', 12);
});

test('mount pre-fills expectedLifespanYears from category default when asset has no lifespan', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->for($user)->create([
        'category' => AssetCategory::Hvac,
        'expected_lifespan_years' => null,
        'install_date' => null,
    ]);

    $this->actingAs($user);

    $expected = AssetLifespanDefaults::forCategory(AssetCategory::Hvac);

    Livewire::test(ReplacementSetupForm::class, ['asset' => $asset])
        ->assertSet('expectedLifespanYears', $expected);
});

test('mount pre-fills installDate from asset when already set', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->for($user)->create([
        'install_date' => '2020-06-15',
    ]);

    $this->actingAs($user);

    Livewire::test(ReplacementSetupForm::class, ['asset' => $asset])
        ->assertSet('installDate', '2020-06-15');
});

// ─── Save ──────────────────────────────────────────────────────────────────

test('save persists expected_lifespan_years and install_date on asset and dispatches tracking-configured', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->for($user)->create([
        'category' => AssetCategory::Appliance,
        'expected_lifespan_years' => null,
        'install_date' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(ReplacementSetupForm::class, ['asset' => $asset])
        ->set('expectedLifespanYears', 10)
        ->set('installDate', '2018-01-01')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('tracking-configured');

    $asset->refresh();
    expect($asset->expected_lifespan_years)->toBe(10);
    expect($asset->install_date->format('Y-m-d'))->toBe('2018-01-01');
});

test('save fails validation when expectedLifespanYears is 0', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->for($user)->create([
        'category' => AssetCategory::Appliance,
    ]);

    $this->actingAs($user);

    Livewire::test(ReplacementSetupForm::class, ['asset' => $asset])
        ->set('expectedLifespanYears', 0)
        ->set('installDate', '2018-01-01')
        ->call('save')
        ->assertHasErrors(['expectedLifespanYears']);
});

test('save fails validation when expectedLifespanYears exceeds 100', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->for($user)->create([
        'category' => AssetCategory::Appliance,
    ]);

    $this->actingAs($user);

    Livewire::test(ReplacementSetupForm::class, ['asset' => $asset])
        ->set('expectedLifespanYears', 101)
        ->set('installDate', '2018-01-01')
        ->call('save')
        ->assertHasErrors(['expectedLifespanYears']);
});

test('save fails validation when installDate is a future date', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->for($user)->create([
        'category' => AssetCategory::Appliance,
    ]);

    $this->actingAs($user);

    Livewire::test(ReplacementSetupForm::class, ['asset' => $asset])
        ->set('expectedLifespanYears', 10)
        ->set('installDate', now()->addDay()->format('Y-m-d'))
        ->call('save')
        ->assertHasErrors(['installDate']);
});

test('save is rejected with 403 when asset does not belong to authenticated user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $asset = Asset::factory()->for($otherUser)->create([
        'category' => AssetCategory::Appliance,
    ]);

    $this->actingAs($user);

    Livewire::test(ReplacementSetupForm::class, ['asset' => $asset])
        ->set('expectedLifespanYears', 10)
        ->set('installDate', '2018-01-01')
        ->call('save')
        ->assertForbidden();
});
