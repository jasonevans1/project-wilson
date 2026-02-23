<?php

use App\Livewire\Maintenance\MaintenanceSchedule;
use App\Livewire\Maintenance\MaintenanceTaskList;
use App\Models\Asset;
use App\Models\MaintenanceOccurrence;
use App\Models\MaintenanceTask;
use App\Models\User;
use Livewire\Livewire;

test('deactivated task is excluded from maintenance schedule', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'name' => 'Inactive Filter Task',
        'is_active' => false,
    ]);
    MaintenanceOccurrence::factory()->pending()->create(['maintenance_task_id' => $task->id]);

    $this->actingAs($user);

    Livewire::test(MaintenanceSchedule::class)
        ->assertDontSee('Inactive Filter Task');
});

test('pending occurrence of deactivated task is not shown in schedule while active task is', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);

    $activeTask = MaintenanceTask::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'name' => 'Active Gutter Clean',
        'is_active' => true,
    ]);
    MaintenanceOccurrence::factory()->pending()->create(['maintenance_task_id' => $activeTask->id]);

    $inactiveTask = MaintenanceTask::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'name' => 'Deactivated Roof Check',
        'is_active' => false,
    ]);
    MaintenanceOccurrence::factory()->pending()->create(['maintenance_task_id' => $inactiveTask->id]);

    $this->actingAs($user);

    Livewire::test(MaintenanceSchedule::class)
        ->assertSee('Active Gutter Clean')
        ->assertDontSee('Deactivated Roof Check');
});

test('completed occurrences of deactivated task are preserved in database', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'is_active' => true,
    ]);
    $completedOccurrence = MaintenanceOccurrence::factory()->completed()->create([
        'maintenance_task_id' => $task->id,
    ]);

    $this->actingAs($user);

    Livewire::test(MaintenanceTaskList::class, ['asset' => $asset])
        ->call('deactivateTask', $task->id);

    expect($task->fresh()->is_active)->toBeFalse();
    expect(MaintenanceOccurrence::find($completedOccurrence->id))->not->toBeNull();
    expect(MaintenanceOccurrence::find($completedOccurrence->id)->completed_at)->not->toBeNull();
});

test('cannot deactivate another users task (403)', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $assetA = Asset::factory()->create(['user_id' => $userA->id]);
    $assetB = Asset::factory()->create(['user_id' => $userB->id]);
    $taskB = MaintenanceTask::factory()->create([
        'user_id' => $userB->id,
        'asset_id' => $assetB->id,
        'is_active' => true,
    ]);

    $this->actingAs($userA);

    Livewire::test(MaintenanceTaskList::class, ['asset' => $assetA])
        ->call('deactivateTask', $taskB->id)
        ->assertForbidden();

    expect($taskB->fresh()->is_active)->toBeTrue();
});
