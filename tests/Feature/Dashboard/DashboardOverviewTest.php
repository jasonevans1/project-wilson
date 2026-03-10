<?php

use App\Enums\AssetStatus;
use App\Livewire\Dashboard\DashboardOverview;
use App\Models\Asset;
use App\Models\MaintenanceOccurrence;
use App\Models\MaintenanceTask;
use App\Models\ServiceRecord;
use App\Models\User;
use Livewire\Livewire;

// ── US1: Live summary panels ───────────────────────────────────────────────

test('assets panel shows count of active assets', function () {
    $user = User::factory()->create();
    Asset::factory()->count(3)->create(['user_id' => $user->id, 'status' => AssetStatus::Active]);
    Asset::factory()->archived()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(DashboardOverview::class)
        ->assertSee('3');
});

test('maintenance panel shows overdue count', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->active()->create(['user_id' => $user->id, 'asset_id' => $asset->id]);
    MaintenanceOccurrence::factory()->overdue()->create(['maintenance_task_id' => $task->id]);

    $this->actingAs($user);

    Livewire::test(DashboardOverview::class)
        ->assertSee('1 overdue');
});

test('maintenance panel shows upcoming 7-day count', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['user_id' => $user->id]);
    $task = MaintenanceTask::factory()->active()->create(['user_id' => $user->id, 'asset_id' => $asset->id]);
    MaintenanceOccurrence::factory()->pending()->create([
        'maintenance_task_id' => $task->id,
        'due_date' => today()->addDays(3)->format('Y-m-d'),
    ]);

    $this->actingAs($user);

    Livewire::test(DashboardOverview::class)
        ->assertSee('1 upcoming');
});

test('service records panel shows most recent service record', function () {
    $user = User::factory()->create();
    $olderAsset = Asset::factory()->create(['user_id' => $user->id, 'name' => 'Old Dishwasher']);
    $newerAsset = Asset::factory()->create(['user_id' => $user->id, 'name' => 'New HVAC Unit']);

    ServiceRecord::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $olderAsset->id,
        'service_date' => '2024-01-01',
    ]);
    ServiceRecord::factory()->create([
        'user_id' => $user->id,
        'asset_id' => $newerAsset->id,
        'service_date' => '2025-06-15',
    ]);

    $this->actingAs($user);

    Livewire::test(DashboardOverview::class)
        ->assertSee('New HVAC Unit')
        ->assertDontSee('Old Dishwasher');
});

test('replacement tracking panel shows count of assets approaching replacement', function () {
    $user = User::factory()->create();
    Asset::factory()->create([
        'user_id' => $user->id,
        'name' => 'Old Refrigerator',
        'install_date' => now()->subYears(10)->format('Y-m-d'),
        'expected_lifespan_years' => 10,
        'status' => AssetStatus::Active,
    ]);

    $this->actingAs($user);

    Livewire::test(DashboardOverview::class)
        ->assertSee('1');
});

test('dashboard does not show other users data', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    // Create data for user B only
    $assetB = Asset::factory()->create(['user_id' => $userB->id, 'name' => 'User B Refrigerator']);
    $taskB = MaintenanceTask::factory()->active()->create(['user_id' => $userB->id, 'asset_id' => $assetB->id]);
    MaintenanceOccurrence::factory()->overdue()->create(['maintenance_task_id' => $taskB->id]);
    ServiceRecord::factory()->create(['user_id' => $userB->id, 'asset_id' => $assetB->id]);

    $this->actingAs($userA);

    Livewire::test(DashboardOverview::class)
        ->assertDontSee('User B Refrigerator');
});

// ── US2: Empty states ─────────────────────────────────────────────────────

test('assets panel shows empty state when user has no assets', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(DashboardOverview::class)
        ->assertSee('Add your first asset');
});

test('maintenance panel shows empty state when user has no tasks', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(DashboardOverview::class)
        ->assertSee('Create your first task');
});

test('service records panel shows empty state when user has no service records', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(DashboardOverview::class)
        ->assertSee('Log your first service record');
});

test('replacement tracking panel shows empty state when no tracking is configured', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(DashboardOverview::class)
        ->assertSee('Set up replacement tracking');
});

test('replacement tracking panel shows all-clear state when tracking configured but none approaching', function () {
    $user = User::factory()->create();
    Asset::factory()->create([
        'user_id' => $user->id,
        'install_date' => now()->subYear()->format('Y-m-d'),
        'expected_lifespan_years' => 20,
        'status' => AssetStatus::Active,
    ]);

    $this->actingAs($user);

    Livewire::test(DashboardOverview::class)
        ->assertSee('All assets within lifespan');
});
