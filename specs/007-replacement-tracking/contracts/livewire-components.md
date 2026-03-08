# Livewire Component Contracts: Replacement Tracking

**Branch**: `007-replacement-tracking` | **Date**: 2026-03-04

---

## ReplacementDashboard

**Class**: `App\Livewire\ReplacementTracking\ReplacementDashboard`
**Route**: `GET /replacement-tracking` → named `replacement-tracking`
**View**: `resources/views/livewire/replacement-tracking/replacement-dashboard.blade.php`

### Public Properties

| Property | Type | Description |
|----------|------|-------------|
| `$setupAssetId` | `?int` | Asset ID currently showing the inline setup form (null = none open) |
| `$recordingAssetId` | `?int` | Asset ID currently showing the inline record-replacement form (null = none open) |

### Actions (wire:click)

| Method | Parameters | Description |
|--------|-----------|-------------|
| `openSetupForm(int $assetId)` | Asset ID | Shows inline setup form for the given asset |
| `closeSetupForm()` | — | Hides the setup form without saving |
| `openRecordForm(int $assetId)` | Asset ID | Shows inline record-replacement form |
| `closeRecordForm()` | — | Hides the record-replacement form without saving |
| `handleTrackingConfigured()` | — | Listener: refreshes asset list after setup form saves |
| `handleReplacementRecorded()` | — | Listener: refreshes asset list after replacement recorded |

### Computed / Query Logic

Assets are loaded in this order:
1. Tracked assets (have both `install_date` and `expected_lifespan_years`), ordered by days remaining ASC (overdue first — negative days remaining).
2. Untracked assets (missing `install_date` or `expected_lifespan_years`), ordered by `name` ASC.

Eager loads: `with(['replacementEvents' => fn($q) => $q->latest('installed_at')->limit(1)])`

### Dispatched Events

| Event | When |
|-------|------|
| — | (No outbound events; this is the root component) |

### Listened Events

| Event | Handler |
|-------|---------|
| `tracking-configured` | `handleTrackingConfigured()` |
| `replacement-recorded` | `handleReplacementRecorded()` |

---

## ReplacementSetupForm

**Class**: `App\Livewire\ReplacementTracking\ReplacementSetupForm`
**Parent**: Embedded inside `ReplacementDashboard` and on `AssetDetail`
**View**: `resources/views/livewire/replacement-tracking/replacement-setup-form.blade.php`

### Public Properties

| Property | Type | Description |
|----------|------|-------------|
| `$asset` | `Asset` | The asset being configured (passed via `#[Prop]`) |
| `$expectedLifespanYears` | `?int` | Bound to lifespan input (pre-filled from asset or default) |
| `$installDate` | `?string` | Bound to install date input (pre-filled from `asset->install_date`) |

### Lifecycle

- `mount(Asset $asset)`: Sets `$expectedLifespanYears` from `$asset->expected_lifespan_years ?? AssetLifespanDefaults::forCategory($asset->category)`. Sets `$installDate` from `$asset->install_date?->format('Y-m-d')`.

### Actions

| Method | Description |
|--------|-------------|
| `save()` | Validates inputs, updates `asset->expected_lifespan_years` and `asset->install_date`, dispatches `tracking-configured` |
| `cancel()` | Dispatches `close-setup-form` |

### Validation (Form Request: `SaveReplacementSetupRequest`)

| Field | Rules |
|-------|-------|
| `expectedLifespanYears` | `required|integer|min:1|max:100` |
| `installDate` | `required|date|before_or_equal:today` |

### Dispatched Events

| Event | When |
|-------|------|
| `tracking-configured` | After successful save |
| `close-setup-form` | On cancel |

---

## RecordReplacementForm

**Class**: `App\Livewire\ReplacementTracking\RecordReplacementForm`
**Parent**: Embedded inside `ReplacementDashboard` and on `AssetDetail`
**View**: `resources/views/livewire/replacement-tracking/record-replacement-form.blade.php`

### Public Properties

| Property | Type | Description |
|----------|------|-------------|
| `$asset` | `Asset` | The asset being replaced (passed via `#[Prop]`) |
| `$installedAt` | `string` | New installation date (required) |
| `$cost` | `?string` | Replacement cost (optional, stored as decimal) |
| `$notes` | `?string` | Free-text notes (optional) |
| `$expectedLifespanYears` | `?int` | Pre-filled from asset; editable on this form |

### Lifecycle

- `mount(Asset $asset)`: Pre-fills `$expectedLifespanYears` from `$asset->expected_lifespan_years`.

### Actions

| Method | Description |
|--------|-------------|
| `save()` | Validates, creates `AssetReplacementEvent`, updates asset's `install_date` and `expected_lifespan_years` (if changed), deletes all `AssetReplacementAlert` rows for this asset (cycle reset), dispatches `replacement-recorded` |
| `cancel()` | Dispatches `close-record-form` |

### Validation (Form Request: `RecordReplacementRequest`)

| Field | Rules |
|-------|-------|
| `installedAt` | `required|date|before_or_equal:today` |
| `cost` | `nullable|numeric|min:0|max:9999999.99` |
| `notes` | `nullable|string|max:1000` |
| `expectedLifespanYears` | `nullable|integer|min:1|max:100` |

### Dispatched Events

| Event | When |
|-------|------|
| `replacement-recorded` | After successful save |
| `close-record-form` | On cancel |

---

## Settings\Notifications (extended)

**Class**: `App\Livewire\Settings\Notifications` *(existing — extended)*

### Added Public Properties

| Property | Type | Description |
|----------|------|-------------|
| `$replacementAlertsEnabled` | `bool` | Global replacement alert opt-out toggle |

### Extended `mount()`

Also reads: `Auth::user()->replacement_alerts_enabled`

### Extended `updateNotificationPreferences()`

Also saves: `replacement_alerts_enabled => $this->replacementAlertsEnabled`
