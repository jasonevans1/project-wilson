# Data Model: Dashboard Overview Panels

## Overview

No new models, tables, or migrations are required. The dashboard aggregates read-only data from four existing models. This document maps each panel to the models and queries it uses.

---

## Panel 1: Assets

**Model**: `App\Models\Asset`

**Query**: Count active assets belonging to the authenticated user.

```
User → assets() → where status = 'active' → count()
```

**Key fields used**:
- `status` (cast to `AssetStatus` enum; filter on `AssetStatus::Active`)
- `user_id` (scoped via `Auth::user()->assets()`)

**Empty state trigger**: Count equals 0.

---

## Panel 2: Maintenance

**Model**: `App\Models\MaintenanceOccurrence` (via `App\Models\MaintenanceTask`)

**Two counts**:

1. **Overdue count**: Incomplete occurrences whose `due_date` is before today, belonging to active tasks owned by the authenticated user.

   ```
   MaintenanceOccurrence
     → whereNull('completed_at')
     → whereHas('task', user_id = Auth::id(), is_active = true)
     → where('due_date', '<', today())
     → count()
   ```

2. **Due in 7 days count**: Incomplete occurrences whose `due_date` falls between today (inclusive) and 7 days from now (inclusive).

   ```
   MaintenanceOccurrence
     → whereNull('completed_at')
     → whereHas('task', user_id = Auth::id(), is_active = true)
     → whereBetween('due_date', [today(), today()->addDays(7)])
     → count()
   ```

**Key fields used**:
- `completed_at` (null = pending)
- `due_date` (date comparison)
- `MaintenanceTask.user_id` (authorization scope)
- `MaintenanceTask.is_active` (exclude deactivated tasks)

**Empty state trigger**: Both counts equal 0 AND no active tasks exist for the user (i.e., the user has never created a maintenance task).

---

## Panel 3: Service Records

**Model**: `App\Models\ServiceRecord` (eager-loads `App\Models\Asset`)

**Query**: Most recent service record by service date.

```
User → serviceRecords() → with('asset') → latest('service_date') → first()
```

**Key fields used**:
- `service_date` (order by, displayed in panel)
- `Asset.name` (displayed in panel)
- `user_id` (scoped via `Auth::user()->serviceRecords()`)

**Empty state trigger**: Result is `null` (no service records exist for the user).

---

## Panel 4: Replacement Tracking

**Model**: `App\Models\Asset`

**Query**: Count active user assets with replacement tracking configured whose replacement date falls on or before 12 months from now.

Step 1 — Fetch candidates (assets with tracking configured):
```
User → assets() → where status = 'active'
               → whereNotNull('install_date')
               → whereNotNull('expected_lifespan_years')
               → get()
```

Step 2 — Filter in PHP using Carbon:
```
$asset->install_date->copy()->addYears($asset->expected_lifespan_years)->lte(now()->addYear())
```

**Key fields used**:
- `install_date` (date; base for replacement date calculation)
- `expected_lifespan_years` (integer; added to install_date)
- `status` (filter to active assets only)
- `user_id` (scoped via `Auth::user()->assets()`)

**Empty state trigger**: User has no assets with both `install_date` and `expected_lifespan_years` set (i.e., no replacement tracking configured at all).

**Zero-but-not-empty state**: User has replacement tracking configured but no assets are due within 12 months. Panel shows count of 0 with a positive status message (not an empty state).

---

## Relationship Summary

```
User
├── assets() → HasMany<Asset>
│   ├── status (AssetStatus enum)
│   ├── install_date
│   └── expected_lifespan_years
├── maintenanceTasks() → HasMany<MaintenanceTask>
│   ├── user_id
│   └── is_active
│   └── occurrences() → HasMany<MaintenanceOccurrence>
│       ├── due_date
│       └── completed_at
└── serviceRecords() → HasMany<ServiceRecord>
    ├── service_date
    └── asset() → BelongsTo<Asset>
        └── name
```

All relationships already exist on the respective models. No new relationships are required.
