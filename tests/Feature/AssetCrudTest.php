<?php

use App\Enums\AssetCategory;
use App\Enums\AssetStatus;
use App\Livewire\Assets\AssetDetail;
use App\Livewire\Assets\AssetForm;
use App\Livewire\Assets\AssetList;
use App\Models\Asset;
use App\Models\User;
use Livewire\Livewire;

// ─── US1: Add a New Home Asset ───────────────────────────────────────────────

test('authenticated verified user can create an asset', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(AssetList::class)
        ->call('openCreateForm')
        ->assertSet('showCreateForm', true);

    Livewire::test(AssetForm::class)
        ->set('name', 'Refrigerator')
        ->set('category', AssetCategory::Appliance->value)
        ->set('location', 'Kitchen')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('asset-created');

    expect(Asset::count())->toBe(1);

    $asset = Asset::first();
    expect($asset->name)->toBe('Refrigerator');
    expect($asset->category)->toBe(AssetCategory::Appliance);
    expect($asset->location)->toBe('Kitchen');
    expect($asset->user_id)->toBe($user->id);
    expect($asset->status)->toBe(AssetStatus::Active);
});

test('creating an asset without name produces a validation error', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(AssetForm::class)
        ->set('name', '')
        ->set('category', AssetCategory::Appliance->value)
        ->set('location', 'Kitchen')
        ->call('save')
        ->assertHasErrors(['name']);

    expect(Asset::count())->toBe(0);
});

test('creating an asset without category produces a validation error', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(AssetForm::class)
        ->set('name', 'Refrigerator')
        ->set('category', '')
        ->set('location', 'Kitchen')
        ->call('save')
        ->assertHasErrors(['category']);

    expect(Asset::count())->toBe(0);
});

test('unauthenticated user cannot access the assets page', function () {
    $this->get('/assets')
        ->assertRedirect();
});

// ─── US2: View and Browse Home Assets ─────────────────────────────────────────

test('user with assets sees all active assets listed', function () {
    $user = User::factory()->create();
    Asset::factory(3)->create(['user_id' => $user->id, 'status' => AssetStatus::Active]);

    $this->actingAs($user);

    Livewire::test(AssetList::class)
        ->assertSet('showArchived', false)
        ->assertCount('assets', 3);
});

test('selecting an asset loads the detail view with all fields', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create([
        'user_id' => $user->id,
        'name' => 'Water Heater',
        'category' => AssetCategory::Plumbing,
        'location' => 'Basement',
        'purchase_date' => '2022-03-15',
        'install_date' => '2022-03-20',
        'warranty_expiration_date' => '2027-03-20',
        'notes' => 'Replaced old unit.',
        'status' => AssetStatus::Active,
    ]);

    $this->actingAs($user);

    Livewire::test(AssetList::class)
        ->call('selectAsset', $asset->id)
        ->assertSet('selectedAssetId', $asset->id);

    Livewire::test(AssetDetail::class, ['asset' => $asset])
        ->assertSee('Water Heater')
        ->assertSee('Plumbing')
        ->assertSee('Basement')
        ->assertSee('2022-03-15')
        ->assertSee('2022-03-20')
        ->assertSee('2027-03-20')
        ->assertSee('Replaced old unit.');
});

test('user with zero assets sees the empty state', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(AssetList::class)
        ->assertSee('No assets yet.');
});

test('user A cannot see user B assets', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    Asset::factory(2)->create(['user_id' => $userA->id]);
    Asset::factory(3)->create(['user_id' => $userB->id]);

    $this->actingAs($userA);

    Livewire::test(AssetList::class)
        ->assertCount('assets', 2);
});
