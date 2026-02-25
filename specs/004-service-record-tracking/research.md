# Research: Service Record Tracking

**Branch**: `004-service-record-tracking` | **Date**: 2026-02-23

## Decision 1: Cost Field Storage

**Decision**: `decimal(10, 2)` nullable column; cast to `'decimal:2'` in the model.

**Rationale**: `decimal` avoids floating-point precision errors for monetary values. Nullable allows "cost unknown" to be distinguished from $0.00 (which means "no cost incurred"). The existing `Asset` model casts dates natively; the `ServiceRecord` model follows the same cast-driven approach. No currency conversion is required (single-currency assumption per spec).

**Alternatives considered**:
- `integer` cents: Avoids float entirely but adds complexity for display/input; overkill for a home asset tracker at this scale.
- `float`: Rejected — floating-point arithmetic is unsuitable for monetary values.

---

## Decision 2: Service Type Representation

**Decision**: `ServiceType` backed string enum stored as a `string` column (not a separate lookup table).

**Rationale**: The set of service types (Maintenance, Repair, Inspection, Replacement) is fixed by the spec and unlikely to change without a code change. The existing `RecurrenceUnit` and `AssetCategory` enums follow identical patterns. A lookup table would add joins with no benefit for this closed set.

**Enum definition**:
```php
enum ServiceType: string {
    case Maintenance  = 'maintenance';
    case Repair       = 'repair';
    case Inspection   = 'inspection';
    case Replacement  = 'replacement';
}
```

**Alternatives considered**:
- Database lookup table: Over-engineered for a fixed, code-driven set.
- PHP constants: Enums are the PHP 8.1+ standard; the project already uses them.

---

## Decision 3: Warranty Storage

**Decision**: Two columns — `under_warranty` (boolean, NOT NULL, default `false`) and `warranty_expires_on` (date, nullable). No separate warranty table.

**Rationale**: The spec specifies a simple flag + optional expiry date (Q2: Option A). Keeping this on the `service_records` table avoids a join and matches the simplicity goal. The `warranty_expires_on` column is only semantically meaningful when `under_warranty` is `true`; app-layer validation enforces this.

**Alternatives considered**:
- Separate `warranties` table: Premature; spec explicitly chose the simple option.
- Single `warranty_expiry_date` nullable column (null = not under warranty): Ambiguous — null could mean "unknown" vs "not covered." Explicit boolean flag removes ambiguity.

---

## Decision 4: Authorization Approach

**Decision**: Follow the existing project pattern — ownership checks via `abort_if($record->user_id !== Auth::id(), 403)` inside Livewire component methods. No Laravel Policy class.

**Rationale**: No policies exist in the project. The existing `MaintenanceTaskList` and `AssetDetail` components use `abort_if` in `mount()` and individual action methods. Introducing a Policy for one feature would be inconsistent without a broader policy refactor (out of scope).

**Alternatives considered**:
- Laravel Policy (`ServiceRecordPolicy`): Cleaner long-term, but inconsistent with current codebase — deferred.
- Route middleware: Too coarse; does not cover record-level ownership for edit/delete.

---

## Decision 5: Component Architecture (One vs Two Components)

**Decision**: Single `ServiceRecordList` full-page component handles listing, inline create form, inline edit, and delete. No separate `ServiceRecordForm` child component.

**Rationale**: The service record form has 7 fields — manageable inline. The `MaintenanceTaskList` + `MaintenanceTaskForm` split was used because the form is shown above the list on the same page and dispatches cross-component events. For service records, the create form is a panel/section within the list view, so a single component is simpler. Editing a record toggles an inline row form (similar to `editingOccurrenceId` pattern in `MaintenanceTaskList`).

**Alternatives considered**:
- Separate `ServiceRecordForm` child: Would require event dispatching for a form that lives inside the same view — unnecessary complexity for this scope.

---

## Decision 6: Route & Navigation

**Decision**: New route `GET /assets/{asset}/service-records` named `service-records.index` in a new `routes/service-records.php` file. A "View Service Records" link added to the `AssetDetail` panel, matching the "View Maintenance" button pattern.

**Rationale**: Maintenance tasks already live at `/assets/{asset}/maintenance`. Service records are a peer concept — same asset-scoped URL pattern keeps navigation consistent. A dedicated page (vs embedding in AssetDetail) keeps each component focused (Constitution I: Modularity).

---

## Decision 7: Validation Pattern

**Decision**: `App\Concerns\ServiceRecordValidationRules` trait providing `serviceRecordRules(): array`, following the exact pattern of `AssetValidationRules`.

**Rationale**: The project uses Concern-based validation rules for Livewire components rather than standalone Form Request classes. Matching this pattern maintains consistency. Rules use array syntax per the constitution.

**Key validation rules**:
- `serviceDate`: `['required', 'date']`
- `serviceType`: `['required', Rule::enum(ServiceType::class)]`
- `description`: `['required', 'string', 'max:5000']`
- `providerName`: `['nullable', 'string', 'max:255']`
- `cost`: `['nullable', 'numeric', 'min:0', 'decimal:0,2']`
- `underWarranty`: `['boolean']`
- `warrantyExpiresOn`: `['nullable', 'date', 'required_if:underWarranty,true']`

---

## Decision 8: Asset Deletion Behaviour

**Decision**: Service records cascade-delete when their parent asset is deleted (`cascadeOnDelete()` on the `asset_id` foreign key).

**Rationale**: The spec edge case states records should be deleted along with the asset or preserved — cascade delete is simpler and consistent with how `maintenance_tasks` handles asset deletion (also cascade). No archival requirement exists in the spec.

**Alternatives considered**:
- `nullOnDelete()` (orphaned records): Breaks the 1:M invariant; records without an asset have no context.
- Soft-delete asset + records: Over-engineered; spec confirms hard-delete for service records.
