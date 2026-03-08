# Research: Replacement Tracking

**Branch**: `007-replacement-tracking` | **Date**: 2026-03-04

## R-001: Lifespan Profile Storage Strategy

**Decision**: Add `expected_lifespan_years` (nullable unsigned small integer) and `replacement_alerts_enabled` (boolean, default true) as new columns on the existing `assets` table via a dedicated migration.

**Rationale**: The `assets` table already carries `install_date`, which is the other half of the lifespan profile. The profile is strictly 1:1 with an asset — there is never more than one active lifespan configuration per asset at any point in time. Adding columns is simpler, avoids an extra join on every dashboard query, and aligns with how the existing asset model already stores the `status` and `install_date` fields. A separate `asset_lifespan_profiles` table would be justified only if multiple lifespan configurations per asset were needed (they are not).

**Alternatives considered**:
- Separate `asset_lifespan_profiles` table — unnecessary join overhead, no benefit over columns given 1:1 cardinality.
- Store as JSON in an `asset_metadata` column — opaque, harder to query and index.

## R-002: Replacement Event History Storage

**Decision**: Create a dedicated `asset_replacement_events` table with columns: `asset_id`, `installed_at` (date), `cost` (decimal, nullable), `notes` (text, nullable), and standard timestamps.

**Rationale**: Replacement history is 1:many per asset (multiple replacements over time). A separate table provides clean append-only history, simple ordering by `installed_at`, and doesn't pollute the `assets` table with a repeating structure. The `installed_at` column on the event record becomes the new installation date when a replacement is logged, driving the reset of the asset's timeline.

**Alternatives considered**:
- Store history as a JSON array on `assets` — unqueryable, no referential integrity, cannot be eager-loaded cleanly.

## R-003: Default Lifespan Values by Asset Category

**Decision**: Implement a static `AssetLifespanDefaults` service class with a `forCategory(AssetCategory $category): ?int` method that returns an integer (years) or null. Defaults are category-level since the current asset model has no sub-type field.

**Rationale**: The `AssetCategory` enum has 8 cases, all of which can carry a meaningful default. A static PHP class (no DB access required) is the correct home for reference/lookup data that never changes at runtime. Using a class method (rather than a seeded DB table) avoids an extra migration, a new model, and query overhead for what is effectively a small constant map. Default values are:

| Category   | Default (years) | Basis |
|------------|-----------------|-------|
| Appliance  | 10              | Average major appliance lifespan |
| Hvac       | 15              | Central HVAC systems |
| Plumbing   | 20              | Water heaters (8–12), pipes (20–50); median |
| Electrical | 25              | Panels and wiring |
| Roofing    | 20              | Asphalt shingle (15–25); median |
| Flooring   | 25              | Hardwood/tile |
| Exterior   | 15              | Siding, windows |
| Other      | null            | Too varied; require manual entry |

**Alternatives considered**:
- Seed a `lifespan_standards` DB table — unnecessary for 8 fixed values; adds migration + model + query.
- Embed defaults in the `AssetCategory` enum itself — tighter coupling; the enum belongs to the domain layer, defaults are application/display logic.

## R-004: Replacement Alert Tracking

**Decision**: Create a dedicated `asset_replacement_alerts` table with a unique constraint on `(asset_id, alert_type)`. Introduce a `ReplacementAlertType` enum with cases `TwoYear`, `OneYear`, `Overdue`. Clearing an alert cycle on replacement event recording means deleting all `asset_replacement_alerts` rows for that asset, allowing them to be re-created on the next daily run.

**Rationale**: This mirrors the `MaintenanceReminder` dedup pattern already in production (unique per occurrence + type). Using DELETE on replacement event (rather than nullifying `sent_at`) is intentional — a replacement starts an entirely new lifecycle cycle, so old alert records are irrelevant and should not persist. The `dismissed_at` column handles the per-notification dismissal for the Overdue type specifically.

**Alternatives considered**:
- Reuse the existing `maintenance_reminders` table — structurally incompatible (that table is keyed on `maintenance_occurrence_id`).
- Single flag on `assets` table (`overdue_dismissed`) — can't track the 2-year and 1-year alerts; loses historical context.

## R-005: Daily Alert Command Architecture

**Decision**: Follow the `SendMaintenanceReminders` command pattern exactly. Create `SendReplacementAlerts` as a standalone Artisan command (no separate queued job), registered daily in `routes/console.php`. The command handles all three alert types: `TwoYear`, `OneYear`, `Overdue`.

**Rationale**: Replacement alerts are simpler than maintenance reminders — no digest grouping, no snooze, no occurrence traversal. A single command class is sufficient without a separate job. The database queue driver is already configured; notification dispatch via `User::notify()` will still queue the actual email delivery.

**Alternatives considered**:
- Separate command per alert type — unnecessary duplication.
- Queued job separate from command — over-engineering given the simpler scope.

## R-006: Notification Settings Extension

**Decision**: Add `replacement_alerts_enabled` (boolean, default true) to the `users` table via a standalone migration. Add `replacement_alerts_enabled` (boolean, default true) to the `assets` table in the same migration that adds `expected_lifespan_years` (R-001 migration). Extend `app/Livewire/Settings/Notifications.php` to include the new global toggle.

**Rationale**: The existing `maintenance_reminders_enabled` column pattern on `users` is the established convention for global notification opt-out. Per-asset opt-out follows the same principle on the `assets` table. Extending the existing `Notifications` Livewire component avoids creating a second settings component for a closely related concern.

**Alternatives considered**:
- Single global flag only (no per-asset) — the spec explicitly requires per-asset opt-out.
- Separate `notification_preferences` pivot table — overkill for two boolean flags.

## R-007: Replacement Tracking Dashboard Component

**Decision**: Create a single `ReplacementTracking\ReplacementDashboard` Livewire component routed at `GET /replacement-tracking`. The component queries all user assets (eager-loaded with `replacementEvents`), sorts tracked assets by days remaining (ascending, overdue first), appends untracked assets with a "Set up tracking" inline CTA. Lifespan configuration and replacement recording are handled by child components `ReplacementSetupForm` and `RecordReplacementForm` rendered inline.

**Rationale**: A single page component with inline child forms avoids full page reloads for configuration updates and keeps the UX tight. Livewire's `$dispatch` / `$on` event system handles parent refresh after a child form saves. This mirrors the `AssetDetail` + `AssetForm` inline editing pattern already used in the asset list.

**Alternatives considered**:
- Modal-based forms — adds complexity; inline forms are already the established project pattern.
- Separate pages per asset for replacement config — excessive navigation for what should be a quick setup action.

## R-008: Overdue Notification Dismissal

**Decision**: The `asset_replacement_alerts` table includes a `dismissed_at` (timestamp, nullable) column. Dismissal is recorded by a signed URL in the notification email, handled by a lightweight `ReplacementAlertDismissController`. The daily command skips alerts where `dismissed_at` is not null (unless the asset has been replaced, in which case the entire row is deleted).

**Rationale**: Signed URLs are already the project's established pattern for email-action handling (maintenance reminder snooze). Reusing the same pattern keeps the implementation consistent and avoids requiring the user to log in to dismiss an alert.

**Alternatives considered**:
- In-app only dismissal (requires login) — poor UX from email context.
- Separate `dismissed` boolean without timestamp — loses audit trail.
