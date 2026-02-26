<?php

use App\Enums\ServiceType;
use App\Livewire\ServiceRecords\ServiceRecordList;
use App\Models\Asset;
use App\Models\ServiceRecord;
use App\Models\User;
use Livewire\Livewire;

test('multiple records display sorted by service_date descending', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    ServiceRecord::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'service_date' => '2024-01-01',
        'service_type' => ServiceType::Inspection->value,
        'description' => 'Oldest record.',
    ]);

    ServiceRecord::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'service_date' => '2025-06-15',
        'service_type' => ServiceType::Repair->value,
        'description' => 'Middle record.',
    ]);

    ServiceRecord::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'service_date' => '2026-01-10',
        'service_type' => ServiceType::Maintenance->value,
        'description' => 'Newest record.',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(ServiceRecordList::class, ['asset' => $asset]);

    $records = $component->instance()->records;

    expect($records)->toHaveCount(3);
    expect($records->first()->service_date->toDateString())->toBe('2026-01-10');
    expect($records->last()->service_date->toDateString())->toBe('2024-01-01');
});

test('empty state message shown when asset has no records', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(ServiceRecordList::class, ['asset' => $asset])
        ->assertSee('No service records yet');
});

test('service type label, date, provider name, and cost are visible in list', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    ServiceRecord::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'service_date' => '2025-03-20',
        'service_type' => ServiceType::Repair->value,
        'description' => 'Fixed the boiler.',
        'provider_name' => 'XYZ Plumbing',
        'cost' => '320.50',
    ]);

    $this->actingAs($user);

    Livewire::test(ServiceRecordList::class, ['asset' => $asset])
        ->assertSee('Repair')
        ->assertSee('Mar 20, 2025')
        ->assertSee('XYZ Plumbing')
        ->assertSee('320.50');
});

test('authenticated user accessing another users asset service records is forbidden', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $assetOfB = Asset::factory()->create(['user_id' => $userB->id]);

    $this->actingAs($userA)
        ->get(route('service-records.index', $assetOfB))
        ->assertForbidden();
});
