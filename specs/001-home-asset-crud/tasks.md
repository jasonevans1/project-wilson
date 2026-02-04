# Tasks: Home Asset CRUD

**Input**: Design documents from `/specs/001-home-asset-crud/`
**Prerequisites**: plan.md ✓, spec.md ✓, research.md ✓, data-model.md ✓, contracts/ ✓, quickstart.md ✓

**Tests**: Included. The Wilson Constitution mandates TDD (Red → Green → Refactor). Test tasks appear first in every user-story phase and MUST fail before implementation begins.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3, US4)
- Exact file paths are included in every task description

---

## Phase 1: Setup (Artisan Scaffolding)

**Purpose**: Create all skeleton files via `php artisan make:` commands. No logic is written in this phase — only the empty stubs that Laravel and Livewire generate. This keeps every subsequent task focused purely on implementation content.

- [X] T001 [P] Run `php artisan make:enum AssetCategory --backed=string --no-interaction` to create `app/Enums/AssetCategory.php`
- [X] T002 [P] Run `php artisan make:enum AssetStatus --backed=string --no-interaction` to create `app/Enums/AssetStatus.php`
- [X] T003 [P] Run `php artisan make:model Asset -mfs --no-interaction` to create `app/Models/Asset.php`, `database/migrations/*_create_assets_table.php`, `database/factories/AssetFactory.php`, and `database/seeders/AssetSeeder.php`
- [X] T004 [P] Run `php artisan make:livewire assets.asset-list --no-interaction` to create `app/Livewire/Assets/AssetList.php` and `resources/views/livewire/assets/asset-list.blade.php`
- [X] T005 [P] Run `php artisan make:livewire assets.asset-form --no-interaction` to create `app/Livewire/Assets/AssetForm.php` and `resources/views/livewire/assets/asset-form.blade.php`
- [X] T006 [P] Run `php artisan make:livewire assets.asset-detail --no-interaction` to create `app/Livewire/Assets/AssetDetail.php` and `resources/views/livewire/assets/asset-detail.blade.php`
- [X] T007 [P] Run `php artisan make:test --pest AssetCrudTest --no-interaction` to create `tests/Feature/AssetCrudTest.php`
- [X] T008 [P] Run `php artisan make:test --pest --unit AssetModelTest --no-interaction` to create `tests/Unit/AssetModelTest.php`
- [X] T009 Create `app/Concerns/AssetValidationRules.php` as an empty trait (run `php artisan make:class AssetValidationRules --no-interaction`, then move the generated file into `app/Concerns/` and convert it to a trait)
- [X] T010 Create `routes/assets.php` as an empty file

**Checkpoint**: All skeleton files exist. No logic has been written. `git status` should show every file listed in `quickstart.md`.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Fill in the shared infrastructure that every user story depends on. No Livewire component logic or views are touched here — only the data layer, routing, validation rules, and navigation.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete.

- [X] T011 [P] Populate `app/Enums/AssetCategory.php` with eight TitleCase backed cases: `Appliance`, `Hvac`, `Plumbing`, `Electrical`, `Roofing`, `Flooring`, `Exterior`, `Other` (backed values are the lowercase equivalents). Add a `label(): string` method that returns a human-readable label for each case (e.g., `Hvac` → `"HVAC"`, `Appliance` → `"Appliance"`).
- [X] T012 [P] Populate `app/Enums/AssetStatus.php` with two TitleCase backed cases: `Active` (value `active`), `Archived` (value `archived`).
- [X] T013 Populate the migration file in `database/migrations/*_create_assets_table.php`. Columns per data-model.md: `id` (bigIncrements), `user_id` (foreignId → users, cascading delete), `name` (string 255), `category` (string 255), `location` (string 255), `purchase_date` (date, nullable), `install_date` (date, nullable), `warranty_expiration_date` (date, nullable), `notes` (text, nullable), `status` (string 255, default `active`), `timestamps`. Run `php artisan migrate --no-interaction` to apply.
- [X] T014 Populate `app/Models/Asset.php`: add `HasFactory` trait, set `$fillable` array (name, category, location, purchase_date, install_date, warranty_expiration_date, notes, status), implement `casts()` method casting `category` to `AssetCategory::class`, `status` to `AssetStatus::class`, and all three date fields to `date`. Add `user(): BelongsTo` relationship method.
- [X] T015 Add `assets(): HasMany<Asset>` relationship method to `app/Models/User.php`.
- [X] T016 [P] Populate `app/Concerns/AssetValidationRules.php` as a trait with a `protected function assetRules(): array` method returning the validation rules from data-model.md (name required string max:255; category required enum; location required string max:255; purchase_date nullable date; install_date nullable date; warranty_expiration_date nullable date; notes nullable string max:2000). Add PHPDoc `@return` with array shape type.
- [X] T017 [P] Populate `database/factories/AssetFactory.php`: implement `definition()` using Faker — name from a hard-coded array of realistic home asset names (e.g., "Refrigerator", "HVAC Unit", "Water Heater", "Roof Shingles", "Dishwasher", "Electrical Panel", "Garage Door Opener", "Bathroom Tiles"), random `AssetCategory` case, location from a hard-coded array ("Kitchen", "Bathroom", "Basement", "Garage", "Attic", "Living Room"), nullable random dates within the last 10 years, 50 % chance of lorem notes. Add an `archived()` state that sets status to `AssetStatus::Archived`.
- [X] T018 Populate `database/seeders/AssetSeeder.php`: create 4 active assets and 1 archived asset for `User::first()` (assumes the default user from the existing seeder). Call `AssetSeeder` from `DatabaseSeeder`. Run `php artisan db:seed --class=AssetSeeder --no-interaction`.
- [X] T019 Populate `routes/assets.php` with a single route group: `Route::middleware(['auth', 'verified'])->group(function () { Route::livewire('assets', AssetList::class)->name('assets.index'); });`. Add `require __DIR__.'/assets.php';` to `routes/web.php`.
- [X] T020 Add the "Assets" sidebar item to `resources/views/layouts/app/sidebar.blade.php` inside the existing "Platform" `<flux:sidebar.group>`: `<flux:sidebar.item icon="wrench" :href="route('assets.index')" :current="request()->routeIs('assets.*')" wire:navigate>Assets</flux:sidebar.item>`

**Checkpoint**: Foundation ready. Navigate to `/assets` — the page loads (empty Livewire component), the sidebar link appears, the database table exists with seed data, and the factory can generate assets in tests. User story implementation can now begin.

---

## Phase 3: User Story 1 — Add a New Home Asset (Priority: P1) 🎯 MVP

**Goal**: An authenticated user can create a new asset via a form and see it persist in the asset list.

**Independent Test**: Seed no assets. Log in. Click "Add Asset." Fill name, category, location. Save. Verify the asset appears in the list with correct data.

### Tests for User Story 1 ⚠️

> **NOTE: Write these tests FIRST, run them, confirm they FAIL, then proceed to implementation.**

- [X] T021 Write feature tests in `tests/Feature/AssetCrudTest.php` covering US1 acceptance scenarios: (a) authenticated + verified user POSTs valid asset data → asset is created in DB scoped to that user and the list page reflects it; (b) submitting without `name` → validation error, no asset created; (c) submitting without `category` → validation error, no asset created; (d) unauthenticated user cannot reach `/assets`.

### Implementation for User Story 1

- [X] T022 Implement `app/Livewire/Assets/AssetList.php`: declare typed public properties (`showArchived bool = false`, `selectedAssetId ?int = null`, `showCreateForm bool = false`), implement `openCreateForm()` action, implement `closePanel()` action, add `#[Computed]` `assets` property that queries `Auth::user()->assets()` filtered by status based on `showArchived`, paginated. Wire event listeners for `asset-created` (reset `showCreateForm`, refresh). The `render()` method returns the view.
- [X] T023 Implement `resources/views/livewire/assets/asset-list.blade.php`: wrap in `<x-layouts::app :title="'Assets'">`. Render an "Add Asset" `<flux:button>` that calls `openCreateForm()`. Conditionally render `<livewire:assets.asset-form />` (create mode, no asset prop) when `showCreateForm` is true, otherwise render the asset list rows (name, category label, location per row) as clickable items. Include an empty-state block (visible when `assets` collection is empty and `showCreateForm` is false) with a prompt to add the first asset.
- [X] T024 Implement `app/Livewire/Assets/AssetForm.php`: declare the `$asset` prop (nullable Asset, defaults null). In `mount()`, if `$asset` is set, populate all public properties from it. Implement `save()` action: validate via `$this->validate($this->assetRules())`, then create or update the Asset (create mode: `Auth::user()->assets()->create(...)`, update mode: `$this->asset->update(...)`), then dispatch the appropriate event (`asset-created` or `asset-updated`). Implement `cancel()` action dispatching `close-panel`.
- [X] T025 Implement `resources/views/livewire/assets/asset-form.blade.php`: a `<form wire:submit="save">` with `<flux:input>` for name and location, a `<flux:select>` for category (options from `AssetCategory::cases()`), `<flux:input type="date">` for the three optional date fields, a `<flux:textarea>` for notes, and `<flux:button type="submit">` Save + `<flux:button wire:click="cancel">` Cancel. Use Flux validation error display on each input.

**Checkpoint**: User Story 1 is fully functional. A user can open the form, fill it in, save, and see the new asset in the list. Tests pass.

---

## Phase 4: User Story 2 — View and Browse Home Assets (Priority: P2)

**Goal**: An authenticated user sees all their active assets in a list and can click into any one to see its full details.

**Independent Test**: Seed 3 active assets. Log in. Open `/assets`. Verify all 3 appear with name, category, location. Click one. Verify the detail view shows all fields. Seed 0 assets for a second user — verify that user sees the empty state, not the first user's assets.

### Tests for User Story 2 ⚠️

> **NOTE: Write these tests FIRST, run them, confirm they FAIL, then proceed to implementation.**

- [X] T026 [P] Write feature tests in `tests/Feature/AssetCrudTest.php` covering US2 acceptance scenarios: (a) user with assets sees all their active assets listed with name, category, location; (b) clicking an asset row loads the detail view with all fields (including optional date and notes fields when populated); (c) user with zero assets sees the empty state prompt; (d) user A cannot see user B's assets (data isolation).
- [X] T027 [P] Write unit tests in `tests/Unit/AssetModelTest.php` covering: (a) `AssetCategory` enum cases and the `label()` method returns correct human-readable labels; (b) `AssetStatus` enum cases exist; (c) Asset model casts `category` to `AssetCategory` and `status` to `AssetStatus`; (d) `AssetFactory` `archived()` state produces an asset with `AssetStatus::Archived`; (e) `User::assets()` relationship returns only that user's assets.

### Implementation for User Story 2

- [X] T028 Update `resources/views/livewire/assets/asset-list.blade.php`: wire each asset row so clicking it calls `selectAsset($asset->id)`. Conditionally render `<livewire:assets.asset-detail :asset="$selectedAsset" />` when `selectedAssetId` is set (pass the Asset model fetched from the `assets` collection or a fresh query scoped to the user). Add a `selectAsset(int $id)` action to `AssetList.php` that sets `selectedAssetId` and clears `showCreateForm`.
- [X] T029 Implement `app/Livewire/Assets/AssetDetail.php`: declare the `$asset` prop (required Asset). Declare `confirmingArchive bool = false` and `editMode bool = false`. Add `closePanel()` action that dispatches `close-panel`. The `render()` method returns the detail view.
- [X] T030 Implement `resources/views/livewire/assets/asset-detail.blade.php`: display all asset fields in a read-only layout (name as heading, category label, location, three date fields if non-null, notes if non-null). Include a back button that calls `closePanel()`. Placeholder buttons for Edit and Archive (wired up in US3 and US4 respectively — render them but leave actions as no-ops for now).

**Checkpoint**: User Stories 1 and 2 are both functional. The list shows assets, clicking drills into detail, empty state works, data isolation holds. Both unit and feature tests pass.

---

## Phase 5: User Story 3 — Update an Existing Home Asset (Priority: P3)

**Goal**: An authenticated user can open an existing asset, edit any field, save, and see the changes reflected.

**Independent Test**: Seed one asset with known values. Log in. Open it in detail. Click Edit. Change the name. Save. Verify the detail and list both show the updated name. Try clearing the name field and saving — verify validation error.

### Tests for User Story 3 ⚠️

> **NOTE: Write these tests FIRST, run them, confirm they FAIL, then proceed to implementation.**

- [X] T031 Write feature tests in `tests/Feature/AssetCrudTest.php` covering US3 acceptance scenarios: (a) authenticated user edits a valid field on their own asset → change persists and is visible in both detail and list; (b) clearing a required field (name or category) and submitting → validation error, original value preserved; (c) user A cannot update user B's asset (the form is never reachable for another user's asset, but verify the update action also enforces ownership via the scoped relationship).

### Implementation for User Story 3

- [X] T032 Wire the Edit button in `resources/views/livewire/assets/asset-detail.blade.php`: connect it to `startEdit()` action in `AssetDetail.php`. When `editMode` is true, replace the read-only detail with `<livewire:assets.asset-form :asset="$asset" />`. Add a `cancelEdit()` action that sets `editMode = false`. Listen for the `asset-updated` event: refresh `$asset` from the database and set `editMode = false`. Listen for `close-panel` from the form: call `cancelEdit()`.

**Checkpoint**: Full create-read-update loop works end-to-end. Tests pass.

---

## Phase 6: User Story 4 — Archive and Restore a Home Asset (Priority: P4)

**Goal**: An authenticated user can archive an active asset (with confirmation), see it disappear from the default list, toggle to view archived assets, and restore an archived asset back to active.

**Independent Test**: Seed 2 active assets. Log in. Open one. Click Archive. Confirm. Verify it leaves the list. Toggle "Show Archived." Verify it appears with an archived indicator. Click Restore on it. Verify it returns to the active list.

### Tests for User Story 4 ⚠️

> **NOTE: Write these tests FIRST, run them, confirm they FAIL, then proceed to implementation.**

- [X] T033 [P] Write feature tests in `tests/Feature/AssetCrudTest.php` covering US4 acceptance scenarios: (a) archiving an active asset sets its status to Archived and it no longer appears in the default (active) list; (b) cancelling the archive confirmation leaves the asset unchanged; (c) toggling the archived filter shows all archived assets with a visual indicator; (d) restoring an archived asset sets its status back to Active and it reappears in the default list; (e) user A cannot archive or restore user B's asset.

### Implementation for User Story 4

- [X] T034 Wire the Archive and Restore actions in `app/Livewire/Assets/AssetDetail.php`: implement `initiateArchive()` (sets `confirmingArchive = true`), `archive()` (sets `$this->asset->status` to `AssetStatus::Archived`, saves, dispatches `asset-archived`), `cancelArchive()` (sets `confirmingArchive = false`), and `restore()` (sets `$this->asset->status` to `AssetStatus::Active`, saves, dispatches `asset-restored`). Update `resources/views/livewire/assets/asset-detail.blade.php`: show the Archive button + a `<flux:modal>` confirmation dialog when `confirmingArchive` is true; show the Restore button only when the asset status is Archived; hide the Archive button when the asset is already Archived.
- [X] T035 Wire the archived toggle in `app/Livewire/Assets/AssetList.php` and its view: implement `toggleArchived()` action (flips `showArchived`, resets `selectedAssetId` and `showCreateForm`). The `assets` computed property already filters by status — verify the logic correctly returns active assets when `showArchived` is false and archived assets when true. Update `resources/views/livewire/assets/asset-list.blade.php`: add a `<flux:switch>` or toggle bound to `showArchived`. When showing archived assets, add a visual badge or label on each row indicating "Archived". Wire the `asset-archived` and `asset-restored` event listeners on `AssetList` to reset `selectedAssetId` and refresh the list.

**Checkpoint**: All four user stories are functional. Archive, restore, and the archived toggle all work. Tests pass.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Final checks, formatting, and verification across the entire feature.

- [X] T036 Run `vendor/bin/pint --dirty` to auto-format all PHP files touched in this feature. Verify zero formatting violations remain.
- [X] T037 Run the full Pest test suite for this feature: `php artisan test --compact --filter=Asset`. Verify all tests in `AssetCrudTest.php` and `AssetModelTest.php` pass.
- [X] T038 Run `php artisan db:seed --no-interaction` and manually smoke-test the full flow end-to-end: log in, navigate to Assets via sidebar, create an asset, view it, edit it, archive it, toggle to archived view, restore it. Verify all seeded assets are visible and accurate.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — all T001–T010 can start immediately. All are parallelizable.
- **Foundational (Phase 2)**: Depends on Phase 1 completion. T011 and T012 (enums) are parallelizable. T013 (migration) depends on T011 (enum values define the category column). T014 (model) depends on T011 + T012 (casts reference enums). T015 (User relationship) depends on T014. T016 (validation concern) depends on T011. T017 (factory) depends on T011 + T012. T018 (seeder) depends on T013 + T017. T019 (routes) depends on T004 (AssetList stub exists). T020 (sidebar nav) depends on T019.
- **User Stories (Phase 3+)**: All depend on Phase 2 completion. Within each story, tests come first and MUST fail before implementation runs.
- **Polish (Phase 7)**: Depends on all user story phases completing.

### User Story Dependencies

- **US1 (P1)**: Independent after Phase 2. Introduces `AssetForm` and the create path in `AssetList`.
- **US2 (P2)**: Independent after Phase 2. Introduces `AssetDetail` and the detail/browse path in `AssetList`. Does not require US1 to be complete (the list can render seeded assets without the create form), but in practice US1 is P1 and will ship first.
- **US3 (P3)**: Depends on US1 (AssetForm must exist) and US2 (AssetDetail must exist — edit is wired inside it). Wire-up only; no new components.
- **US4 (P4)**: Depends on US2 (AssetDetail must exist — archive/restore are wired inside it). Adds the toggle to AssetList, which is independent of US1/US3.

### Within Each User Story

1. Tests written and confirmed to FAIL
2. Component logic implemented
3. View markup implemented
4. Tests re-run and confirmed to PASS

### Parallel Opportunities

- **Phase 1**: All of T001–T010 are parallelizable (independent `make:` commands and file creation).
- **Phase 2**: T011 + T012 + T016 + T017 are parallelizable (different files, no mutual dependency). T019 + T020 are parallelizable once T004 is done.
- **Phase 4 (US2)**: T026 (feature tests) and T027 (unit tests) are parallelizable — different test files, no shared state.
- **Phase 6 (US4)**: T033 (tests) is parallelizable against Phase 5 if US3 implementation is already underway.

---

## Parallel Example: Phase 1

```
Launch together — all are independent file-creation commands:
  T001 make:enum AssetCategory
  T002 make:enum AssetStatus
  T003 make:model Asset -mfs
  T004 make:livewire assets.asset-list
  T005 make:livewire assets.asset-form
  T006 make:livewire assets.asset-detail
  T007 make:test AssetCrudTest
  T008 make:test AssetModelTest
  T009 make:class AssetValidationRules (then move to Concerns/)
  T010 create routes/assets.php
```

## Parallel Example: Phase 2 (first wave)

```
Launch together — different files, no mutual dependency:
  T011 Populate AssetCategory enum
  T012 Populate AssetStatus enum
  T016 Populate AssetValidationRules concern
  T017 Populate AssetFactory
```

## Parallel Example: Phase 4 (US2 tests)

```
Launch together — different test files:
  T026 Feature tests for US2 in tests/Feature/AssetCrudTest.php
  T027 Unit tests in tests/Unit/AssetModelTest.php
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational
3. Complete Phase 3: User Story 1 (Create)
4. **STOP and VALIDATE**: Run `php artisan test --compact --filter=Asset`. Verify create + validation tests pass. Smoke-test manually.
5. The app now has a working asset inventory entry point — this is the minimum viable product.

### Incremental Delivery

1. Setup + Foundational → data layer and nav are live
2. US1 (Create) → users can add assets (MVP)
3. US2 (View/Browse) → users can see and drill into assets
4. US3 (Update) → users can correct asset data
5. US4 (Archive/Restore) → users can manage asset lifecycle
6. Polish → format, test, smoke

### Parallel Team Strategy

With multiple developers (after Phase 2 completes):

- Developer A: US1 (Phase 3) → then US3 (Phase 5, since it depends on US1 + US2)
- Developer B: US2 (Phase 4) → then US4 (Phase 6, since it depends on US2)
- Merge after US1 + US2 are both green, then US3 + US4 can proceed.

---

## Notes

- [P] tasks = different files, no dependencies — safe to run concurrently
- [Story] label maps each task to its user story for traceability
- Constitution requires TDD: every user-story phase starts with test tasks that must FAIL before implementation
- `vendor/bin/pint --dirty` must run before any commit — formatting violations block delivery
- All queries go through `Auth::user()->assets()` — never `Asset::query()` directly. This is how FR-007 (data isolation) is enforced
- Enums use TitleCase cases; backed values are lowercase strings
- The `AssetForm` component is shared between US1 (create) and US3 (update) — it is implemented in US1 and extended in US3
