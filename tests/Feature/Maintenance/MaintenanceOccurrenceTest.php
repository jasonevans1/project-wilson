<?php

use App\Livewire\Maintenance\MaintenanceSchedule;
use App\Livewire\Maintenance\MaintenanceTaskList;
use App\Models\Asset;
use App\Models\MaintenanceOccurrence;
use App\Models\MaintenanceTask;
use App\Models\User;
use Livewire\Livewire;

// ─── completeOccurrence via MaintenanceSchedule ───────────────────────────────

test('completeOccurrence sets completed_at on the occurrence', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'recurrence_unit' => 'monthly',
        'recurrence_count' => 1,
        'is_active' => true,
    ]);
    $occurrence = MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->addDays(7),
    ]);

    $this->actingAs($user);

    Livewire::test(MaintenanceSchedule::class)
        ->call('completeOccurrence', $occurrence->id);

    expect($occurrence->fresh()->completed_at)->not->toBeNull();
});

test('next occurrence is created with due_date calculated from prior due_date not completed_at', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'recurrence_unit' => 'monthly',
        'recurrence_count' => 1,
        'is_active' => true,
    ]);
    $dueDate = today()->subMonths(2); // overdue 2 months ago
    $occurrence = MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => $dueDate,
    ]);

    $this->actingAs($user);

    Livewire::test(MaintenanceSchedule::class)
        ->call('completeOccurrence', $occurrence->id);

    $nextOccurrence = MaintenanceOccurrence::where('maintenance_task_id', $task->id)
        ->whereNull('completed_at')
        ->first();

    expect($nextOccurrence)->not->toBeNull();
    // Next due date must be based on original due_date, not today (completed_at)
    expect($nextOccurrence->due_date->toDateString())
        ->toBe($dueDate->copy()->addMonthNoOverflow()->toDateString());
});

test('early completion does not shift the next due date forward', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'recurrence_unit' => 'monthly',
        'recurrence_count' => 1,
        'is_active' => true,
    ]);
    $dueDate = today()->addWeeks(2); // completing early (2 weeks before due)
    $occurrence = MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => $dueDate,
    ]);

    $this->actingAs($user);

    Livewire::test(MaintenanceSchedule::class)
        ->call('completeOccurrence', $occurrence->id);

    $nextOccurrence = MaintenanceOccurrence::where('maintenance_task_id', $task->id)
        ->whereNull('completed_at')
        ->first();

    // Must be due_date + 1 month, not today + 1 month
    expect($nextOccurrence->due_date->toDateString())
        ->toBe($dueDate->copy()->addMonthNoOverflow()->toDateString());
    expect($nextOccurrence->due_date->toDateString())
        ->not->toBe(today()->addMonthNoOverflow()->toDateString());
});

test('completed occurrence record is preserved in the database', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'recurrence_unit' => 'monthly',
        'recurrence_count' => 1,
        'is_active' => true,
    ]);
    $occurrence = MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->addDays(7),
    ]);

    $this->actingAs($user);

    Livewire::test(MaintenanceSchedule::class)
        ->call('completeOccurrence', $occurrence->id);

    // The completed occurrence still exists (not hard-deleted) with completed_at set
    $completed = MaintenanceOccurrence::where('maintenance_task_id', $task->id)
        ->whereNotNull('completed_at')
        ->first();

    expect($completed)->not->toBeNull();
    expect($completed->id)->toBe($occurrence->id);
    expect($completed->completed_at)->not->toBeNull();
});

test('cannot complete another users occurrence via MaintenanceSchedule (403)', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $assetB = Asset::factory()->create(['user_id' => $userB->id]);
    $taskB = MaintenanceTask::factory()->create([
        'user_id' => $userB->id,
        'asset_id' => $assetB->id,
        'is_active' => true,
    ]);
    $occurrenceB = MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $taskB->id,
    ]);

    $this->actingAs($userA);

    Livewire::test(MaintenanceSchedule::class)
        ->call('completeOccurrence', $occurrenceB->id)
        ->assertForbidden();

    expect($occurrenceB->fresh()->completed_at)->toBeNull();
});

// ─── completeOccurrence via MaintenanceTaskList ───────────────────────────────

test('completeOccurrence via MaintenanceTaskList sets completed_at', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'recurrence_unit' => 'monthly',
        'recurrence_count' => 1,
        'is_active' => true,
    ]);
    $occurrence = MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->addDays(7),
    ]);

    $this->actingAs($user);

    Livewire::test(MaintenanceTaskList::class, ['asset' => $asset])
        ->call('completeOccurrence', $occurrence->id);

    expect($occurrence->fresh()->completed_at)->not->toBeNull();
});

test('cannot complete another users occurrence via MaintenanceTaskList (403)', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $assetA = Asset::factory()->create(['user_id' => $userA->id]);
    $assetB = Asset::factory()->create(['user_id' => $userB->id]);
    $taskB = MaintenanceTask::factory()->create([
        'user_id' => $userB->id,
        'asset_id' => $assetB->id,
        'is_active' => true,
    ]);
    $occurrenceB = MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $taskB->id,
    ]);

    $this->actingAs($userA);

    Livewire::test(MaintenanceTaskList::class, ['asset' => $assetA])
        ->call('completeOccurrence', $occurrenceB->id)
        ->assertForbidden();

    expect($occurrenceB->fresh()->completed_at)->toBeNull();
});

test('mark complete button is visible for pending occurrence in task list', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'name' => 'Service Water Heater',
        'is_active' => true,
    ]);
    MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->addDays(7),
    ]);

    $this->actingAs($user);

    Livewire::test(MaintenanceTaskList::class, ['asset' => $asset])
        ->assertSee('Mark Complete');
});

// ─── saveOccurrenceDueDate ────────────────────────────────────────────────────

test('saveOccurrenceDueDate updates the targeted occurrence due date', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'is_active' => true,
    ]);
    $occurrence = MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->addDays(7),
    ]);

    $this->actingAs($user);

    $newDate = today()->addDays(30)->toDateString();

    Livewire::test(MaintenanceTaskList::class, ['asset' => $asset])
        ->call('startEditDueDate', $occurrence->id)
        ->set('editDueDate', $newDate)
        ->call('saveOccurrenceDueDate')
        ->assertHasNoErrors();

    expect($occurrence->fresh()->due_date->toDateString())->toBe($newDate);
});

test('saveOccurrenceDueDate does not affect other occurrences', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task1 = MaintenanceTask::factory()->create(['user_id' => $user->id, 'asset_id' => $asset->id, 'is_active' => true]);
    $task2 = MaintenanceTask::factory()->create(['user_id' => $user->id, 'asset_id' => $asset->id, 'is_active' => true]);
    $occurrence1 = MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task1->id,
        'due_date' => today()->addDays(7),
    ]);
    $occurrence2 = MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task2->id,
        'due_date' => today()->addDays(14),
    ]);

    $this->actingAs($user);

    $newDate = today()->addDays(30)->toDateString();

    Livewire::test(MaintenanceTaskList::class, ['asset' => $asset])
        ->call('startEditDueDate', $occurrence1->id)
        ->set('editDueDate', $newDate)
        ->call('saveOccurrenceDueDate');

    expect($occurrence1->fresh()->due_date->toDateString())->toBe($newDate);
    expect($occurrence2->fresh()->due_date->toDateString())->toBe(today()->addDays(14)->toDateString());
});

test('past due date makes occurrence immediately overdue', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'is_active' => true,
    ]);
    $occurrence = MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->addDays(7),
    ]);

    $this->actingAs($user);

    $pastDate = today()->subDays(3)->toDateString();

    Livewire::test(MaintenanceTaskList::class, ['asset' => $asset])
        ->call('startEditDueDate', $occurrence->id)
        ->set('editDueDate', $pastDate)
        ->call('saveOccurrenceDueDate');

    expect($occurrence->fresh()->isOverdue())->toBeTrue();
});

test('saveOccurrenceDueDate requires due_date', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'is_active' => true,
    ]);
    $occurrence = MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->addDays(7),
    ]);

    $this->actingAs($user);

    Livewire::test(MaintenanceTaskList::class, ['asset' => $asset])
        ->call('startEditDueDate', $occurrence->id)
        ->set('editDueDate', '')
        ->call('saveOccurrenceDueDate')
        ->assertHasErrors(['editDueDate']);
});

test('cannot edit another users occurrence due date (403)', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $assetA = Asset::factory()->create(['user_id' => $userA->id]);
    $assetB = Asset::factory()->create(['user_id' => $userB->id]);
    $taskB = MaintenanceTask::factory()->create([
        'user_id' => $userB->id,
        'asset_id' => $assetB->id,
        'is_active' => true,
    ]);
    $occurrenceB = MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $taskB->id,
        'due_date' => today()->addDays(7),
    ]);
    $originalDate = $occurrenceB->due_date->toDateString();

    $this->actingAs($userA);

    Livewire::test(MaintenanceTaskList::class, ['asset' => $assetA])
        ->call('startEditDueDate', $occurrenceB->id)
        ->set('editDueDate', today()->addDays(30)->toDateString())
        ->call('saveOccurrenceDueDate')
        ->assertForbidden();

    expect($occurrenceB->fresh()->due_date->toDateString())->toBe($originalDate);
});

test('cancelEditDueDate resets editing state', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'is_active' => true,
    ]);
    $occurrence = MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->addDays(7),
    ]);

    $this->actingAs($user);

    Livewire::test(MaintenanceTaskList::class, ['asset' => $asset])
        ->call('startEditDueDate', $occurrence->id)
        ->assertSet('editingOccurrenceId', $occurrence->id)
        ->call('cancelEditDueDate')
        ->assertSet('editingOccurrenceId', null)
        ->assertSet('editDueDate', null);
});
