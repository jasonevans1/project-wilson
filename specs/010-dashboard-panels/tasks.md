# Tasks: Dashboard Overview Panels

**Input**: Design documents from `/specs/010-dashboard-panels/`
**Prerequisites**: plan.md ✅, spec.md ✅, research.md ✅, data-model.md ✅, contracts/ ✅, quickstart.md ✅

**TDD**: Tests are written first and verified to fail before implementation (non-negotiable per project constitution).

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2)

---

## Phase 1: Setup (Scaffold)

**Purpose**: Create the new Livewire component and test file shells using Artisan.

- [x] T001 Scaffold Livewire component via `php artisan make:livewire Dashboard/DashboardOverview --no-interaction` — creates `app/Livewire/Dashboard/DashboardOverview.php` and `resources/views/livewire/dashboard/dashboard-overview.blade.php`
- [x] T00X [P] Scaffold Pest feature test via `php artisan make:test --pest Dashboard/DashboardOverviewTest --no-interaction` — creates `tests/Feature/Dashboard/DashboardOverviewTest.php`

**Checkpoint**: Both scaffold files exist and the project compiles without errors.

---

## Phase 2: Foundational (Route Wiring)

**Purpose**: Connect the new Livewire component to the dashboard route so all subsequent tests run against the correct component.

**⚠️ CRITICAL**: Route must be updated before any HTTP-level tests can be validated.

- [x] T00X Update `routes/web.php` — change `Route::view('dashboard', 'dashboard')` to `Route::livewire('dashboard', \App\Livewire\Dashboard\DashboardOverview::class)` with `['auth', 'verified']` middleware and `->name('dashboard')`
- [x] T00X Add `render()` method to `app/Livewire/Dashboard/DashboardOverview.php` returning `view('livewire.dashboard.dashboard-overview')->layout('layouts.app')` — matches the pattern used by `MaintenanceSchedule` and `ReplacementDashboard`

**Checkpoint**: `php artisan test --compact tests/Feature/DashboardTest.php` still passes (existing auth redirect + access tests remain green).

---

## Phase 3: User Story 1 — Homeowner Sees Live Summary Panels (Priority: P1) 🎯 MVP

**Goal**: Each of the four panels (Assets, Maintenance, Service Records, Replacement Tracking) displays the correct live metric when the authenticated user has data.

**Independent Test**: Seed the database with an asset, an overdue maintenance occurrence, a service record, and a replacement-configured asset due within 12 months. Load the dashboard and assert each panel displays its expected metric value.

### Tests for User Story 1 ⚠️ Write first — verify FAIL before implementing

- [x] T00X [US1] In `tests/Feature/Dashboard/DashboardOverviewTest.php`: write test `assets panel shows count of active assets` — creates 3 active assets + 1 archived for user; asserts component shows `3` (not `4`)
- [x] T00X [US1] In `tests/Feature/Dashboard/DashboardOverviewTest.php`: write test `maintenance panel shows overdue count` — creates an active task with a pending occurrence due yesterday; asserts component shows `1` overdue
- [x] T00X [US1] In `tests/Feature/Dashboard/DashboardOverviewTest.php`: write test `maintenance panel shows upcoming 7-day count` — creates an active task with a pending occurrence due in 3 days; asserts component shows `1` upcoming
- [x] T00X [US1] In `tests/Feature/Dashboard/DashboardOverviewTest.php`: write test `service records panel shows most recent service record` — creates two service records for different dates; asserts the panel shows the most recent date and asset name
- [x] T00X [US1] In `tests/Feature/Dashboard/DashboardOverviewTest.php`: write test `replacement tracking panel shows count of assets approaching replacement` — creates an asset with `install_date` set 10 years ago and `expected_lifespan_years = 10`; asserts panel shows `1`
- [x] T0XX [US1] In `tests/Feature/Dashboard/DashboardOverviewTest.php`: write test `dashboard does not show other users data` — creates data for user B; asserts user A sees `0` / `null` across all panels

### Implementation for User Story 1

- [x] T0XX [US1] Implement `assetCount(): int` computed property in `app/Livewire/Dashboard/DashboardOverview.php` — `Auth::user()->assets()->where('status', AssetStatus::Active)->count()`; add `use App\Enums\AssetStatus` import
- [x] T0XX [US1] Implement `overdueMaintenanceCount(): int` computed property in `app/Livewire/Dashboard/DashboardOverview.php` — query `MaintenanceOccurrence` where `completed_at IS NULL`, `due_date < today()`, scoped to auth user's active tasks via `whereHas`
- [x] T0XX [US1] Implement `upcomingMaintenanceCount(): int` computed property in `app/Livewire/Dashboard/DashboardOverview.php` — query `MaintenanceOccurrence` where `completed_at IS NULL`, `due_date BETWEEN today() AND today()->addDays(7)`, scoped to auth user's active tasks via `whereHas`
- [x] T0XX [US1] Implement `latestServiceRecord(): ?ServiceRecord` computed property in `app/Livewire/Dashboard/DashboardOverview.php` — `Auth::user()->serviceRecords()->with('asset')->latest('service_date')->first()`; add `use App\Models\ServiceRecord` import
- [x] T0XX [US1] Implement `approachingReplacementCount(): ?int` computed property in `app/Livewire/Dashboard/DashboardOverview.php` — fetch active user assets with both `install_date` and `expected_lifespan_years` set; return `null` if none (no tracking configured), otherwise count those where `install_date->copy()->addYears($asset->expected_lifespan_years)->lte(now()->addYear())`
- [x] T0XX [US1] Build the four data-state panels in `resources/views/livewire/dashboard/dashboard-overview.blade.php` — replace placeholder content with four `<flux:card>` (or equivalent) panels using `$assetCount`, `$overdueMaintenanceCount`, `$upcomingMaintenanceCount`, `$latestServiceRecord`, and `$approachingReplacementCount`; each panel includes a title, the metric, and a `wire:navigate` link to its named route (`assets.index`, `maintenance.schedule`, `replacement-tracking`)

**Checkpoint**: All 6 US1 tests pass. Run: `php artisan test --compact tests/Feature/Dashboard/DashboardOverviewTest.php --filter="does not show other|assets panel|maintenance panel|service records panel|replacement tracking panel shows count"`

---

## Phase 4: User Story 2 — New User Sees Helpful Empty States (Priority: P2)

**Goal**: Every panel shows a descriptive message and a call-to-action link when the authenticated user has no data in that feature area.

**Independent Test**: Create a fresh user with no assets, tasks, service records, or replacement tracking. Load the dashboard and assert each panel displays an empty-state message and a working link to get started.

### Tests for User Story 2 ⚠️ Write first — verify FAIL before implementing

- [x] T0XX [US2] In `tests/Feature/Dashboard/DashboardOverviewTest.php`: write test `assets panel shows empty state when user has no assets` — fresh user with no assets; asserts panel shows a prompt to add their first asset and links to `route('assets.index')`
- [x] T0XX [US2] In `tests/Feature/Dashboard/DashboardOverviewTest.php`: write test `maintenance panel shows empty state when user has no tasks` — fresh user; asserts panel shows a prompt to create their first task and links to `route('maintenance.schedule')`
- [x] T0XX [US2] In `tests/Feature/Dashboard/DashboardOverviewTest.php`: write test `service records panel shows empty state when user has no service records` — fresh user; asserts panel shows a prompt to log their first service record and links to `route('assets.index')`
- [x] T0XX [US2] In `tests/Feature/Dashboard/DashboardOverviewTest.php`: write test `replacement tracking panel shows empty state when no tracking is configured` — fresh user (no replacement tracking); asserts panel shows a prompt to set up tracking and links to `route('replacement-tracking')`
- [x] T0XX [US2] In `tests/Feature/Dashboard/DashboardOverviewTest.php`: write test `replacement tracking panel shows all-clear state when tracking configured but none approaching` — asset with `install_date` = 1 year ago and `expected_lifespan_years = 20`; asserts panel shows count `0` with a positive "all within lifespan" message (not an empty state)

### Implementation for User Story 2

- [x] T0XX [US2] Add empty state conditional branches to the Assets, Maintenance, Service Records, and Replacement Tracking panels in `resources/views/livewire/dashboard/dashboard-overview.blade.php` — use `@if` / `@else` to show either the metric or the empty-state message + CTA link per panel
- [x] T0XX [US2] Add the Replacement Tracking "all-clear" conditional branch in `resources/views/livewire/dashboard/dashboard-overview.blade.php` — when `$approachingReplacementCount === 0` (but not `null`), show a positive status message instead of the empty state or the count alert

**Checkpoint**: All 5 US2 tests pass. Run: `php artisan test --compact tests/Feature/Dashboard/DashboardOverviewTest.php --filter="empty state|all-clear"`

---

## Phase 5: Polish & Cross-Cutting Concerns

- [x] T0XX Run `vendor/bin/pint --dirty` to fix code style on all modified files (`app/Livewire/Dashboard/DashboardOverview.php`, `routes/web.php`, `resources/views/livewire/dashboard/dashboard-overview.blade.php`)
- [x] T0XX Run the full dashboard test suite to confirm all tests pass: `php artisan test --compact tests/Feature/Dashboard/ tests/Feature/DashboardTest.php`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately; T001 and T002 run in parallel
- **Foundational (Phase 2)**: Depends on Phase 1 — T003 then T004 (sequential, same file)
- **User Story 1 (Phase 3)**: Depends on Phase 2 — write all tests (T005–T010) first, verify they fail, then implement (T011–T016)
- **User Story 2 (Phase 4)**: Depends on Phase 3 being complete — write tests (T017–T021) first, verify they fail, then implement (T022–T023)
- **Polish (Phase 5)**: Depends on Phase 4 — T024 then T025

### User Story Dependencies

- **US1 (P1)**: Can start after Foundational (Phase 2) — no dependency on US2
- **US2 (P2)**: Can start after US1 is complete — shares the same view file and component, so implementing on top of the US1 work is cleanest

### TDD Order Within Each Story

1. Write all test cases for the story
2. Run the tests and confirm they **fail** (red)
3. Implement the computed properties
4. Implement the view changes
5. Run tests again and confirm they **pass** (green)
6. Refactor if needed, keeping tests green

### Parallel Opportunities

```bash
# Phase 1 — run together:
T001: php artisan make:livewire Dashboard/DashboardOverview --no-interaction
T002: php artisan make:test --pest Dashboard/DashboardOverviewTest --no-interaction

# Phase 3 implementation — computed properties are independent methods in the same class;
# implement in any order after tests are written and failing:
T011: assetCount()
T012: overdueMaintenanceCount()
T013: upcomingMaintenanceCount()
T014: latestServiceRecord()
T015: approachingReplacementCount()
```

---

## Implementation Strategy

### MVP (User Story 1 Only)

1. Complete Phase 1: Setup (scaffold)
2. Complete Phase 2: Foundational (route wiring)
3. Write US1 tests → verify they fail
4. Complete Phase 3: US1 implementation
5. **STOP and VALIDATE**: `php artisan test --compact tests/Feature/Dashboard/`
6. Demo: four live summary panels showing real data ✅

### Incremental Delivery

1. Setup + Foundational → component wired to route
2. US1 → live data panels for all 4 feature areas (MVP)
3. US2 → empty states for new users
4. Polish → code style + final test run

### Notes

- The existing `tests/Feature/DashboardTest.php` (2 basic auth tests) must remain passing throughout — the route name `dashboard` is preserved, only the handler changes from a plain view to a Livewire component
- All computed properties must be scoped to `Auth::user()` — never query globally
- The `latestServiceRecord()` computed property **must** eager-load `asset` to avoid N+1 when the view accesses `$latestServiceRecord->asset->name`
- `approachingReplacementCount()` returns `null` (not `0`) when no assets have replacement tracking configured — the view uses this distinction to differentiate "no tracking" (empty state) from "tracking set but nothing due" (all-clear state)
