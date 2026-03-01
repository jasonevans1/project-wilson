# Quickstart: Service Record Tracking

**Branch**: `004-service-record-tracking` | **Date**: 2026-02-23

## Prerequisites

- DDEV running (`ddev start`)
- On branch `001-service-record-tracking`
- Existing features (assets, maintenance) already migrated

---

## Implementation Order

Follow this sequence to avoid dependency issues. Each step can be verified independently.

### Step 1: Enum

Create `app/Enums/ServiceType.php`.

```bash
ddev exec --raw php artisan make:class app/Enums/ServiceType --no-interaction
```

Implement as a backed string enum with cases: `Maintenance`, `Repair`, `Inspection`, `Replacement`. Add a `label(): string` method. Follow `RecurrenceUnit` as reference.

---

### Step 2: Validation Concern

Create `app/Concerns/ServiceRecordValidationRules.php`.

```bash
ddev exec --raw php artisan make:class app/Concerns/ServiceRecordValidationRules --no-interaction
```

Implement as a `trait` with a `serviceRecordRules(): array` method. Follow `AssetValidationRules` as reference. See `contracts/livewire-components.md` for the full rule set.

---

### Step 3: Model, Migration, Factory, Seeder

```bash
ddev exec --raw php artisan make:model ServiceRecord -mfs --no-interaction
```

- **Migration**: See `data-model.md` for the full `Schema::create` blueprint.
- **Model**: Add `$fillable`, `casts()`, `user()`, and `asset()` relationships. Use `ServiceType` cast for `service_type`.
- **Factory**: See `data-model.md` for the factory definition. Include an `underWarranty()` state.
- **Seeder**: Create representative records for development — at least 3 per seeded asset, covering all `ServiceType` values.

Run the migration:

```bash
ddev exec --raw php artisan migrate --no-interaction
```

---

### Step 4: Model Relationship Additions

Add `serviceRecords(): HasMany` to:

- `app/Models/Asset.php`
- `app/Models/User.php`

---

### Step 5: Tests (write first — TDD)

```bash
ddev exec --raw php artisan make:test --pest ServiceRecords/ServiceRecordCreationTest --no-interaction
ddev exec --raw php artisan make:test --pest ServiceRecords/ServiceRecordViewTest --no-interaction
ddev exec --raw php artisan make:test --pest ServiceRecords/ServiceRecordEditTest --no-interaction
ddev exec --raw php artisan make:test --pest ServiceRecords/ServiceRecordDeleteTest --no-interaction
```

Write all tests to RED before implementing the component. Key coverage areas:

| Test File                        | Scenarios                                                                 |
|----------------------------------|---------------------------------------------------------------------------|
| `ServiceRecordCreationTest`      | Creates record with all fields; validates required fields; $0 cost; past date; ownership (403 for other user's asset) |
| `ServiceRecordViewTest`          | Lists records sorted by date desc; empty state; 403 for other user       |
| `ServiceRecordEditTest`          | Edits any field; validation on edit; date resort after edit              |
| `ServiceRecordDeleteTest`        | Deletes own record; cannot delete other user's record; cancel = no-op    |

---

### Step 6: Livewire Component

```bash
ddev exec --raw php artisan make:livewire ServiceRecords/ServiceRecordList --no-interaction
```

Implement `ServiceRecordList` per `contracts/livewire-components.md`. Key patterns:

- `mount()`: `abort_if($this->asset->user_id !== Auth::id(), 403)`
- `#[Computed]` `records()`: `$this->asset->serviceRecords()->orderBy('service_date', 'desc')->get()`
- Inline create form toggled by `$showForm`
- Inline edit toggled by `$editingRecordId` (same pattern as `editingOccurrenceId` in `MaintenanceTaskList`)
- `deleteRecord()` triggered from blade via `wire:confirm`

---

### Step 7: Route

Create `routes/service-records.php`:

```php
<?php

use App\Livewire\ServiceRecords\ServiceRecordList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/assets/{asset}/service-records', ServiceRecordList::class)
        ->name('service-records.index');
});
```

Add to `routes/web.php`:

```php
require __DIR__.'/service-records.php';
```

---

### Step 8: Asset Detail Link

In `resources/views/livewire/assets/asset-detail.blade.php`, add the "View Service Records" button adjacent to the "View Maintenance" button:

```blade
<flux:button variant="ghost" size="sm"
    :href="route('service-records.index', $asset)"
    wire:navigate>
    {{ __('View Service Records') }}
</flux:button>
```

---

### Step 9: Code Formatting

```bash
ddev exec --raw vendor/bin/pint --dirty
```

---

### Step 10: Run Tests

```bash
ddev exec --raw php artisan test --compact tests/Feature/ServiceRecords/
```

All tests must be green before the feature is complete.

---

## Key Files Reference

| File | Purpose |
|------|---------|
| `app/Enums/ServiceType.php` | ServiceType backed enum |
| `app/Concerns/ServiceRecordValidationRules.php` | Validation rules trait |
| `app/Models/ServiceRecord.php` | Eloquent model |
| `database/migrations/*_create_service_records_table.php` | Migration |
| `database/factories/ServiceRecordFactory.php` | Test factory |
| `app/Livewire/ServiceRecords/ServiceRecordList.php` | Full CRUD component |
| `resources/views/livewire/service-records/service-record-list.blade.php` | Blade view |
| `routes/service-records.php` | Route definition |
| `tests/Feature/ServiceRecords/` | Pest feature tests |

## Reference Components

These existing files are the best patterns to follow:

| Reference | Used for |
|-----------|----------|
| `app/Livewire/Maintenance/MaintenanceTaskList.php` | Inline edit pattern (`editingOccurrenceId`), `abort_if` auth, `#[Computed]`, `unset($this->records)` |
| `app/Concerns/AssetValidationRules.php` | Validation concern structure |
| `app/Enums/RecurrenceUnit.php` | Backed enum structure |
| `database/migrations/2026_02_20_043904_create_maintenance_tasks_table.php` | Migration style (`foreignIdFor`, cascade) |
