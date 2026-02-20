# Livewire Component Contracts: Maintenance Schedule & Task Management

---

## `App\Livewire\Maintenance\MaintenanceSchedule`

**Route**: `maintenance.schedule` (`GET /maintenance`)
**Purpose**: Consolidated view of all pending/overdue maintenance occurrences across the user's assets.

### Public Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$filterAssetId` | `?int` | `null` | When set, filters schedule to a single asset |

### Computed Properties (`#[Computed]`)

| Property | Return Type | Description |
|----------|-------------|-------------|
| `occurrences` | `Collection` | All pending occurrences for auth user, sorted by `due_date` ASC, eager-loaded with `task.asset`; filtered by `filterAssetId` when set |
| `assets` | `Collection` | All active assets for auth user — used to populate the filter dropdown |

### Public Actions

| Method | Parameters | Description |
|--------|------------|-------------|
| `completeOccurrence(int $occurrenceId)` | — | Marks the occurrence complete (`completed_at = now()`), calls `MaintenanceScheduler::generateNextOccurrence()`. Refreshes `occurrences`. |
| `filterByAsset(?int $assetId)` | — | Sets `$filterAssetId`; resets to all assets when null |

### Dispatched Events

| Event | Payload | When |
|-------|---------|------|
| (none) | — | Component is self-contained; no cross-component events dispatched |

### Listened Events

| Event | Handler | Source |
|-------|---------|--------|
| `task-created` | Refresh `occurrences` | `MaintenanceTaskForm` |

---

## `App\Livewire\Maintenance\MaintenanceTaskForm`

**Embedded in**: Asset detail context (`maintenance.asset` route or modal within asset detail)
**Purpose**: Create (and optionally edit) a recurring `MaintenanceTask` for a given asset.

### Public Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$assetId` | `int` | required | ID of the asset this task belongs to |
| `$name` | `string` | `''` | Task name |
| `$description` | `string` | `''` | Optional description |
| `$recurrenceUnit` | `string` | `'monthly'` | Selected `RecurrenceUnit` value |
| `$recurrenceCount` | `int` | `1` | Multiplier count (≥ 1) |
| `$startDate` | `?string` | `null` | Optional start date (defaults to today on save) |

### Public Actions

| Method | Parameters | Description |
|--------|------------|-------------|
| `save()` | — | Validates via `SaveMaintenanceTaskRequest` rules, creates `MaintenanceTask`, calls `MaintenanceScheduler::generateFirstOccurrence()`, dispatches `task-created`, resets form |

### Dispatched Events

| Event | Payload | When |
|-------|---------|------|
| `task-created` | — | After successful task creation |

---

## `App\Livewire\Maintenance\MaintenanceTaskList`

**Route**: `maintenance.asset` (`GET /assets/{asset}/maintenance`)
**Purpose**: Per-asset view showing all maintenance tasks (active and historical) with the ability to create new tasks and edit occurrence due dates.

### Public Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$asset` | `Asset` | route model | The asset whose tasks are shown (injected via route model binding) |
| `$editingOccurrenceId` | `?int` | `null` | ID of the occurrence currently being edited (inline due-date editor) |
| `$editDueDate` | `?string` | `null` | Working value for the inline due-date editor |

### Computed Properties (`#[Computed]`)

| Property | Return Type | Description |
|----------|-------------|-------------|
| `tasks` | `Collection` | Active `MaintenanceTask` records for this asset, with `pendingOccurrence` and `occurrences` (completed, latest 5) eager-loaded |

### Public Actions

| Method | Parameters | Description |
|--------|------------|-------------|
| `completeOccurrence(int $occurrenceId)` | — | Same completion logic as `MaintenanceSchedule::completeOccurrence`. Refreshes `tasks`. |
| `startEditDueDate(int $occurrenceId)` | — | Sets `$editingOccurrenceId` and pre-fills `$editDueDate` from current due date |
| `saveOccurrenceDueDate()` | — | Validates via `UpdateOccurrenceDueDateRequest`, updates `due_date` on occurrence, clears edit state |
| `cancelEditDueDate()` | — | Clears `$editingOccurrenceId` and `$editDueDate` |
| `deactivateTask(int $taskId)` | — | Sets `is_active = false` on the task; pending occurrence remains but is hidden from active schedule |

### Listened Events

| Event | Handler | Source |
|-------|---------|--------|
| `task-created` | Refresh `tasks` | `MaintenanceTaskForm` (embedded in same view) |

---

## Shared Behavior Notes

- **Authorization**: Every action that touches a model must verify `auth()->id() === $task->user_id` (or equivalent). Use `abort(403)` on mismatch.
- **Eager loading**: All queries must eager-load traversed relationships before the Livewire render cycle.
- **Reactivity**: Completion and due-date edits update the component state within the same request — no page reload.
