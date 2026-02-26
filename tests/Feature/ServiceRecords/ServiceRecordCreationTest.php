<?php

use App\Enums\ServiceType;
use App\Livewire\ServiceRecords\ServiceRecordList;
use App\Models\Asset;
use App\Models\ServiceRecord;
use App\Models\User;
use Livewire\Livewire;

test('authenticated user can create a service record for their asset', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(ServiceRecordList::class, ['asset' => $asset])
        ->call('showCreateForm')
        ->set('serviceDate', '2026-01-15')
        ->set('serviceType', ServiceType::Maintenance->value)
        ->set('description', 'Replaced the HVAC filter and cleaned the vents.')
        ->set('providerName', 'ABC Services')
        ->set('cost', '150.00')
        ->call('saveRecord')
        ->assertHasNoErrors();

    expect(ServiceRecord::count())->toBe(1);

    $record = ServiceRecord::first();
    expect($record->asset_id)->toBe($asset->id);
    expect($record->user_id)->toBe($user->id);
    expect($record->service_type)->toBe(ServiceType::Maintenance);
    expect($record->service_date->toDateString())->toBe('2026-01-15');
    expect($record->description)->toBe('Replaced the HVAC filter and cleaned the vents.');
    expect($record->provider_name)->toBe('ABC Services');
    expect((string) $record->cost)->toBe('150.00');
});

test('service_date is required', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(ServiceRecordList::class, ['asset' => $asset])
        ->call('showCreateForm')
        ->set('serviceType', ServiceType::Repair->value)
        ->set('description', 'Fixed a leak.')
        ->call('saveRecord')
        ->assertHasErrors(['serviceDate']);

    expect(ServiceRecord::count())->toBe(0);
});

test('service_type is required', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(ServiceRecordList::class, ['asset' => $asset])
        ->call('showCreateForm')
        ->set('serviceDate', '2026-01-15')
        ->set('description', 'Fixed a leak.')
        ->call('saveRecord')
        ->assertHasErrors(['serviceType']);

    expect(ServiceRecord::count())->toBe(0);
});

test('description is required', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(ServiceRecordList::class, ['asset' => $asset])
        ->call('showCreateForm')
        ->set('serviceDate', '2026-01-15')
        ->set('serviceType', ServiceType::Inspection->value)
        ->call('saveRecord')
        ->assertHasErrors(['description']);

    expect(ServiceRecord::count())->toBe(0);
});

test('cost of zero is accepted', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(ServiceRecordList::class, ['asset' => $asset])
        ->call('showCreateForm')
        ->set('serviceDate', '2026-01-15')
        ->set('serviceType', ServiceType::Maintenance->value)
        ->set('description', 'Warranty-covered repair, no charge.')
        ->set('cost', '0')
        ->call('saveRecord')
        ->assertHasNoErrors();

    expect((string) ServiceRecord::first()->cost)->toBe('0.00');
});

test('past service date is accepted', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(ServiceRecordList::class, ['asset' => $asset])
        ->call('showCreateForm')
        ->set('serviceDate', '2020-06-01')
        ->set('serviceType', ServiceType::Replacement->value)
        ->set('description', 'Replaced water heater.')
        ->call('saveRecord')
        ->assertHasNoErrors();

    expect(ServiceRecord::first()->service_date->toDateString())->toBe('2020-06-01');
});

test('unauthenticated users are redirected to login', function () {
    $asset = Asset::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->get(route('service-records.index', $asset))
        ->assertRedirect(route('login'));
});

test('authenticated user cannot access another users asset service records', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $assetOfB = Asset::factory()->create(['user_id' => $userB->id]);

    $this->actingAs($userA)
        ->get(route('service-records.index', $assetOfB))
        ->assertForbidden();
});
