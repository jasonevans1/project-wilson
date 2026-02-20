<?php

use App\Enums\RecurrenceUnit;
use App\Livewire\Maintenance\MaintenanceTaskForm;
use App\Models\Asset;
use App\Models\MaintenanceOccurrence;
use App\Models\MaintenanceTask;
use App\Models\User;
use Livewire\Livewire;

test('authenticated user can create a maintenance task for their asset', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(MaintenanceTaskForm::class, ['assetId' => $asset->id])
        ->set('name', 'Change HVAC filter')
        ->set('recurrenceUnit', 'monthly')
        ->set('recurrenceCount', 1)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('task-created');

    expect(MaintenanceTask::count())->toBe(1);

    $task = MaintenanceTask::first();
    expect($task->name)->toBe('Change HVAC filter');
    expect($task->recurrence_unit)->toBe(RecurrenceUnit::Monthly);
    expect($task->recurrence_count)->toBe(1);
    expect($task->user_id)->toBe($user->id);
    expect($task->asset_id)->toBe($asset->id);
    expect($task->is_active)->toBeTrue();
});

test('first occurrence is created with provided start_date', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(MaintenanceTaskForm::class, ['assetId' => $asset->id])
        ->set('name', 'Oil Change')
        ->set('recurrenceUnit', 'monthly')
        ->set('recurrenceCount', 3)
        ->set('startDate', '2026-03-01')
        ->call('save')
        ->assertHasNoErrors();

    expect(MaintenanceOccurrence::count())->toBe(1);
    expect(MaintenanceOccurrence::first()->due_date->toDateString())->toBe('2026-03-01');
});

test('first occurrence defaults to today when no start_date is provided', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(MaintenanceTaskForm::class, ['assetId' => $asset->id])
        ->set('name', 'Filter Replacement')
        ->call('save')
        ->assertHasNoErrors();

    expect(MaintenanceOccurrence::count())->toBe(1);
    expect(MaintenanceOccurrence::first()->due_date->toDateString())->toBe(today()->toDateString());
});

test('name is required', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(MaintenanceTaskForm::class, ['assetId' => $asset->id])
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name']);

    expect(MaintenanceTask::count())->toBe(0);
});

test('name cannot exceed 255 characters', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(MaintenanceTaskForm::class, ['assetId' => $asset->id])
        ->set('name', str_repeat('a', 256))
        ->call('save')
        ->assertHasErrors(['name']);

    expect(MaintenanceTask::count())->toBe(0);
});

test('recurrence_count must be at least 1', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(MaintenanceTaskForm::class, ['assetId' => $asset->id])
        ->set('name', 'Filter Replacement')
        ->set('recurrenceCount', 0)
        ->call('save')
        ->assertHasErrors(['recurrenceCount']);

    expect(MaintenanceTask::count())->toBe(0);
});

test('asset must belong to the authenticated user', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $assetOfB = Asset::factory()->create(['user_id' => $userB->id]);

    $this->actingAs($userA);

    Livewire::test(MaintenanceTaskForm::class, ['assetId' => $assetOfB->id])
        ->set('name', 'Filter Replacement')
        ->call('save')
        ->assertHasErrors(['assetId']);

    expect(MaintenanceTask::count())->toBe(0);
});

test('task-created event is dispatched after successful save', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(MaintenanceTaskForm::class, ['assetId' => $asset->id])
        ->set('name', 'Change HVAC filter')
        ->call('save')
        ->assertDispatched('task-created');
});
