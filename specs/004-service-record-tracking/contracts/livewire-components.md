# Livewire Component Contracts: Service Record Tracking

**Branch**: `004-service-record-tracking` | **Date**: 2026-02-23

> This project is a Livewire monolith (not a REST API). "Contracts" describe routes, Livewire component public interfaces, and inter-component events.

---

## Route Contract

**File**: `routes/service-records.php`

| Method | URI                              | Component                              | Name                    | Middleware          |
|--------|----------------------------------|----------------------------------------|-------------------------|---------------------|
| GET    | `/assets/{asset}/service-records` | `App\Livewire\ServiceRecords\ServiceRecordList` | `service-records.index` | `auth`, `verified`  |

**Loaded from** `routes/web.php` via `require __DIR__.'/service-records.php';`

---

## Component: `ServiceRecordList`

**Class**: `App\Livewire\ServiceRecords\ServiceRecordList`
**View**: `resources/views/livewire/service-records/service-record-list.blade.php`
**Route**: `service-records.index`

### Props (mounted from route)

| Property | Type    | Description                       |
|----------|---------|-----------------------------------|
| `$asset` | `Asset` | The asset whose records are shown. Ownership verified in `mount()`. |

### Public State

| Property              | Type      | Default | Description                                    |
|-----------------------|-----------|---------|------------------------------------------------|
| `$showForm`           | bool      | false   | Whether the create form is expanded            |
| `$editingRecordId`    | ?int      | null    | ID of the record currently being edited inline |
| `$serviceDate`        | string    | ''      | Form field: service date (Y-m-d)               |
| `$serviceType`        | string    | ''      | Form field: service type enum value            |
| `$description`        | string    | ''      | Form field: description/notes                  |
| `$providerName`       | string    | ''      | Form field: provider name                      |
| `$cost`               | ?string   | null    | Form field: cost (string for input binding)    |
| `$underWarranty`      | bool      | false   | Form field: warranty flag                      |
| `$warrantyExpiresOn`  | ?string   | null    | Form field: warranty expiry date (Y-m-d)       |

### Computed Properties

| Property    | Returns                          | Description                                        |
|-------------|----------------------------------|----------------------------------------------------|
| `$records`  | `Collection<ServiceRecord>`      | Service records for the asset, ordered by `service_date` desc, eager-loaded with no extra relations needed |

### Public Actions

| Method                    | Parameters       | Description                                             |
|---------------------------|------------------|---------------------------------------------------------|
| `showCreateForm()`        | —                | Resets form fields, sets `$showForm = true`             |
| `cancelForm()`            | —                | Sets `$showForm = false`, resets all form fields        |
| `saveRecord()`            | —                | Validates + creates new `ServiceRecord`; resets form    |
| `startEdit(int $id)`      | record ID        | Populates form fields from record, sets `$editingRecordId` |
| `cancelEdit()`            | —                | Clears `$editingRecordId` and form fields               |
| `updateRecord()`          | —                | Validates + updates the record at `$editingRecordId`    |
| `deleteRecord(int $id)`   | record ID        | Hard-deletes the record after ownership check; triggered via `wire:confirm` |

### Authorization

All actions verify `$record->user_id === Auth::id()` (abort 403 otherwise). `mount()` verifies `$asset->user_id === Auth::id()`.

### Events Dispatched

| Event Name       | Payload | Consumers    | Description                              |
|------------------|---------|--------------|------------------------------------------|
| (none external)  | —       | —            | All state is managed within this component |

---

## Modified Component: `AssetDetail`

**File**: `resources/views/livewire/assets/asset-detail.blade.php`

A "View Service Records" button is added to the action bar in the asset detail panel, matching the existing "View Maintenance" button pattern:

```blade
<flux:button variant="ghost" size="sm"
    :href="route('service-records.index', $asset)"
    wire:navigate>
    {{ __('View Service Records') }}
</flux:button>
```

No changes to `AssetDetail.php` — view-only modification.

---

## Validation Rules (Concern)

**Class**: `App\Concerns\ServiceRecordValidationRules`

| Field              | Rules                                                                 |
|--------------------|-----------------------------------------------------------------------|
| `serviceDate`      | `required`, `date`                                                    |
| `serviceType`      | `required`, `Rule::enum(ServiceType::class)`                          |
| `description`      | `required`, `string`, `max:5000`                                      |
| `providerName`     | `nullable`, `string`, `max:255`                                       |
| `cost`             | `nullable`, `numeric`, `min:0`, `decimal:0,2`                         |
| `underWarranty`    | `boolean`                                                             |
| `warrantyExpiresOn`| `nullable`, `date`, `required_if:underWarranty,true`                  |
