# Implementation Plan: Dashboard Overview Panels

**Branch**: `010-dashboard-panels` | **Date**: 2026-03-09 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/010-dashboard-panels/spec.md`

## Summary

Replace the placeholder dashboard Blade view with a Livewire component (`DashboardOverview`) that renders four live summary panels — Assets, Maintenance, Service Records, and Replacement Tracking. Each panel reads from existing Eloquent models via `#[Computed]` properties and displays a contextual empty state when no data exists. No new database tables or models are required.

## Technical Context

**Language/Version**: PHP 8.3
**Primary Dependencies**: Laravel 12, Livewire 4, Flux UI Free v2
**Storage**: MariaDB 10.11 — read-only; no new tables or migrations
**Testing**: Pest 4 (feature tests)
**Target Platform**: Web (authenticated, verified users)
**Project Type**: Web application (Laravel + Livewire)
**Performance Goals**: Dashboard loads within normal page-load expectations; four `#[Computed]` queries execute on page load
**Constraints**: Data must be strictly scoped to the authenticated user; no cross-user data
**Scale/Scope**: Single-user dashboard view; reads from up to four existing tables

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] I. Modularity: `DashboardOverview` is a single-purpose Livewire component scoped to the dashboard domain
- [x] II. Separation of Concerns: data queries live in `#[Computed]` properties; UI uses `<flux:*>` components; no raw HTML inputs; routes use named helpers
- [x] III. High Cohesion: new component placed in `app/Livewire/Dashboard/` following the existing per-domain directory structure; no cross-domain files added
- [x] IV. Information Hiding: all return types declared on computed properties; no unnecessary public surface; `protected`/`private` helpers where needed
- [x] V. Appropriate Coupling: queries go through Eloquent relationships (no `DB::`); eager loading applied to `serviceRecords()->with('asset')`; no circular dependencies

**Post-Design Re-check**: All five principles satisfied. No violations to track.

## Project Structure

### Documentation (this feature)

```text
specs/010-dashboard-panels/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/
│   └── dashboard-overview.md
└── tasks.md             # Phase 2 output (/speckit.tasks — not yet created)
```

### Source Code

```text
app/
└── Livewire/
    └── Dashboard/
        └── DashboardOverview.php          ← NEW

resources/
└── views/
    └── livewire/
        └── dashboard/
            └── dashboard-overview.blade.php  ← NEW

routes/
└── web.php                                ← MODIFIED (Route::view → Route::livewire)

tests/
└── Feature/
    └── Livewire/
        └── Dashboard/
            └── DashboardOverviewTest.php  ← NEW
```

**Structure Decision**: Follows the existing per-domain Livewire layout used throughout the application (`Assets/`, `Maintenance/`, `ReplacementTracking/`, `ServiceRecords/`). The `Dashboard/` subdirectory is a new domain namespace but consistent with the pattern.

## Phase 0: Research

All unknowns resolved. See [research.md](research.md).

Key decisions:
1. Dashboard converts from `Route::view()` to `Route::livewire()` with a new `DashboardOverview` component — consistent with every other page in the app.
2. No new models, migrations, or database changes required.
3. Replacement tracking "approaching" check uses in-PHP Carbon filter (matching existing `ReplacementDashboard` pattern).
4. Maintenance counts use two separate `count()` queries on `MaintenanceOccurrence`.
5. Service record panel eager-loads `asset` to avoid N+1.

## Phase 1: Design

See [data-model.md](data-model.md) and [contracts/dashboard-overview.md](contracts/dashboard-overview.md).

### Component: `DashboardOverview`

**Computed properties**:

| Property | Returns | Query shape |
|---|---|---|
| `assetCount()` | `int` | `user->assets()->where('status', Active)->count()` |
| `overdueMaintenanceCount()` | `int` | pending occurrences with `due_date < today()` scoped to user's active tasks |
| `upcomingMaintenanceCount()` | `int` | pending occurrences with `due_date` in [today, +7 days] scoped to user's active tasks |
| `latestServiceRecord()` | `?ServiceRecord` | `user->serviceRecords()->with('asset')->latest('service_date')->first()` |
| `approachingReplacementCount()` | `int\|null` | active assets with tracking configured, filtered where replacement date ≤ now + 1 year; `null` if none configured |

### Panel State Matrix

See [contracts/dashboard-overview.md](contracts/dashboard-overview.md) for the full matrix covering all data/empty/zero states per panel.

### Navigation Links

| Panel | Named Route |
|---|---|
| Assets | `assets.index` |
| Maintenance | `maintenance.schedule` |
| Service Records | `assets.index` |
| Replacement Tracking | `replacement-tracking` |

### Test Coverage

Feature tests (Pest) covering:
- Each panel renders correct metric when data exists
- Each panel renders empty state when no data exists
- Replacement Tracking panel renders "all clear" when tracking is configured but none is approaching
- Data isolation: authenticated user cannot see another user's data
- Guest is redirected (middleware enforcement)
