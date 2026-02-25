# Implementation Plan: Service Record Tracking

**Branch**: `001-service-record-tracking` | **Date**: 2026-02-23 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/001-service-record-tracking/spec.md`

## Summary

Add a Service Record Tracking feature that allows authenticated homeowners to log, view, edit, and delete service history entries for their assets. Each record captures the service date, type (Maintenance/Repair/Inspection/Replacement), description, provider name, cost, and an optional warranty coverage flag with expiry date. Records are scoped per asset (1:M), with the data model structured to allow future multi-asset expansion. A new `ServiceRecordList` Livewire component provides the full CRUD interface, accessible from a dedicated route per asset and linked from the existing `AssetDetail` panel.

## Technical Context

**Language/Version**: PHP 8.3
**Primary Dependencies**: Laravel 12, Livewire 4, Flux UI Free v2, Pest 4
**Storage**: MariaDB 10.11 — one new table: `service_records`
**Testing**: Pest 4 (feature tests with `RefreshDatabase`; unit test for ServiceType enum)
**Target Platform**: Web (DDEV local, production Laravel server)
**Project Type**: Web application (Laravel monolith with Livewire reactive UI)
**Performance Goals**: Service history for an asset (100+ records) loads within 2 seconds
**Constraints**: Data isolation enforced per user; no cross-user record access; cost is nullable decimal, not required
**Scale/Scope**: Single-user scoped data; up to hundreds of records per asset expected

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] I. Modularity: `ServiceRecord` model, `ServiceRecordList` Livewire component, `ServiceRecordValidationRules` concern, and `ServiceType` enum each have a single declared purpose
- [x] II. Separation of Concerns: validation lives in `ServiceRecordValidationRules` concern (matching `AssetValidationRules` pattern); UI uses `<flux:*>` exclusively; data access is Eloquent-only; `env()` not used
- [x] III. High Cohesion: Laravel 12 directory structure followed exactly; `casts()` method used (not `$casts`); enum cases in TitleCase; each migration is self-contained with all column attributes
- [x] IV. Information Hiding: all methods have explicit return types; typed parameters throughout; PHPDoc on structured data; public surface is minimal
- [x] V. Appropriate Coupling: `ServiceRecord` eager-loads `asset` where relationship is traversed; no `DB::` usage; no circular dependencies; no `ShouldQueue` needed (no long-running operations)

*Post-design re-check: All gates pass. No violations to track.*

## Project Structure

### Documentation (this feature)

```text
specs/001-service-record-tracking/
├── plan.md              ← this file
├── research.md          ← Phase 0 output
├── data-model.md        ← Phase 1 output
├── quickstart.md        ← Phase 1 output
├── contracts/
│   └── livewire-components.md
└── tasks.md             ← Phase 2 output (/speckit.tasks — NOT created here)
```

### Source Code (repository root)

```text
app/
├── Concerns/
│   └── ServiceRecordValidationRules.php   ← new: validation rules trait
├── Enums/
│   └── ServiceType.php                    ← new: Maintenance/Repair/Inspection/Replacement
├── Livewire/
│   └── ServiceRecords/
│       └── ServiceRecordList.php          ← new: full CRUD list + inline form
├── Models/
│   └── ServiceRecord.php                  ← new: model (+ User/Asset relationship additions)

database/
├── factories/
│   └── ServiceRecordFactory.php           ← new
├── migrations/
│   └── XXXX_create_service_records_table.php  ← new
└── seeders/
    └── ServiceRecordSeeder.php            ← new

resources/views/livewire/
└── service-records/
    └── service-record-list.blade.php      ← new

routes/
├── web.php                                ← modified: require service-records.php
└── service-records.php                    ← new: GET /assets/{asset}/service-records

tests/Feature/ServiceRecords/
├── ServiceRecordCreationTest.php          ← new
├── ServiceRecordViewTest.php              ← new
├── ServiceRecordEditTest.php              ← new
└── ServiceRecordDeleteTest.php            ← new

resources/views/livewire/assets/
└── asset-detail.blade.php                 ← modified: add "View Service Records" link
```

**Structure Decision**: Single Laravel monolith (Option 1). New code lives under existing `app/Livewire/ServiceRecords/`, `app/Enums/`, and `app/Concerns/` directories matching the established feature-folder pattern.
