<?php

use App\Enums\AssetCategory;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// ─── AssetCategory enum ───────────────────────────────────────────────────────

test('AssetCategory has eight cases with correct backed values', function () {
    $cases = AssetCategory::cases();

    expect($cases)->toHaveCount(8);
    expect(AssetCategory::Appliance->value)->toBe('appliance');
    expect(AssetCategory::Hvac->value)->toBe('hvac');
    expect(AssetCategory::Plumbing->value)->toBe('plumbing');
    expect(AssetCategory::Electrical->value)->toBe('electrical');
    expect(AssetCategory::Roofing->value)->toBe('roofing');
    expect(AssetCategory::Flooring->value)->toBe('flooring');
    expect(AssetCategory::Exterior->value)->toBe('exterior');
    expect(AssetCategory::Other->value)->toBe('other');
});

test('AssetCategory label method returns correct human-readable labels', function () {
    expect(AssetCategory::Appliance->label())->toBe('Appliance');
    expect(AssetCategory::Hvac->label())->toBe('HVAC');
    expect(AssetCategory::Plumbing->label())->toBe('Plumbing');
    expect(AssetCategory::Electrical->label())->toBe('Electrical');
    expect(AssetCategory::Roofing->label())->toBe('Roofing');
    expect(AssetCategory::Flooring->label())->toBe('Flooring');
    expect(AssetCategory::Exterior->label())->toBe('Exterior');
    expect(AssetCategory::Other->label())->toBe('Other');
});

// ─── AssetStatus enum ─────────────────────────────────────────────────────────

test('AssetStatus has Active and Archived cases', function () {
    expect(AssetStatus::cases())->toHaveCount(2);
    expect(AssetStatus::Active->value)->toBe('active');
    expect(AssetStatus::Archived->value)->toBe('archived');
});

// ─── Asset model casts ────────────────────────────────────────────────────────

test('Asset model casts category to AssetCategory and status to AssetStatus', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    expect($asset->category)->toBeInstanceOf(AssetCategory::class);
    expect($asset->status)->toBeInstanceOf(AssetStatus::class);
});

// ─── AssetFactory archived state ──────────────────────────────────────────────

test('AssetFactory archived state produces an asset with Archived status', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->archived()->create(['user_id' => $user->id]);

    expect($asset->status)->toBe(AssetStatus::Archived);
});

// ─── User::assets() relationship ──────────────────────────────────────────────

test('User assets relationship returns only that user assets', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    Asset::factory(2)->create(['user_id' => $userA->id]);
    Asset::factory(3)->create(['user_id' => $userB->id]);

    expect($userA->assets()->count())->toBe(2);
    expect($userB->assets()->count())->toBe(3);
});
