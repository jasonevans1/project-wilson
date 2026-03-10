# Quickstart: Dashboard Overview Panels

## What this feature does

Replaces the placeholder dashboard with four live summary panels — one each for Assets, Maintenance, Service Records, and Replacement Tracking. Each panel shows the most actionable metric for that area and links to the corresponding feature page.

## Prerequisites

All prerequisite features are already implemented:
- Assets (001-home-asset-crud)
- Maintenance Schedule (003-maintenance-schedule)
- Service Record Tracking (004-service-record-tracking)
- Replacement Tracking (007-replacement-tracking)

No database migrations are required. This feature is read-only.

## Files to Create

```
app/Livewire/Dashboard/DashboardOverview.php
resources/views/livewire/dashboard/dashboard-overview.blade.php
tests/Feature/Livewire/Dashboard/DashboardOverviewTest.php
```

## Files to Modify

```
routes/web.php   — change Route::view() to Route::livewire()
```

## Key Implementation Notes

### Route Change

```php
// Before
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// After
Route::livewire('dashboard', \App\Livewire\Dashboard\DashboardOverview::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
```

### Component Computed Properties

The component uses four `#[Computed]` properties — one per panel:

1. **`assetCount()`** — `Auth::user()->assets()->where('status', AssetStatus::Active)->count()`
2. **`overdueMaintenanceCount()`** — count of pending occurrences with `due_date < today()`
3. **`upcomingMaintenanceCount()`** — count of pending occurrences with `due_date` between today and +7 days
4. **`latestServiceRecord()`** — most recent service record with eager-loaded asset
5. **`approachingReplacementCount()`** — count/null for assets with replacement due ≤ 12 months

See `data-model.md` for exact query shapes and `contracts/dashboard-overview.md` for the full state matrix (what each panel displays per condition).

### View Structure

The view replaces the current placeholder grid in `dashboard.blade.php`. It uses `<flux:card>` or equivalent Flux UI panel components with:
- A panel title and icon
- The metric/status text
- A `<flux:link>` or `<flux:button>` navigating to the feature section
- An empty state section (different content, same panel) when no data exists

### Test Strategy

Feature tests (Pest) cover:
- Authenticated user with data → each panel shows correct metric
- Authenticated user with no data → each panel shows empty state
- Data isolation → user A cannot see user B's data
- Navigation links resolve to correct named routes

## Artisan Commands

```bash
# Create the Livewire component
php artisan make:livewire Dashboard/DashboardOverview --no-interaction

# Create the feature test
php artisan make:test --pest Livewire/Dashboard/DashboardOverviewTest --no-interaction
```
