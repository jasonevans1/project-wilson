# Tasks: Maintenance Schedule & Task Management

**Input**: Design documents from `/specs/003-maintenance-schedule/`
**Prerequisites**: plan.md ✓, spec.md ✓, research.md ✓, data-model.md ✓, contracts/ ✓, quickstart.md ✓

**TDD enforced**: Per the Wilson Constitution, every implementation task is preceded by a test task that must be written first and verified to FAIL before implementation begins.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel with other [P] tasks in the same phase (different files, no shared dependencies)
- **[Story]**: Which user story this task belongs to (US1–US4)
- Exact file paths are included in every task description

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Wire up the routing foundation that all user story Livewire components depend on.

- [x] T001 Create `routes/maintenance.php` with an `auth` + `verified` middleware group (routes will be added per user story phase)
- [x] T002 Add `require __DIR__.'/maintenance.php';` to `routes/web.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: All data models, migrations, factories, form requests, and the core service must exist before any user story Livewire component can be built or tested.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete.

- [x] T003 [P] Create `RecurrenceUnit` backed string enum with cases `Daily`, `Weekly`, `Monthly`, `Yearly` in `app/Enums/RecurrenceUnit.php`
- [x] T004 [P] Run `php artisan make:model MaintenanceTask -mf --no-interaction`; implement full migration schema (user_id FK, asset_id FK, name, description, recurrence_unit, recurrence_count, is_active) in `database/migrations/..._create_maintenance_tasks_table.php` and full model with casts(), relationships, and active local scope in `app/Models/MaintenanceTask.php`
- [x] T005 [P] Run `php artisan make:model MaintenanceOccurrence -mf --no-interaction`; implement full migration schema (maintenance_task_id FK, due_date, completed_at, notes) in `database/migrations/..._create_maintenance_occurrences_table.php` and full model with casts(), isOverdue/isPending accessors, and task() relationship in `app/Models/MaintenanceOccurrence.php`
- [x] T006 Run `php artisan migrate --no-interaction` to apply both new migrations
- [x] T007 [P] Complete `MaintenanceTaskFactory` in `database/factories/MaintenanceTaskFactory.php` with `active()` and `inactive()` states and realistic fake data
- [x] T008 [P] Complete `MaintenanceOccurrenceFactory` in `database/factories/MaintenanceOccurrenceFactory.php` with `pending()` and `completed()` states; `completed()` state sets `completed_at = now()`
- [x] T009 [P] Add `maintenanceTasks(): HasMany` relationship (returning `MaintenanceTask`) to `app/Models/Asset.php`
- [x] T010 [P] Add `maintenanceTasks(): HasMany` relationship (returning `MaintenanceTask`) to `app/Models/User.php`
- [x] T011 [P] Run `php artisan make:request Maintenance/SaveMaintenanceTaskRequest --no-interaction`; implement `authorize()` (auth check) and `rules()` with array syntax (name required/string/max:255, asset_id required/exists, recurrence_unit required/enum, recurrence_count required/integer/min:1/max:365, description nullable, start_date nullable/date) in `app/Http/Requests/Maintenance/SaveMaintenanceTaskRequest.php`
- [x] T012 [P] Run `php artisan make:request Maintenance/UpdateOccurrenceDueDateRequest --no-interaction`; implement `authorize()` and `rules()` (due_date required/date) in `app/Http/Requests/Maintenance/UpdateOccurrenceDueDateRequest.php`
- [x] T013 [P] Run `php artisan make:test --pest --unit --no-interaction tests/Unit/Services/MaintenanceSchedulerTest`; write unit tests covering: `nextDueDate()` for all four `RecurrenceUnit` values with count=1 and count>1, month-end overflow (Jan 31 + 1 month = Feb 28), leap year (Feb 29 + 1 year = Feb 28); **verify tests FAIL before T014**
- [x] T014 Run `php artisan make:class Services/MaintenanceScheduler --no-interaction`; implement `nextDueDate(Carbon $fromDate, RecurrenceUnit $unit, int $count): Carbon` (using `addMonthsNoOverflow`/`addYearsNoOverflow`), `generateFirstOccurrence(MaintenanceTask $task, ?Carbon $startDate = null): MaintenanceOccurrence`, and `completeOccurrence(MaintenanceOccurrence $occurrence): MaintenanceOccurrence` (wrapping complete + next generation in `DB::transaction()`, using `$occurrence->due_date` as base) in `app/Services/MaintenanceScheduler.php`

**Checkpoint**: Run `php artisan test --compact tests/Unit/Services/MaintenanceSchedulerTest.php` — all tests must pass before proceeding.

---

## Phase 3: User Story 1 — Create a Recurring Maintenance Task (Priority: P1) 🎯 MVP

**Goal**: A homeowner navigates to an asset's maintenance page, fills in a task name, selects a recurrence unit and count, optionally adds a description and start date, saves the task, and the first due occurrence is automatically calculated and stored.

**Independent Test**: Navigate to `/assets/{id}/maintenance`, create a task with "Change HVAC filter" / monthly / count 1, confirm the task appears in the list with a due date one month from today (or from today if no start date given).

### Tests for User Story 1

> **Write FIRST — verify FAIL before any implementation below**

- [x] T015 [P] [US1] Run `php artisan make:test --pest --no-interaction tests/Feature/Maintenance/MaintenanceTaskFormTest`; write feature tests covering: task saved with correct recurrence_unit and recurrence_count, first `MaintenanceOccurrence` created on save with correct `due_date`, start_date defaults to today when not provided, name validation (required/max), recurrence_count validation (min:1), asset must belong to auth user, `task-created` event dispatched in `tests/Feature/Maintenance/MaintenanceTaskFormTest.php`

### Implementation for User Story 1

- [x] T016 [US1] Run `php artisan make:livewire Maintenance/MaintenanceTaskForm --no-interaction`; implement public properties (`$assetId`, `$name`, `$description`, `$recurrenceUnit = 'monthly'`, `$recurrenceCount = 1`, `$startDate`), `save()` action (validate via `SaveMaintenanceTaskRequest` rules, create `MaintenanceTask`, call `MaintenanceScheduler::generateFirstOccurrence()`, dispatch `task-created`, reset form) in `app/Livewire/Maintenance/MaintenanceTaskForm.php`
- [x] T017 [US1] Implement `MaintenanceTaskForm` blade view using Flux components: `<flux:input>` for name, `<flux:textarea>` for description, `<flux:select>` for recurrence_unit (Daily/Weekly/Monthly/Yearly options), `<flux:input type="number">` for recurrence_count, `<flux:input type="date">` for start_date, `<flux:button>` submit — with `wire:model` bindings and `wire:submit` in `resources/views/livewire/maintenance/maintenance-task-form.blade.php`
- [x] T018 [US1] Run `php artisan make:livewire Maintenance/MaintenanceTaskList --no-interaction`; implement `$asset` (route-bound), `tasks()` `#[Computed]` property (active tasks for asset eager-loading `occurrences`), `deactivateTask(int $taskId)` stub (auth check, sets `is_active = false`), listens for `task-created` event in `app/Livewire/Maintenance/MaintenanceTaskList.php`
- [x] T019 [US1] Implement `MaintenanceTaskList` blade view showing active tasks list (name, recurrence label, pending occurrence due date), embedded `<livewire:maintenance.maintenance-task-form :asset-id="$asset->id" />`, empty state when no tasks exist, using Flux card/table layout in `resources/views/livewire/maintenance/maintenance-task-list.blade.php`
- [x] T020 [US1] Add `Route::livewire('assets/{asset}/maintenance', MaintenanceTaskList::class)->name('maintenance.asset');` to `routes/maintenance.php`

**Checkpoint**: Run `php artisan test --compact tests/Feature/Maintenance/MaintenanceTaskFormTest.php` — all tests must pass. Then manually create a task at `/assets/{id}/maintenance` and confirm the occurrence is stored.

---

## Phase 4: User Story 2 — View Maintenance Schedule (Priority: P2)

**Goal**: A homeowner visits `/maintenance` and sees all pending/overdue occurrences across all their assets sorted by due date ascending. Overdue items have an inline "Overdue" badge. A dropdown filters by asset.

**Independent Test**: With 3+ tasks across 2+ assets (some overdue, some upcoming), visit `/maintenance` and confirm ascending sort, overdue badges on past-due items, and asset filter correctly scopes the list to one asset.

### Tests for User Story 2

> **Write FIRST — verify FAIL before any implementation below**

- [x] T021 [P] [US2] Run `php artisan make:test --pest --no-interaction tests/Feature/Maintenance/MaintenanceScheduleTest`; write feature tests covering: all pending occurrences listed for auth user (not other users'), sorted ascending by due_date, overdue item detection (due_date in past + not completed), asset filter shows only that asset's occurrences, empty state when no occurrences exist in `tests/Feature/Maintenance/MaintenanceScheduleTest.php`

### Implementation for User Story 2

- [x] T022 [US2] Run `php artisan make:livewire Maintenance/MaintenanceSchedule --no-interaction`; implement `$filterAssetId = null`, `occurrences()` `#[Computed]` (pending occurrences for auth user via `maintenanceTasks()->active()->with('pendingOccurrence.task.asset')`, filtered by `$filterAssetId` when set, sorted by `due_date` ASC), `assets()` `#[Computed]` (active assets for auth user), `filterByAsset(?int $assetId)` action in `app/Livewire/Maintenance/MaintenanceSchedule.php`
- [x] T023 [US2] Implement `MaintenanceSchedule` blade view: asset filter `<flux:select>` bound to `wire:model.live="filterAssetId"`, occurrences list (task name, asset name, due date, inline `<flux:badge color="red">Overdue</flux:badge>` when `$occurrence->isOverdue()`), empty state message using Flux components in `resources/views/livewire/maintenance/maintenance-schedule.blade.php`
- [x] T024 [US2] Add `Route::livewire('maintenance', MaintenanceSchedule::class)->name('maintenance.schedule');` to `routes/maintenance.php`

**Checkpoint**: Run `php artisan test --compact tests/Feature/Maintenance/MaintenanceScheduleTest.php` — all tests must pass. Confirm schedule at `/maintenance` shows correct sort and overdue badges.

---

## Phase 5: User Story 3 — Mark a Task as Complete (Priority: P2)

**Goal**: A homeowner clicks "Mark Complete" on a pending occurrence in either the schedule view or the per-asset view. The occurrence is stamped with `completed_at = now()`, a new occurrence is auto-generated with `due_date` calculated from the prior occurrence's `due_date` (not `completed_at`), and the list updates reactively without a page reload.

**Independent Test**: Create a monthly task with a start date of 2 months ago (overdue). Mark it complete. Confirm the occurrence gains `completed_at`, a new occurrence appears with `due_date = original_due_date + 1 month`, and the old occurrence disappears from the pending list — all within the same page interaction.

### Tests for User Story 3

> **Write FIRST — verify FAIL before any implementation below**

- [ ] T025 [P] [US3] Run `php artisan make:test --pest --no-interaction tests/Feature/Maintenance/MaintenanceOccurrenceTest`; write feature tests covering: `completeOccurrence` sets `completed_at`, next occurrence created with `due_date = prior due_date + interval` (NOT `completed_at + interval`), early completion does not shift next due date, completed occurrence visible in task history, cannot complete another user's occurrence (403) in `tests/Feature/Maintenance/MaintenanceOccurrenceTest.php`

### Implementation for User Story 3

- [ ] T026 [US3] Add `completeOccurrence(int $occurrenceId): void` action to `app/Livewire/Maintenance/MaintenanceSchedule.php` — load occurrence, verify `auth()->id() === $occurrence->task->user_id` (abort 403 on mismatch), call injected `MaintenanceScheduler::completeOccurrence()`, clear computed cache to trigger re-render
- [ ] T027 [US3] Add `completeOccurrence(int $occurrenceId): void` action to `app/Livewire/Maintenance/MaintenanceTaskList.php` — same pattern as T026; clear computed cache to trigger re-render
- [ ] T028 [US3] Update `resources/views/livewire/maintenance/maintenance-schedule.blade.php` and `resources/views/livewire/maintenance/maintenance-task-list.blade.php` to add a `<flux:button wire:click="completeOccurrence({{ $occurrence->id }})">Mark Complete</flux:button>` per pending occurrence row, with `wire:loading wire:target="completeOccurrence({{ $occurrence->id }})"` scoped loading indicator and `wire:key="occurrence-{{ $occurrence->id }}"` on each row

**Checkpoint**: Run `php artisan test --compact tests/Feature/Maintenance/MaintenanceOccurrenceTest.php` — all tests must pass. Confirm marking complete removes item from pending list and a new occurrence appears in the schedule.

---

## Phase 6: User Story 4 — Manually Adjust a Task's Due Date (Priority: P3)

**Goal**: On the per-asset maintenance page, a homeowner clicks an edit icon next to a pending occurrence's due date. An inline date input appears pre-filled with the current due date. On save, only that occurrence's `due_date` is updated (other occurrences unchanged). If the new date is in the past, the occurrence immediately shows as overdue.

**Independent Test**: Pick a pending occurrence. Change its due date to 4 weeks from now. Confirm the change is reflected in the list, other occurrences are unchanged, and the schedule view also reflects the new date. Then change it to a past date and confirm the overdue badge appears.

### Tests for User Story 4

> **Write FIRST — verify FAIL before any implementation below**

- [ ] T029 [P] [US4] Add tests to `tests/Feature/Maintenance/MaintenanceOccurrenceTest.php` covering: `saveOccurrenceDueDate` updates only the targeted occurrence, other occurrences unaffected, past due date immediately makes occurrence overdue, `due_date` required validation, cannot edit another user's occurrence (403)

### Implementation for User Story 4

- [ ] T030 [US4] Add to `app/Livewire/Maintenance/MaintenanceTaskList.php`: public properties `$editingOccurrenceId = null` and `$editDueDate = null`; actions `startEditDueDate(int $occurrenceId)` (sets both properties, pre-fills `$editDueDate` from occurrence's current `due_date`), `saveOccurrenceDueDate()` (validates `$editDueDate` via `UpdateOccurrenceDueDateRequest` rules, auth check, updates occurrence, resets edit state, clears computed cache), `cancelEditDueDate()` (resets both properties)
- [ ] T031 [US4] Update `resources/views/livewire/maintenance/maintenance-task-list.blade.php`: for each pending occurrence, conditionally render inline edit mode (a `<flux:input type="date">` bound to `wire:model="editDueDate"` with Save/Cancel `<flux:button>` elements) when `$editingOccurrenceId === $occurrence->id`, otherwise render due date display with an edit `<flux:button icon="pencil" wire:click="startEditDueDate({{ $occurrence->id }}">`

**Checkpoint**: Run `php artisan test --compact tests/Feature/Maintenance/MaintenanceOccurrenceTest.php` — all tests must pass. Confirm inline date editing works end-to-end on `/assets/{id}/maintenance`.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Task deactivation, navigation integration, code formatting, and final test run.

- [ ] T032 [P] Run `php artisan make:test --pest --no-interaction tests/Feature/Maintenance/MaintenanceTaskDeactivationTest`; write feature tests covering: deactivated task (`is_active = false`) excluded from `/maintenance` schedule, pending occurrence for deactivated task not generated further, completed occurrences of deactivated task still returned in history query, cannot deactivate another user's task (403) in `tests/Feature/Maintenance/MaintenanceTaskDeactivationTest.php`
- [ ] T033 Implement `deactivateTask(int $taskId): void` in `app/Livewire/Maintenance/MaintenanceTaskList.php` (auth check, sets `is_active = false`, clears computed cache); update `resources/views/livewire/maintenance/maintenance-task-list.blade.php` to add a `<flux:button variant="danger" wire:click="deactivateTask({{ $task->id }})">` per active task row
- [ ] T034 [P] Add a "Maintenance" navigation link (using `route('maintenance.schedule')`) to the main application navigation layout (locate in `resources/views/components/` or `resources/views/layouts/`)
- [ ] T035 [P] Add a "View Maintenance" link (using `route('maintenance.asset', $asset)`) to the asset detail panel in `resources/views/livewire/assets/asset-detail.blade.php` (or equivalent)
- [ ] T036 Run `vendor/bin/pint --dirty` to auto-fix formatting on all new and modified PHP files
- [ ] T037 Run `php artisan test --compact --filter=Maintenance` to execute all maintenance tests and confirm every test passes

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 — **BLOCKS all user stories**
- **US1 (Phase 3)**: Depends on Phase 2 — no dependency on US2/US3/US4
- **US2 (Phase 4)**: Depends on Phase 2 — no dependency on US1/US3/US4
- **US3 (Phase 5)**: Depends on US1 (Phase 3) and US2 (Phase 4) — `completeOccurrence` is added to components created in those phases
- **US4 (Phase 6)**: Depends on US1 (Phase 3) — editing is added to `MaintenanceTaskList` created in Phase 3
- **Polish (Phase 7)**: Depends on all story phases complete

### User Story Dependencies

```
Phase 1 (Setup)
    ↓
Phase 2 (Foundational)
    ↓           ↓
Phase 3 (US1)  Phase 4 (US2)
    ↓    ↘  ↙    ↓
    |    Phase 5 (US3)
    ↓
Phase 6 (US4)
    ↓ ↙
Phase 7 (Polish)
```

### Within Each User Story

1. Test task written and **verified to FAIL**
2. Implementation tasks (model → service → component → view)
3. Run tests to confirm they pass (Red → Green)
4. Run `pint --dirty`
5. Story checkpoint verified before moving on

### Parallel Opportunities Per Phase

**Phase 2 (Foundational)**:
```
Parallel group A: T003, T004, T005 (enum + both models)
Then: T006 (migrate — depends on T004, T005)
Parallel group B: T007, T008, T009, T010, T011, T012, T013 (factories, relationships, requests, scheduler test)
Then: T014 (scheduler implementation — depends on T003, T013)
```

**Phase 3 (US1)**:
```
Parallel group: T015 (test)
Then sequentially: T016 → T017 → T018 → T019 → T020
```

**Phase 4 (US2)**:
```
Parallel group: T021 (test) — can run alongside Phase 3 implementation
Then sequentially: T022 → T023 → T024
```

---

## Parallel Example: Phase 2 (Foundational)

```bash
# Group A — run simultaneously:
Task: "Create RecurrenceUnit enum in app/Enums/RecurrenceUnit.php"             # T003
Task: "Create MaintenanceTask migration + model"                                # T004
Task: "Create MaintenanceOccurrence migration + model"                         # T005

# After Group A completes:
Task: "Run php artisan migrate"                                                 # T006

# Group B — run simultaneously after T006:
Task: "Complete MaintenanceTaskFactory"                                        # T007
Task: "Complete MaintenanceOccurrenceFactory"                                  # T008
Task: "Add maintenanceTasks() to Asset model"                                  # T009
Task: "Add maintenanceTasks() to User model"                                   # T010
Task: "Create SaveMaintenanceTaskRequest"                                       # T011
Task: "Create UpdateOccurrenceDueDateRequest"                                  # T012
Task: "Write MaintenanceScheduler unit tests (verify FAIL)"                    # T013

# After Group B:
Task: "Implement MaintenanceScheduler service"                                 # T014
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1 (Setup)
2. Complete Phase 2 (Foundational) — **required before anything else**
3. Complete Phase 3 (US1 — Create Task)
4. **STOP AND VALIDATE**: Can create recurring tasks, first occurrence auto-generated ✓
5. User has tangible value: tasks are defined and scheduled

### Incremental Delivery

| Phase | Delivers | Testable At |
|---|---|---|
| Phase 1 + 2 | Data foundation | Unit tests only |
| + Phase 3 (US1) | Create tasks + per-asset view | `/assets/{id}/maintenance` |
| + Phase 4 (US2) | Global schedule view | `/maintenance` |
| + Phase 5 (US3) | Mark complete + auto next-occurrence | Both views |
| + Phase 6 (US4) | Inline due date editing | Per-asset view |
| + Phase 7 | Deactivation + navigation | Full app |

---

## Notes

- `[P]` tasks operate on different files with no shared pending dependencies — safe to run in parallel
- `[Story]` labels map every task to the user story from `spec.md` for full traceability
- The Wilson Constitution mandates TDD: every test task must be written and **verified to FAIL** before its paired implementation task begins
- Per `research.md`: use `addMonthsNoOverflow()` not `addMonths()` for monthly recurrence
- Per `research.md`: `completeOccurrence()` must wrap both DB writes in `DB::transaction()`
- Per `data-model.md`: next due date ALWAYS calculates from `prior due_date`, never from `completed_at`
- Per `contracts/livewire-components.md`: every component action must `abort(403)` if `auth()->id() !== $task->user_id`
