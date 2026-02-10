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

    Livewire::test(AssetList::class)
        ->dispatch('asset-created')
        ->assertSet('showSuccess', true)
        ->assertSet('showCreateForm', false)
        ->assertSee('Asset added.');

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

// ─── US3: Update an Existing Home Asset ───────────────────────────────────────

test('authenticated user can update their own asset', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create([
        'user_id' => $user->id,
        'name' => 'Refrigerator',
        'category' => AssetCategory::Appliance,
        'location' => 'Kitchen',
    ]);

    $this->actingAs($user);

    Livewire::test(AssetForm::class, ['asset' => $asset])
        ->set('name', 'New Refrigerator')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('asset-updated');

    $asset->refresh();
    expect($asset->name)->toBe('New Refrigerator');
});

test('updating an asset without name produces a validation error and preserves original', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create([
        'user_id' => $user->id,
        'name' => 'Refrigerator',
        'category' => AssetCategory::Appliance,
        'location' => 'Kitchen',
    ]);

    $this->actingAs($user);

    Livewire::test(AssetForm::class, ['asset' => $asset])
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name']);

    $asset->refresh();
    expect($asset->name)->toBe('Refrigerator');
});

test('user A cannot update user B asset', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $asset = Asset::factory()->create([
        'user_id' => $userB->id,
        'name' => 'Water Heater',
        'category' => AssetCategory::Plumbing,
        'location' => 'Basement',
    ]);

    $this->actingAs($userA);

    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    Livewire::test(AssetForm::class, ['asset' => $asset])
        ->set('name', 'Hacked')
        ->call('save');
});

// ─── US4: Archive and Restore a Home Asset ────────────────────────────────────

test('archiving an active asset sets status to Archived and removes it from active list', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id, 'status' => AssetStatus::Active]);

    $this->actingAs($user);

    Livewire::test(AssetDetail::class, ['asset' => $asset])
        ->call('initiateArchive')
        ->assertSet('confirmingArchive', true)
        ->call('archive')
        ->assertDispatched('asset-archived');

    $asset->refresh();
    expect($asset->status)->toBe(AssetStatus::Archived);

    Livewire::test(AssetList::class)
        ->assertCount('assets', 0);
});

test('cancelling archive confirmation leaves asset unchanged', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id, 'status' => AssetStatus::Active]);

    $this->actingAs($user);

    Livewire::test(AssetDetail::class, ['asset' => $asset])
        ->call('initiateArchive')
        ->assertSet('confirmingArchive', true)
        ->call('cancelArchive')
        ->assertSet('confirmingArchive', false);

    $asset->refresh();
    expect($asset->status)->toBe(AssetStatus::Active);
});

test('toggling archived filter shows archived assets with badge', function () {
    $user = User::factory()->create();
    Asset::factory()->create(['user_id' => $user->id, 'status' => AssetStatus::Active]);
    Asset::factory()->archived()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(AssetList::class)
        ->assertCount('assets', 1)
        ->call('toggleArchived')
        ->assertSet('showArchived', true)
        ->assertCount('assets', 1)
        ->assertSee('Archived');
});

test('restoring an archived asset sets status back to Active', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->archived()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(AssetDetail::class, ['asset' => $asset])
        ->call('restore')
        ->assertDispatched('asset-restored');

    $asset->refresh();
    expect($asset->status)->toBe(AssetStatus::Active);

    Livewire::test(AssetList::class)
        ->assertCount('assets', 1);
});

test('user A cannot archive user B asset', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $userB->id, 'status' => AssetStatus::Active]);

    $this->actingAs($userA);

    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    Livewire::test(AssetDetail::class, ['asset' => $asset])
        ->call('archive');
});

test('user A cannot restore user B asset', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $asset = Asset::factory()->archived()->create(['user_id' => $userB->id]);

    $this->actingAs($userA);

    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    Livewire::test(AssetDetail::class, ['asset' => $asset])
        ->call('restore');
});
