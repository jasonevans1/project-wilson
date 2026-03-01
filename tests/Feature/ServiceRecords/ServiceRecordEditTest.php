<?php

use App\Enums\ServiceType;
use App\Livewire\ServiceRecords\ServiceRecordList;
use App\Models\Asset;
use App\Models\ServiceRecord;
use App\Models\User;
use Livewire\Livewire;

test('authenticated user can edit cost and description of their service record', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $record = ServiceRecord::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'service_date' => '2025-06-01',
        'service_type' => ServiceType::Repair->value,
        'description' => 'Original description.',
        'cost' => '100.00',
    ]);

    $this->actingAs($user);

    Livewire::test(ServiceRecordList::class, ['asset' => $asset])
        ->call('startEdit', $record->id)
        ->set('description', 'Updated description.')
        ->set('cost', '250.00')
        ->call('updateRecord')
        ->assertHasNoErrors();

    $record->refresh();
    expect($record->description)->toBe('Updated description.');
    expect((string) $record->cost)->toBe('250.00');
});

test('editing service_date to an older date repositions record in sorted order', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $newer = ServiceRecord::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'service_date' => '2026-01-10',
        'service_type' => ServiceType::Maintenance->value,
        'description' => 'Newer record.',
    ]);

    $older = ServiceRecord::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'service_date' => '2025-01-01',
        'service_type' => ServiceType::Inspection->value,
        'description' => 'Older record.',
    ]);

    $this->actingAs($user);

    Livewire::test(ServiceRecordList::class, ['asset' => $asset])
        ->call('startEdit', $newer->id)
        ->set('serviceDate', '2023-06-01')
        ->call('updateRecord')
        ->assertHasNoErrors();

    $newer->refresh();
    expect($newer->service_date->toDateString())->toBe('2023-06-01');

    $records = $asset->serviceRecords()->orderBy('service_date', 'desc')->get();
    expect($records->first()->id)->toBe($older->id);
    expect($records->last()->id)->toBe($newer->id);
});

test('clearing description during edit triggers a validation error', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $record = ServiceRecord::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'service_date' => '2025-06-01',
        'service_type' => ServiceType::Repair->value,
        'description' => 'Original description.',
    ]);

    $this->actingAs($user);

    Livewire::test(ServiceRecordList::class, ['asset' => $asset])
        ->call('startEdit', $record->id)
        ->set('description', '')
        ->call('updateRecord')
        ->assertHasErrors(['description']);

    $record->refresh();
    expect($record->description)->toBe('Original description.');
});

test('authenticated user attempting to edit another users record is forbidden', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $assetA = Asset::factory()->create(['user_id' => $userA->id]);
    $assetB = Asset::factory()->create(['user_id' => $userB->id]);
    $recordOfB = ServiceRecord::factory()->create([
        'user_id' => $userB->id,
        'asset_id' => $assetB->id,
        'service_date' => '2025-06-01',
        'service_type' => ServiceType::Repair->value,
        'description' => 'User B record.',
    ]);

    $this->actingAs($userA);

    Livewire::test(ServiceRecordList::class, ['asset' => $assetA])
        ->call('startEdit', $recordOfB->id)
        ->assertForbidden();
});
