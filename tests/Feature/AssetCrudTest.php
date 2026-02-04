<?php

use App\Enums\AssetCategory;
use App\Enums\AssetStatus;
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
