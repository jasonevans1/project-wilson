# Quickstart: Replacement Tracking

**Branch**: `007-replacement-tracking` | **Date**: 2026-03-04

## Prerequisites

- Existing models: `User`, `Asset` (with `install_date`, `category`), `AssetCategory` enum
- Database queue driver already configured
- Notification infrastructure from 006-notification-system in place
- `maintenance_reminders_enabled` column on `users` as the established opt-out pattern

---

## New Files to Create

### Models & Database

| File | Command | Purpose |
|------|---------|---------|
| `app/Models/AssetReplacementEvent.php` | `php artisan make:model AssetReplacementEvent -mf --no-interaction` | Replacement event history |
| `app/Models/AssetReplacementAlert.php` | `php artisan make:model AssetReplacementAlert -mf --no-interaction` | Alert dedup tracking |
| `app/Enums/ReplacementAlertType.php` | `php artisan make:class app/Enums/ReplacementAlertType --no-interaction` (manual enum) | TwoYear, OneYear, Overdue |
| `app/Services/AssetLifespanDefaults.php` | `php artisan make:class app/Services/AssetLifespanDefaults --no-interaction` | Default lifespan map by AssetCategory |
| Migration (assets columns) | `php artisan make:migration add_replacement_columns_to_assets_table --no-interaction` | `expected_lifespan_years`, `replacement_alerts_enabled` |
| Migration (users column) | `php artisan make:migration add_replacement_alerts_enabled_to_users_table --no-interaction` | `replacement_alerts_enabled` |

### Livewire Components

| File | Command | Purpose |
|------|---------|---------|
| `app/Livewire/ReplacementTracking/ReplacementDashboard.php` | `php artisan make:livewire ReplacementTracking/ReplacementDashboard --no-interaction` | Main dashboard page |
| `app/Livewire/ReplacementTracking/ReplacementSetupForm.php` | `php artisan make:livewire ReplacementTracking/ReplacementSetupForm --no-interaction` | Inline lifespan config form |
| `app/Livewire/ReplacementTracking/RecordReplacementForm.php` | `php artisan make:livewire ReplacementTracking/RecordReplacementForm --no-interaction` | Inline record replacement form |

### Form Requests

| File | Command | Purpose |
|------|---------|---------|
| `app/Http/Requests/Replacement/SaveReplacementSetupRequest.php` | `php artisan make:request Replacement/SaveReplacementSetupRequest --no-interaction` | Lifespan + install date validation |
| `app/Http/Requests/Replacement/RecordReplacementRequest.php` | `php artisan make:request Replacement/RecordReplacementRequest --no-interaction` | Replacement event validation |

### Notifications & Commands

| File | Command | Purpose |
|------|---------|---------|
| `app/Notifications/ReplacementAlertNotification.php` | `php artisan make:notification ReplacementAlertNotification --no-interaction` | Single alert email (all 3 types) |
| `app/Console/Commands/SendReplacementAlerts.php` | `php artisan make:command SendReplacementAlerts --no-interaction` | Daily replacement alert job |
| `app/Http/Controllers/ReplacementAlertDismissController.php` | `php artisan make:controller ReplacementAlertDismissController --no-interaction` | Handle signed dismiss URLs |

### Routes

| File | Addition |
|------|----------|
| `routes/replacement-tracking.php` | New file: dashboard route + dismiss signed route |
| `routes/web.php` | `require __DIR__.'/replacement-tracking.php';` |
| `routes/console.php` | Schedule `replacement:send-alerts` daily |

### Tests

| File | Command | Coverage |
|------|---------|----------|
| `tests/Feature/ReplacementDashboardTest.php` | `php artisan make:test ReplacementDashboardTest --pest --no-interaction` | Dashboard render, sort order, untracked CTA |
| `tests/Feature/ReplacementSetupFormTest.php` | `php artisan make:test ReplacementSetupFormTest --pest --no-interaction` | Lifespan config save, default pre-fill, validation |
| `tests/Feature/RecordReplacementFormTest.php` | `php artisan make:test RecordReplacementFormTest --pest --no-interaction` | Replacement record, timeline reset, alert cycle reset |
| `tests/Feature/SendReplacementAlertsTest.php` | `php artisan make:test SendReplacementAlertsTest --pest --no-interaction` | Alert dedup, all 3 types, opt-out, dismissed skip |
| `tests/Feature/ReplacementAlertDismissTest.php` | `php artisan make:test ReplacementAlertDismissTest --pest --no-interaction` | Signed URL dismiss, invalid sig, idempotent |
| `tests/Unit/ReplacementAlertTypeTest.php` | `php artisan make:test ReplacementAlertTypeTest --pest --unit --no-interaction` | Enum values and labels |
| `tests/Unit/AssetLifespanDefaultsTest.php` | `php artisan make:test AssetLifespanDefaultsTest --pest --unit --no-interaction` | Default values per category, null for Other |

---

## Implementation Order

1. **Database layer**: Migrations (assets columns, users column, `asset_replacement_events`, `asset_replacement_alerts`) + models + factories
2. **`ReplacementAlertType` enum** + **`AssetLifespanDefaults` service**
3. **`ReplacementSetupForm`** Livewire component + `SaveReplacementSetupRequest`
4. **`RecordReplacementForm`** Livewire component + `RecordReplacementRequest`
5. **`ReplacementDashboard`** Livewire component (composes setup + record forms)
6. **Routes** (`routes/replacement-tracking.php`, include in `web.php`)
7. **Navigation update** — add "Replacement Tracking" link to main nav
8. **`ReplacementAlertNotification`** + **`SendReplacementAlerts` command** + schedule registration
9. **`ReplacementAlertDismissController`** + dismiss signed route
10. **Extend `Settings\Notifications`** — add global `replacement_alerts_enabled` toggle

---

## Key Patterns to Follow

- **Eager loading**: Dashboard queries must use `->with(['replacementEvents' => fn($q) => $q->latest('installed_at')->limit(1)])` to avoid N+1.
- **Asset ownership**: Always scope queries to `Auth::user()->assets()` — never query `Asset::query()` directly in Livewire components.
- **Computed values**: Days remaining, useful life %, replacement date are calculated in PHP/Blade — not stored columns.
- **Alert cycle reset**: When `RecordReplacementForm::save()` completes, call `AssetReplacementAlert::where('asset_id', $asset->id)->delete()` before dispatching `replacement-recorded`.
- **Signed URLs**: Use `URL::signedRoute('replacement.alert.dismiss', ['alert' => $alert->id], now()->addDays(30))` in the notification.
- **Factory states**: Create `pending`, `sent`, `dismissed` states on `AssetReplacementAlertFactory`; `withCost`, `withNotes` states on `AssetReplacementEventFactory`.
- **Pint**: Run `vendor/bin/pint --dirty` before finalizing each file group.
