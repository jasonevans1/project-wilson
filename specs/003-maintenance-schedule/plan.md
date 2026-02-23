# Implementation Plan: Maintenance Schedule & Task Management

**Branch**: `003-maintenance-schedule` | **Date**: 2026-02-19 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/003-maintenance-schedule/spec.md`

## Summary

Build a recurring maintenance task and schedule system for authenticated homeowners. Users create `MaintenanceTask` definitions (linked to an asset, with a recurrence unit + count model), and the system maintains exactly one pending `MaintenanceOccurrence` per task at a time. Completing an occurrence auto-generates the next, with the due date calculated from the prior scheduled date. Users view all pending/overdue occurrences in a consolidated schedule with inline overdue badges, can filter by asset, manually adjust individual occurrence due dates, and deactivate tasks (soft-delete preserving history). Data is strictly isolated per authenticated user.

## Technical Context

**Language/Version**: PHP 8.3
**Primary Dependencies**: Laravel 12, Livewire 4, Flux UI Free v2, Laravel Fortify v1
**Storage**: MariaDB 10.11 via Eloquent — 2 new tables: `maintenance_tasks`, `maintenance_occurrences`
**Testing**: Pest 4 (Feature + Unit), Playwright (E2E)
**Target Platform**: Web (existing Laravel 12 application, server-rendered Livewire)
**Project Type**: Web application
**Performance Goals**: Schedule view loads < 500ms for typical user (< 100 assets, < 500 occurrences)
**Constraints**: User data isolation enforced at query layer via `user_id` on `maintenance_tasks`; all relationship traversals eager-loaded; no `DB::` usage
**Scale/Scope**: Single-household; tens of assets, hundreds of occurrences over time

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] I. Modularity — `MaintenanceTask` and `MaintenanceOccurrence` are single-purpose models; `MaintenanceScheduler` service has a single declared purpose (due-date calculation and occurrence generation); each Livewire component is scoped to one concern
- [x] II. Separation of Concerns — all date arithmetic lives in `MaintenanceScheduler`; Livewire components delegate scheduling logic to the service; validation in dedicated Form Request classes; UI uses `<flux:*>` exclusively; routes defined in `routes/maintenance.php`
- [x] III. High Cohesion — maintenance domain grouped under `app/Livewire/Maintenance/`; enums in `app/Enums/`; follows Laravel 12 streamlined structure; `casts()` method used on models; enum cases TitleCase
- [x] IV. Information Hiding — `MaintenanceScheduler` exposes only the public methods called by Livewire; all internal helpers `protected`/`private`; explicit return types and typed parameters on every method; PHPDoc with array-shape types where applicable
- [x] V. Appropriate Coupling — dependency chain: `MaintenanceOccurrence` → `MaintenanceTask` → `Asset` → `User`; no circular dependencies; relationships eager-loaded on every schedule query; no cross-domain boundary violations

**Gate result: PASS — no violations, no complexity tracking required**

## Project Structure

### Documentation (this feature)

```text
specs/003-maintenance-schedule/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
app/
├── Enums/
│   ├── RecurrenceUnit.php                          # new — Daily, Weekly, Monthly, Yearly
│   └── (AssetCategory.php, AssetStatus.php unchanged)
│
├── Livewire/
│   └── Maintenance/                                # new namespace
│       ├── MaintenanceSchedule.php                 # consolidated schedule view (all assets + asset filter)
│       ├── MaintenanceTaskForm.php                 # create/edit task form (embedded in asset context)
│       └── MaintenanceTaskList.php                 # per-asset task list + history panel
│
├── Models/
│   ├── MaintenanceTask.php                         # new
│   ├── MaintenanceOccurrence.php                   # new
│   └── Asset.php                                   # updated: hasMany MaintenanceTask
│
├── Services/
│   └── MaintenanceScheduler.php                    # new — due-date calculation + occurrence generation
│
└── Http/
    └── Requests/
        └── Maintenance/
            ├── SaveMaintenanceTaskRequest.php       # new
            └── UpdateOccurrenceDueDateRequest.php   # new

database/
└── migrations/
    ├── ..._create_maintenance_tasks_table.php
    └── ..._create_maintenance_occurrences_table.php

database/
└── factories/
    ├── MaintenanceTaskFactory.php
    └── MaintenanceOccurrenceFactory.php

routes/
└── maintenance.php                                 # new — required in web.php

resources/views/livewire/
└── maintenance/
    ├── maintenance-schedule.blade.php
    ├── maintenance-task-form.blade.php
    └── maintenance-task-list.blade.php

tests/
├── Feature/
│   └── Maintenance/
│       ├── MaintenanceScheduleTest.php             # schedule view, filtering, overdue display
│       ├── MaintenanceTaskFormTest.php             # create task, validation, occurrence generation
│       ├── MaintenanceOccurrenceTest.php           # complete occurrence, next-date generation, due-date edit
│       └── MaintenanceTaskDeactivationTest.php     # soft deactivate, history preserved
└── Unit/
    └── Services/
        └── MaintenanceSchedulerTest.php            # pure date arithmetic (no DB)
```

**Structure Decision**: Follows the existing Laravel 12 streamlined layout exactly. The maintenance domain mirrors the `app/Livewire/Assets/` pattern with a new `Maintenance/` namespace. All recurrence date logic is extracted into `app/Services/MaintenanceScheduler.php` to keep Livewire components free of business logic and to enable pure unit testing of date arithmetic.

## Complexity Tracking

> No Constitution violations — this section left intentionally empty.
