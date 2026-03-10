# Contract: Dashboard Overview Component

This is a server-rendered Livewire component. There are no REST API endpoints — all data is provided via `#[Computed]` properties rendered on page load.

## Component: `App\Livewire\Dashboard\DashboardOverview`

**Route**: `GET /dashboard` (named `dashboard`) — auth + verified middleware
**View**: `livewire.dashboard.dashboard-overview`

---

## Computed Properties

### `assetCount(): int`

Returns the count of active (non-archived) assets owned by the authenticated user.

```
Input:  Auth::id()
Output: int (≥ 0)
```

### `overdueMaintenanceCount(): int`

Returns the count of incomplete maintenance occurrences whose due date is before today, scoped to the authenticated user's active tasks.

```
Input:  Auth::id(), today()
Output: int (≥ 0)
```

### `upcomingMaintenanceCount(): int`

Returns the count of incomplete maintenance occurrences due between today and 7 days from now (inclusive), scoped to the authenticated user's active tasks.

```
Input:  Auth::id(), today(), today()->addDays(7)
Output: int (≥ 0)
```

### `latestServiceRecord(): ?ServiceRecord` (with `asset` eager-loaded)

Returns the most recently dated service record for the authenticated user, or `null` if none exist.

```
Input:  Auth::id()
Output: ServiceRecord{service_date, asset{name}} | null
```

### `approachingReplacementCount(): int`

Returns the count of active assets with replacement tracking configured whose calculated replacement date falls on or before 12 months from today. Returns `null` (triggers empty state) when no assets have replacement tracking configured at all.

```
Input:  Auth::id(), now(), now()->addYear()
Output: int (≥ 0) | null (no tracking configured)
```

---

## Navigation Links per Panel

| Panel | Target Route | Named Route |
|---|---|---|
| Assets | `/assets` | `assets.index` |
| Maintenance | `/maintenance` | `maintenance.schedule` |
| Service Records | `/assets` | `assets.index` |
| Replacement Tracking | `/replacement-tracking` | `replacement-tracking` |

---

## State Matrix

| Panel | Condition | Display |
|---|---|---|
| Assets | `assetCount > 0` | "{N} active assets" + link |
| Assets | `assetCount == 0` | Empty state + "Add your first asset" link |
| Maintenance | Either count > 0 | "{N} overdue · {M} due this week" + link |
| Maintenance | Both counts == 0 AND user has tasks | "All caught up" + link |
| Maintenance | Both counts == 0 AND no tasks exist | Empty state + "Create your first task" link |
| Service Records | `latestServiceRecord != null` | "{date} · {asset name}" + link |
| Service Records | `latestServiceRecord == null` | Empty state + "Log your first service record" link |
| Replacement Tracking | `approachingReplacementCount > 0` | "{N} assets need attention" + link |
| Replacement Tracking | `approachingReplacementCount == 0 AND tracking configured` | "All assets within lifespan" + link |
| Replacement Tracking | `approachingReplacementCount == null` (no tracking configured) | Empty state + "Set up replacement tracking" link |
