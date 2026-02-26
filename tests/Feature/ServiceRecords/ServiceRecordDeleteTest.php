<?php

use App\Enums\ServiceType;
use App\Livewire\ServiceRecords\ServiceRecordList;
use App\Models\Asset;
use App\Models\ServiceRecord;
use App\Models\User;
use Livewire\Livewire;

test('authenticated user can delete their own service record', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $record = ServiceRecord::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'service_date' => '2025-06-01',
        'service_type' => ServiceType::Repair->value,
        'description' => 'Record to delete.',
    ]);

    $this->actingAs($user);

    Livewire::test(ServiceRecordList::class, ['asset' => $asset])
        ->call('deleteRecord', $record->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('service_records', ['id' => $record->id]);
});

test('authenticated user attempting to delete another users record is forbidden', function () {
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
        ->call('deleteRecord', $recordOfB->id)
        ->assertForbidden();

    $this->assertDatabaseHas('service_records', ['id' => $recordOfB->id]);
});

test('deleting a record does not affect other records for the same asset', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $recordA = ServiceRecord::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'service_date' => '2025-01-01',
        'service_type' => ServiceType::Inspection->value,
        'description' => 'Record A — keep this.',
    ]);

    $recordB = ServiceRecord::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'service_date' => '2025-06-01',
        'service_type' => ServiceType::Repair->value,
        'description' => 'Record B — delete this.',
    ]);

    $this->actingAs($user);

    Livewire::test(ServiceRecordList::class, ['asset' => $asset])
        ->call('deleteRecord', $recordB->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('service_records', ['id' => $recordB->id]);
    $this->assertDatabaseHas('service_records', ['id' => $recordA->id]);
    expect(ServiceRecord::where('asset_id', $asset->id)->count())->toBe(1);
});
