# Research: Dashboard Overview Panels

## Question 1: Should the dashboard become a Livewire component or remain a plain Blade view?

**Decision**: Convert to a Livewire component (`App\Livewire\Dashboard\DashboardOverview`).

**Rationale**: Every other interactive page in the app (Assets, Maintenance, Replacement Tracking) is a Livewire component served via `Route::livewire()`. The current `Route::view('dashboard', 'dashboard')` is a legacy scaffold placeholder. Livewire components provide `#[Computed]` properties for clean, cacheable data access, which is exactly what four read-only summary panels need.

**Alternatives considered**: Keeping the plain Blade view and injecting data via a controller; rejected because it would break consistency with every other page in the app, requiring a new controller class for a read-only page.

---

## Question 2: How should Replacement Tracking "approaching within 12 months" be queried?

**Decision**: Fetch all active user assets with `install_date` and `expected_lifespan_years` set, then filter in PHP using `$asset->install_date->copy()->addYears($asset->expected_lifespan_years)->lte(now()->addYear())`.

**Rationale**: The existing `ReplacementDashboard` component uses the same in-collection filter pattern. The column values stored (`install_date` as a date, `expected_lifespan_years` as integer) require date arithmetic that Carbon handles cleanly; this avoids complex raw SQL date calculations.

**Alternatives considered**: Pure SQL with `DATE_ADD(install_date, INTERVAL expected_lifespan_years YEAR) <= ?`; rejected for now because the existing codebase consistently uses in-PHP filtering for this domain logic, and the panel only needs a count.

---

## Question 3: How should overdue vs. due-in-7-days maintenance counts be computed?

**Decision**: Two separate counts using the `MaintenanceOccurrence` model, scoped to the authenticated user's active tasks via `whereHas`:
- **Overdue**: `completed_at IS NULL AND due_date < today()`
- **Due in 7 days**: `completed_at IS NULL AND due_date >= today() AND due_date <= today() + 7 days`

**Rationale**: The existing `MaintenanceSchedule` component shows this pattern — `whereNull('completed_at')` + `whereHas('task', fn($q) => $q->where('user_id', Auth::id())->active())`. The two counts are distinct queries on `MaintenanceOccurrence` and can be expressed as separate `#[Computed]` properties or a combined value object.

**Alternatives considered**: A single query returning all pending occurrences and splitting in PHP; rejected because two targeted `count()` queries are more efficient than loading all records when the panel only displays counts.

---

## Question 4: What is the most recent service record, and where does it link?

**Decision**: `Auth::user()->serviceRecords()->with('asset')->latest('service_date')->first()`. The panel displays the `service_date` and `asset->name`. Clicking navigates to `route('assets.index')` (the Assets list page), consistent with how service records are accessed in the existing app.

**Rationale**: Service records do not have a standalone route in the app — they are viewed per-asset on the asset detail page. There is no `route('service-records.index')`. Linking to the Assets list is the correct entry point for a user who wants to investigate service history.

**Alternatives considered**: Linking to a specific asset detail page for the most recently serviced asset; rejected because the panel's purpose is navigation to the feature area, not to a specific record.

---

## Question 5: Does a new database migration or model need to be created?

**Decision**: No new tables, migrations, or models are required.

**Rationale**: All four panels read from existing tables (`assets`, `maintenance_occurrences`, `maintenance_tasks`, `service_records`) using existing Eloquent models. The dashboard is purely a read-only aggregation of data that already exists.

---

## Question 6: Where does the `DashboardOverview` Livewire component live in the directory structure?

**Decision**: `app/Livewire/Dashboard/DashboardOverview.php` with view at `resources/views/livewire/dashboard/dashboard-overview.blade.php`.

**Rationale**: The existing Livewire components are organized by domain: `Assets/`, `Maintenance/`, `ReplacementTracking/`, `ServiceRecords/`, `Settings/`. A `Dashboard/` subdirectory follows this same convention for the dashboard domain.
