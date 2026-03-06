<?php

use App\Enums\AssetCategory;
use App\Livewire\ReplacementTracking\ReplacementDashboard;
use App\Models\Asset;
use App\Models\User;
use Livewire\Livewire;

// ─── Route access ──────────────────────────────────────────────────────────

test('authenticated user can access the replacement tracking page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('replacement-tracking'))
        ->assertSuccessful();
});

test('unauthenticated access to replacement tracking redirects to login', function () {
    $this->get(route('replacement-tracking'))
        ->assertRedirectToRoute('login');
});

// ─── Asset ordering ─────────────────────────────────────────────────────────

test('tracked assets appear before untracked assets in the list', function () {
    $user = User::factory()->create();

    $untracked = Asset::factory()->for($user)->create([
        'name' => 'Untracked Asset',
        'category' => AssetCategory::Appliance,
        'install_date' => null,
        'expected_lifespan_years' => null,
    ]);

    $tracked = Asset::factory()->for($user)->create([
        'name' => 'Tracked Asset',
        'category' => AssetCategory::Appliance,
        'install_date' => now()->subYears(2),
        'expected_lifespan_years' => 10,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(ReplacementDashboard::class);

    $trackedIds = $component->get('trackedAssets')->pluck('id')->toArray();
    $untrackedIds = $component->get('untrackedAssets')->pluck('id')->toArray();

    expect($trackedIds)->toContain($tracked->id);
    expect($untrackedIds)->toContain($untracked->id);
    expect($trackedIds)->not->toContain($untracked->id);
    expect($untrackedIds)->not->toContain($tracked->id);
});

test('tracked assets are ordered by days remaining ascending', function () {
    $user = User::factory()->create();

    $further = Asset::factory()->for($user)->create([
        'name' => 'Further Asset',
        'category' => AssetCategory::Appliance,
        'install_date' => now()->subYear(),
        'expected_lifespan_years' => 10,
    ]);

    $closer = Asset::factory()->for($user)->create([
        'name' => 'Closer Asset',
        'category' => AssetCategory::Appliance,
        'install_date' => now()->subYears(9),
        'expected_lifespan_years' => 10,
    ]);

    $this->actingAs($user);

    $trackedIds = Livewire::test(ReplacementDashboard::class)
        ->get('trackedAssets')
        ->pluck('id')
        ->toArray();

    expect(array_search($closer->id, $trackedIds))->toBeLessThan(array_search($further->id, $trackedIds));
});

test('an overdue asset appears at the top of the tracked list', function () {
    $user = User::factory()->create();

    $overdue = Asset::factory()->for($user)->create([
        'name' => 'Overdue Asset',
        'category' => AssetCategory::Appliance,
        'install_date' => now()->subYears(12),
        'expected_lifespan_years' => 10,
    ]);

    $current = Asset::factory()->for($user)->create([
        'name' => 'Current Asset',
        'category' => AssetCategory::Appliance,
        'install_date' => now()->subYears(2),
        'expected_lifespan_years' => 10,
    ]);

    $this->actingAs($user);

    $trackedIds = Livewire::test(ReplacementDashboard::class)
        ->get('trackedAssets')
        ->pluck('id')
        ->toArray();

    expect($trackedIds[0])->toBe($overdue->id);
});

test('untracked assets display a set up tracking call-to-action button', function () {
    $user = User::factory()->create();

    Asset::factory()->for($user)->create([
        'name' => 'My Untracked Asset',
        'category' => AssetCategory::Appliance,
        'install_date' => null,
        'expected_lifespan_years' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(ReplacementDashboard::class)
        ->assertSee('Set Up');
});

test('assets belonging to other users do not appear on the dashboard', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Asset::factory()->for($other)->create([
        'name' => 'Other User Asset',
        'category' => AssetCategory::Appliance,
        'install_date' => now()->subYears(2),
        'expected_lifespan_years' => 10,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(ReplacementDashboard::class);

    expect($component->get('trackedAssets'))->toHaveCount(0);
    expect($component->get('untrackedAssets'))->toHaveCount(0);
});
