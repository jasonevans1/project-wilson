<?php

use App\Livewire\Maintenance\MaintenanceSchedule;
use App\Models\Asset;
use App\Models\MaintenanceOccurrence;
use App\Models\MaintenanceTask;
use App\Models\User;
use Livewire\Livewire;

test('shows pending occurrences for authenticated user', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->create(['user_id' => $user->id, 'asset_id' => $asset->id, 'name' => 'Change HVAC Filter', 'is_active' => true]);
    MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->addDays(7),
    ]);

    $this->actingAs($user);

    Livewire::test(MaintenanceSchedule::class)
        ->assertSee('Change HVAC Filter');
});

test('does not show other users pending occurrences', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $assetB = Asset::factory()->create(['user_id' => $userB->id]);
    $taskB = MaintenanceTask::factory()->create(['user_id' => $userB->id, 'asset_id' => $assetB->id, 'name' => 'Other User Task', 'is_active' => true]);
    MaintenanceOccurrence::factory()->pending()->create(['maintenance_task_id' => $taskB->id]);

    $this->actingAs($userA);

    Livewire::test(MaintenanceSchedule::class)
        ->assertDontSee('Other User Task');
});

test('occurrences are sorted by due_date ascending', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $taskA = MaintenanceTask::factory()->create(['user_id' => $user->id, 'asset_id' => $asset->id, 'name' => 'Task Alpha', 'is_active' => true]);
    $taskB = MaintenanceTask::factory()->create(['user_id' => $user->id, 'asset_id' => $asset->id, 'name' => 'Task Beta', 'is_active' => true]);

    MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $taskA->id,
        'due_date' => today()->addDays(14),
    ]);
    MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $taskB->id,
        'due_date' => today()->addDays(3),
    ]);

    $this->actingAs($user);

    // Task Beta (due sooner) should appear before Task Alpha
    Livewire::test(MaintenanceSchedule::class)
        ->assertSeeInOrder(['Task Beta', 'Task Alpha']);
});

test('overdue occurrence displays overdue badge', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->create(['user_id' => $user->id, 'asset_id' => $asset->id, 'is_active' => true]);
    MaintenanceOccurrence::factory()->overdue()->create(['maintenance_task_id' => $task->id]);

    $this->actingAs($user);

    Livewire::test(MaintenanceSchedule::class)
        ->assertSee('Overdue');
});

test('upcoming occurrence does not display overdue badge', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->create(['user_id' => $user->id, 'asset_id' => $asset->id, 'is_active' => true]);
    MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->addDays(7),
    ]);

    $this->actingAs($user);

    Livewire::test(MaintenanceSchedule::class)
        ->assertDontSee('Overdue');
});

test('asset filter scopes occurrences to selected asset', function () {
    $user = User::factory()->create();
    $asset1 = Asset::factory()->create(['user_id' => $user->id]);
    $asset2 = Asset::factory()->create(['user_id' => $user->id]);
    $task1 = MaintenanceTask::factory()->create(['user_id' => $user->id, 'asset_id' => $asset1->id, 'name' => 'Task for Asset One', 'is_active' => true]);
    $task2 = MaintenanceTask::factory()->create(['user_id' => $user->id, 'asset_id' => $asset2->id, 'name' => 'Task for Asset Two', 'is_active' => true]);
    MaintenanceOccurrence::factory()->pending()->create(['maintenance_task_id' => $task1->id]);
    MaintenanceOccurrence::factory()->pending()->create(['maintenance_task_id' => $task2->id]);

    $this->actingAs($user);

    Livewire::test(MaintenanceSchedule::class)
        ->call('filterByAsset', $asset1->id)
        ->assertSee('Task for Asset One')
        ->assertDontSee('Task for Asset Two');
});

test('resetting asset filter shows all occurrences', function () {
    $user = User::factory()->create();
    $asset1 = Asset::factory()->create(['user_id' => $user->id]);
    $asset2 = Asset::factory()->create(['user_id' => $user->id]);
    $task1 = MaintenanceTask::factory()->create(['user_id' => $user->id, 'asset_id' => $asset1->id, 'name' => 'Task Asset One', 'is_active' => true]);
    $task2 = MaintenanceTask::factory()->create(['user_id' => $user->id, 'asset_id' => $asset2->id, 'name' => 'Task Asset Two', 'is_active' => true]);
    MaintenanceOccurrence::factory()->pending()->create(['maintenance_task_id' => $task1->id]);
    MaintenanceOccurrence::factory()->pending()->create(['maintenance_task_id' => $task2->id]);

    $this->actingAs($user);

    Livewire::test(MaintenanceSchedule::class)
        ->call('filterByAsset', $asset1->id)
        ->assertSee('Task Asset One')
        ->assertDontSee('Task Asset Two')
        ->call('filterByAsset', null)
        ->assertSee('Task Asset One')
        ->assertSee('Task Asset Two');
});

test('shows empty state when no pending occurrences exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(MaintenanceSchedule::class)
        ->assertSee('No pending maintenance');
});
