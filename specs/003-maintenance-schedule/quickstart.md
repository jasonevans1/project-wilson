# Quickstart: Maintenance Schedule & Task Management

**Branch**: `003-maintenance-schedule`

## Prerequisites

- Feature branch `003-maintenance-schedule` checked out
- DDEV running (`ddev start`)
- Existing migrations applied (001-home-asset-crud, 002-asset-template-system)
- At least one user and one asset seeded

## Steps to Get the Feature Running

### 1. Run Migrations

```bash
ddev exec --raw php artisan migrate
```

This creates `maintenance_tasks` and `maintenance_occurrences` tables.

### 2. (Optional) Seed Test Data

```bash
ddev exec --raw php artisan tinker
# Then:
# $user = \App\Models\User::first();
# $asset = $user->assets()->first();
# $task = $user->maintenanceTasks()->create([...]);
```

Or use factories in a seeder once created.

### 3. Build Frontend Assets

If changes to Blade/Flux templates are not reflecting:

```bash
npm run build
# or for live reloading:
npm run dev
```

### 4. Access the Feature

- **Schedule view** (all assets): `http://localhost/maintenance`
- **Per-asset view**: `http://localhost/assets/{id}/maintenance`

### 5. Run Tests

```bash
# All maintenance tests
ddev exec --raw php artisan test --compact --filter=Maintenance

# Unit tests only (fast, no DB)
ddev exec --raw php artisan test --compact tests/Unit/Services/MaintenanceSchedulerTest.php

# Specific feature test
ddev exec --raw php artisan test --compact --filter=MaintenanceTaskFormTest
```

## Key Files at a Glance

| Purpose | File |
|---------|------|
| Route definitions | `routes/maintenance.php` |
| Schedule page component | `app/Livewire/Maintenance/MaintenanceSchedule.php` |
| Task form component | `app/Livewire/Maintenance/MaintenanceTaskForm.php` |
| Per-asset list component | `app/Livewire/Maintenance/MaintenanceTaskList.php` |
| Date arithmetic service | `app/Services/MaintenanceScheduler.php` |
| Recurrence enum | `app/Enums/RecurrenceUnit.php` |
| Task model | `app/Models/MaintenanceTask.php` |
| Occurrence model | `app/Models/MaintenanceOccurrence.php` |

## Architecture Reminder

- **One pending occurrence per task at all times.** Completing an occurrence triggers `MaintenanceScheduler::generateNextOccurrence()` which creates the next one using the prior `due_date` (not `completed_at`) as the base.
- **Deactivated tasks** (`is_active = false`) are excluded from the schedule view but their completed occurrences remain queryable for history.
- **All data is user-scoped.** Every query must originate from `auth()->user()->maintenanceTasks()` or equivalent to prevent cross-user data leakage.
