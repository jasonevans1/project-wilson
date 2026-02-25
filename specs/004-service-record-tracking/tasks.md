# Tasks: Service Record Tracking

**Input**: Design documents from `/specs/004-service-record-tracking/`
**Branch**: `004-service-record-tracking`

**TDD Requirement (Constitution — Non-Negotiable)**: Tests are written **first**, verified to FAIL, then implementation is written. Red → Green → Refactor on every story phase.

**Organization**: Tasks are grouped by user story. Each story is independently testable and deployable.

## Format: `[ID] [P?] [Story?] Description`

- **[P]**: Can run in parallel (different files, no blocking dependencies on incomplete tasks)
- **[Story]**: Maps to User Story from spec.md (US1–US4)
- Exact file paths included in every task description

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Create the foundational enum and validation concern before any model or component work.

- [x] T001 [P] Create `app/Enums/ServiceType.php` — backed string enum with `Maintenance`, `Repair`, `Inspection`, `Replacement` cases (TitleCase) and a `label(): string` method; follow `app/Enums/RecurrenceUnit.php` as structure reference
- [x] T002 [P] Create `app/Concerns/ServiceRecordValidationRules.php` — `trait ServiceRecordValidationRules` with `serviceRecordRules(): array` method; rules for `serviceDate` (required, date), `serviceType` (required, Rule::enum), `description` (required, string, max:5000), `providerName` (nullable, string, max:255), `cost` (nullable, numeric, min:0, decimal:0,2), `underWarranty` (boolean), `warrantyExpiresOn` (nullable, date, required_if:underWarranty,true); follow `app/Concerns/AssetValidationRules.php` as structure reference

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Database table, model, factory, seeder, relationships, and route — everything US1–US4 depend on.

**⚠️ CRITICAL**: No user story work begins until this phase is complete.

- [ ] T003 Run `ddev exec --raw php artisan make:model ServiceRecord -mfs --no-interaction` to scaffold `app/Models/ServiceRecord.php`, migration stub, `database/factories/ServiceRecordFactory.php`, `database/seeders/ServiceRecordSeeder.php`
- [ ] T004 [P] Implement migration at `database/migrations/*_create_service_records_table.php` — columns: `id`, `foreignIdFor(User)->constrained()->cascadeOnDelete()`, `foreignIdFor(Asset)->constrained()->cascadeOnDelete()`, `date('service_date')`, `string('service_type')`, `text('description')`, `string('provider_name')->nullable()`, `decimal('cost', 10, 2)->nullable()`, `boolean('under_warranty')->default(false)`, `date('warranty_expires_on')->nullable()`, `timestamps()`; see `data-model.md` for full blueprint
- [ ] T005 [P] Implement `app/Models/ServiceRecord.php` — `$fillable` with all 9 fields; `casts()` returning `service_date => 'date'`, `service_type => ServiceType::class`, `cost => 'decimal:2'`, `under_warranty => 'boolean'`, `warranty_expires_on => 'date'`; `user(): BelongsTo` → `User::class`; `asset(): BelongsTo` → `Asset::class`; explicit return types on all methods
- [ ] T006 [P] Implement `database/factories/ServiceRecordFactory.php` — `definition()` with `user_id`, `asset_id`, `service_date` (past 2 years to now), `service_type` (random ServiceType case value), `description` (paragraph), `provider_name` (optional company), `cost` (optional float 0–5000), `under_warranty` (false), `warranty_expires_on` (null); `underWarranty(): static` state setting `under_warranty = true` and `warranty_expires_on` to a future date
- [ ] T007 [P] Implement `database/seeders/ServiceRecordSeeder.php` — creates ≥3 records per seeded asset covering all four `ServiceType` values; use `ServiceRecord::factory()` with appropriate states
- [ ] T008 [P] Add `serviceRecords(): HasMany` relationship returning `$this->hasMany(ServiceRecord::class)` to `app/Models/Asset.php`
- [ ] T009 [P] Add `serviceRecords(): HasMany` relationship returning `$this->hasMany(ServiceRecord::class)` to `app/Models/User.php`
- [ ] T010 Run `ddev exec --raw php artisan migrate --no-interaction` to apply the new `service_records` migration
- [ ] T011 [P] Create `routes/service-records.php` — `Route::middleware(['auth', 'verified'])->group(...)` containing `Route::get('/assets/{asset}/service-records', ServiceRecordList::class)->name('service-records.index')`
- [ ] T012 Add `require __DIR__.'/service-records.php';` to `routes/web.php` (after the existing maintenance.php require)

**Checkpoint**: Run `ddev exec --raw php artisan route:list --name=service-records` — route must appear before proceeding.

---

## Phase 3: User Story 1 — Log a Service Record (Priority: P1) 🎯 MVP

**Goal**: An authenticated homeowner can create a service record for an asset they own, with all required fields validated, and the new record immediately appears in the list.

**Independent Test**: Navigate to an asset's service records page, submit the create form with valid data, and confirm the record appears in the list. Test in isolation using `ServiceRecordCreationTest.php`.

### Tests for User Story 1 (TDD — write first, verify FAIL before T014)

- [ ] T013 [US1] Write feature tests in `tests/Feature/ServiceRecords/ServiceRecordCreationTest.php` via `ddev exec --raw php artisan make:test --pest ServiceRecords/ServiceRecordCreationTest --no-interaction`; cover: (1) authenticated user submits valid record → saved and visible, (2) omitting `service_date` → validation error, (3) omitting `service_type` → validation error, (4) omitting `description` → validation error, (5) cost = 0.00 → accepted, (6) past service_date → accepted, (7) unauthenticated access → redirect, (8) authenticated user accessing another user's asset → 403; run tests to confirm RED

### Implementation for User Story 1

- [ ] T014 [US1] Run `ddev exec --raw php artisan make:livewire ServiceRecords/ServiceRecordList --no-interaction` to scaffold `app/Livewire/ServiceRecords/ServiceRecordList.php` and `resources/views/livewire/service-records/service-record-list.blade.php`
- [ ] T015 [US1] Implement `app/Livewire/ServiceRecords/ServiceRecordList.php` — `use ServiceRecordValidationRules`; `#[Prop] public Asset $asset`; `mount(): void` with `abort_if($this->asset->user_id !== Auth::id(), 403)`; public properties: `$showForm = false`, `$serviceDate`, `$serviceType`, `$description`, `$providerName`, `$cost`, `$underWarranty = false`, `$warrantyExpiresOn`; `showCreateForm(): void` (reset fields, set `$showForm = true`); `cancelForm(): void` (set `$showForm = false`, reset fields); `saveRecord(): void` (validate via `$this->validate($this->serviceRecordRules())`, create `ServiceRecord`, reset form, `unset($this->records)`)
- [ ] T016 [US1] Implement create form section in `resources/views/livewire/service-records/service-record-list.blade.php` — page heading with asset name; "Add Service Record" button toggling `$showForm`; when `$showForm`: `<flux:card>` form with `<flux:input type="date">` for `serviceDate`, `<flux:select>` for `serviceType` (Maintenance/Repair/Inspection/Replacement options), `<flux:textarea>` for `description`, `<flux:input>` for `providerName`, `<flux:input type="number">` for `cost`, `<flux:checkbox>` for `underWarranty`, conditional `<flux:input type="date">` for `warrantyExpiresOn` shown only when `$underWarranty`; Save and Cancel buttons; `<flux:error>` for each validated field
- [ ] T017 [US1] Run `ddev exec --raw php artisan test --compact tests/Feature/ServiceRecords/ServiceRecordCreationTest.php` — all tests must be GREEN before proceeding

**Checkpoint**: US1 is independently testable and functional. A user can create service records.

---

## Phase 4: User Story 2 — View Service History for an Asset (Priority: P2)

**Goal**: An authenticated homeowner can view all service records for an asset in reverse chronological order, with an informative empty state when no records exist.

**Independent Test**: Use the factory to create multiple records for an asset, navigate to the service records page, and confirm records appear sorted newest-first with type/date/provider/cost visible. Test in isolation using `ServiceRecordViewTest.php`.

### Tests for User Story 2 (TDD — write first, verify FAIL before T019)

- [ ] T018 [US2] Write feature tests in `tests/Feature/ServiceRecords/ServiceRecordViewTest.php` via `ddev exec --raw php artisan make:test --pest ServiceRecords/ServiceRecordViewTest --no-interaction`; cover: (1) multiple records display sorted by `service_date` descending, (2) empty state message shown when asset has no records, (3) service type label, date, provider name, and cost visible in list, (4) 403 for authenticated user accessing another user's asset; run tests to confirm RED

### Implementation for User Story 2

- [ ] T019 [P] [US2] Add `#[Computed] public function records(): Collection` to `app/Livewire/ServiceRecords/ServiceRecordList.php` — returns `$this->asset->serviceRecords()->orderBy('service_date', 'desc')->get()`; add `unset($this->records)` after save, edit, and delete operations
- [ ] T020 [P] [US2] Implement service record list display in `resources/views/livewire/service-records/service-record-list.blade.php` — `@if ($this->records->isEmpty())`: empty state `<flux:card>` with "No service records yet" text and CTA button to add first record; `@else`: `@foreach ($this->records as $record)` with `<flux:card wire:key="record-{{ $record->id }}">` showing formatted `service_date` (e.g. `M j, Y`), `service_type->label()` as `<flux:badge>`, `provider_name` (if present), cost formatted as currency (show "—" if null)
- [ ] T021 [P] [US2] Add "View Service Records" button to `resources/views/livewire/assets/asset-detail.blade.php` — `<flux:button variant="ghost" size="sm" :href="route('service-records.index', $asset)" wire:navigate>{{ __('View Service Records') }}</flux:button>` adjacent to the existing "View Maintenance" button
- [ ] T022 [US2] Run `ddev exec --raw php artisan test --compact tests/Feature/ServiceRecords/ServiceRecordViewTest.php` — all tests must be GREEN before proceeding

**Checkpoint**: US1 and US2 are both independently testable. Users can create and browse service records.

---

## Phase 5: User Story 3 — Edit a Service Record (Priority: P3)

**Goal**: An authenticated homeowner can edit any field of an existing service record they own, with validation enforced, and the updated record immediately reflected in the sorted list.

**Independent Test**: Use the factory to create a record, invoke inline edit, change the cost and description, save, and confirm the updated values appear. Test in isolation using `ServiceRecordEditTest.php`.

### Tests for User Story 3 (TDD — write first, verify FAIL before T024)

- [ ] T023 [US3] Write feature tests in `tests/Feature/ServiceRecords/ServiceRecordEditTest.php` via `ddev exec --raw php artisan make:test --pest ServiceRecords/ServiceRecordEditTest --no-interaction`; cover: (1) authenticated user edits cost and description → record reflects new values, (2) editing service_date to an older date → record repositions correctly in sorted order, (3) clearing `description` during edit → validation error prevents save, (4) authenticated user attempting to edit another user's record → 403; run tests to confirm RED

### Implementation for User Story 3

- [ ] T024 [P] [US3] Add inline edit state and actions to `app/Livewire/ServiceRecords/ServiceRecordList.php` — `public ?int $editingRecordId = null`; `startEdit(int $id): void` (load record, verify ownership via `abort_if`, populate form properties, set `$editingRecordId`); `cancelEdit(): void` (reset `$editingRecordId` and form fields); `updateRecord(): void` (validate via `$this->validate($this->serviceRecordRules())`, find record with ownership check, update all fields, reset `$editingRecordId`, `unset($this->records)`)
- [ ] T025 [P] [US3] Add per-record inline edit form to `resources/views/livewire/service-records/service-record-list.blade.php` — inside each `@foreach` record card: `@if ($editingRecordId === $record->id)`: show the same Flux field set as the create form pre-populated with record values, Save/Cancel buttons calling `updateRecord()`/`cancelEdit()`; `@else`: show read-only record display with an Edit icon button `<flux:button icon="pencil" ... wire:click="startEdit({{ $record->id }})">`
- [ ] T026 [US3] Run `ddev exec --raw php artisan test --compact tests/Feature/ServiceRecords/ServiceRecordEditTest.php` — all tests must be GREEN before proceeding

**Checkpoint**: US1, US2, and US3 are all independently testable. Users can create, browse, and correct service records.

---

## Phase 6: User Story 4 — Delete a Service Record (Priority: P4)

**Goal**: An authenticated homeowner can permanently delete a service record they own after explicit confirmation, completing the full CRUD cycle.

**Independent Test**: Use the factory to create a record, invoke deletion, confirm, and assert the record no longer exists in the database or the list. Test in isolation using `ServiceRecordDeleteTest.php`.

### Tests for User Story 4 (TDD — write first, verify FAIL before T028)

- [ ] T027 [US4] Write feature tests in `tests/Feature/ServiceRecords/ServiceRecordDeleteTest.php` via `ddev exec --raw php artisan make:test --pest ServiceRecords/ServiceRecordDeleteTest --no-interaction`; cover: (1) authenticated user deletes own record → `assertDatabaseMissing('service_records', ...)` and record absent from component list, (2) authenticated user attempting to delete another user's record → 403 and record still present, (3) record deletion does not affect other records for the same asset; run tests to confirm RED

### Implementation for User Story 4

- [ ] T028 [P] [US4] Add `deleteRecord(int $id): void` to `app/Livewire/ServiceRecords/ServiceRecordList.php` — find `ServiceRecord::findOrFail($id)`, `abort_if($record->user_id !== Auth::id(), 403)`, call `$record->delete()`, `unset($this->records)`
- [ ] T029 [P] [US4] Add delete button to each record row in `resources/views/livewire/service-records/service-record-list.blade.php` — `<flux:button variant="danger" size="sm" wire:click="deleteRecord({{ $record->id }})" wire:confirm="{{ __('Delete this service record? This cannot be undone.') }}">{{ __('Delete') }}</flux:button>` inside the read-only row display (not shown during edit mode)
- [ ] T030 [US4] Run `ddev exec --raw php artisan test --compact tests/Feature/ServiceRecords/ServiceRecordDeleteTest.php` — all tests must be GREEN before proceeding

**Checkpoint**: All four user stories are independently testable and complete. Full CRUD is functional.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Edge cases, unit coverage, formatting, and final validation.

- [ ] T031 [P] Write unit test for `ServiceType::label()` in `tests/Unit/Enums/ServiceTypeTest.php` via `ddev exec --raw php artisan make:test --pest --unit Enums/ServiceTypeTest --no-interaction`; assert each case returns the expected human-readable label string
- [ ] T032 [P] Add future service date warning in `resources/views/livewire/service-records/service-record-list.blade.php` — in the read-only record row, display a `<flux:badge color="yellow">Future Date</flux:badge>` when `$record->service_date->isFuture()` (edge case from spec)
- [ ] T033 Run `ddev exec --raw vendor/bin/pint --dirty` to fix all formatting issues across new and modified files before final commit
- [ ] T034 Run full feature test suite `ddev exec --raw php artisan test --compact tests/Feature/ServiceRecords/` — all tests GREEN
- [ ] T035 Run unit test `ddev exec --raw php artisan test --compact tests/Unit/Enums/ServiceTypeTest.php` — GREEN
- [ ] T036 Verify navigation end-to-end: confirm `route('service-records.index', $asset)` resolves correctly via `ddev exec --raw php artisan route:list --name=service-records` and that the "View Service Records" link appears in `asset-detail.blade.php`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — T001 and T002 can start immediately and run in parallel
- **Foundational (Phase 2)**: Depends on Phase 1 — BLOCKS all user story phases
- **US1 (Phase 3)**: Depends on Phase 2 completion — write tests (T013) first
- **US2 (Phase 4)**: Depends on US1 completion (T015/T016 must be in place for computed property)
- **US3 (Phase 5)**: Depends on US2 completion (edit form builds on list display)
- **US4 (Phase 6)**: Depends on US3 completion (delete button placed in established row structure)
- **Polish (Phase 7)**: Depends on all story phases complete

### Within Each User Story Phase

1. Write tests → run to confirm RED
2. Generate/implement component and view
3. Run tests → confirm GREEN

### Parallel Opportunities

- **Phase 1**: T001 ‖ T002 (different files)
- **Phase 2**: T004 ‖ T005 ‖ T006 ‖ T007 ‖ T008 ‖ T009 ‖ T011 (all different files, after T003)
- **US2 implementation**: T019 ‖ T020 ‖ T021 (three different files: component, view, asset-detail)
- **US3 implementation**: T024 ‖ T025 (component vs view)
- **US4 implementation**: T028 ‖ T029 (component vs view)
- **Phase 7**: T031 ‖ T032 (different files)

---

## Parallel Examples

### Phase 2 (after T003 scaffold)

```
Parallel group A:
  Task: "Implement migration in database/migrations/*_create_service_records_table.php"  [T004]
  Task: "Implement ServiceRecord model in app/Models/ServiceRecord.php"                  [T005]
  Task: "Implement ServiceRecordFactory in database/factories/ServiceRecordFactory.php"  [T006]
  Task: "Implement ServiceRecordSeeder in database/seeders/ServiceRecordSeeder.php"      [T007]
  Task: "Add serviceRecords() HasMany to app/Models/Asset.php"                          [T008]
  Task: "Add serviceRecords() HasMany to app/Models/User.php"                           [T009]
  Task: "Create routes/service-records.php"                                              [T011]
```

### US2 Implementation (after T018 tests written)

```
Parallel group B:
  Task: "Add #[Computed] records() to ServiceRecordList.php"                             [T019]
  Task: "Implement list display + empty state in service-record-list.blade.php"          [T020]
  Task: "Add View Service Records button to asset-detail.blade.php"                      [T021]
```

---

## Implementation Strategy

### MVP First (US1 Only)

1. Complete Phase 1: Setup (T001–T002)
2. Complete Phase 2: Foundational (T003–T012)
3. Complete Phase 3: US1 — Log a Service Record (T013–T017)
4. **STOP and VALIDATE**: Run `php artisan test --compact tests/Feature/ServiceRecords/ServiceRecordCreationTest.php`
5. Users can create service records — MVP delivered

### Incremental Delivery

1. Phases 1–2 → Foundation ready
2. Phase 3 (US1) → Create records → Test → **Demo**: users can log service events
3. Phase 4 (US2) → View history → Test → **Demo**: users can browse service history
4. Phase 5 (US3) → Edit records → Test → **Demo**: users can correct mistakes
5. Phase 6 (US4) → Delete records → Test → **Demo**: full CRUD complete
6. Phase 7 → Polish → Format → Final test run

---

## Task Summary

| Phase | Story | Tasks | Parallelizable | Notes |
|-------|-------|-------|---------------|-------|
| 1: Setup | — | T001–T002 | 2 of 2 | Enum + validation concern |
| 2: Foundational | — | T003–T012 | 8 of 10 | Model, migration, factory, seeder, relationships, route |
| 3: US1 | Log Record (P1) | T013–T017 | 0 of 5 | TDD: tests → stub → component → view → green |
| 4: US2 | View History (P2) | T018–T022 | 3 of 5 | TDD: tests → 3-way parallel impl → green |
| 5: US3 | Edit Record (P3) | T023–T026 | 2 of 4 | TDD: tests → 2-way parallel impl → green |
| 6: US4 | Delete Record (P4) | T027–T030 | 2 of 4 | TDD: tests → 2-way parallel impl → green |
| 7: Polish | — | T031–T036 | 2 of 6 | Unit test, future-date badge, pint, final run |

**Total tasks**: 36
**Parallelizable tasks**: 19 of 36
**Suggested MVP scope**: Phases 1–3 (US1 only, T001–T017)
