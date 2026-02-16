# Tasks: Asset Template System

**Input**: Design documents from `/specs/002-asset-template-system/`
**Prerequisites**: plan.md (required), spec.md (required), research.md, data-model.md, contracts/

**Tests**: Included — constitution mandates TDD (Red → Green → Refactor).

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Scaffold all new files using Artisan commands and register routes

- [ ] T001 Generate TemplateGroup model, migration, factory, and seeder via `php artisan make:model TemplateGroup -mfs --no-interaction`
- [ ] T002 Generate AssetTemplate model, migration, factory, and seeder via `php artisan make:model AssetTemplate -mfs --no-interaction`
- [ ] T003 Generate TemplateLibrary Livewire component via `php artisan make:livewire Assets/TemplateLibrary --no-interaction`
- [ ] T004 [P] Generate feature test file via `php artisan make:test --pest TemplateLibraryTest --no-interaction` in `tests/Feature/TemplateLibraryTest.php`
- [ ] T005 [P] Generate unit test file via `php artisan make:test --pest --unit TemplateModelTest --no-interaction` in `tests/Unit/TemplateModelTest.php`
- [ ] T006 Add template library route `assets/templates` to `routes/assets.php` pointing to `TemplateLibrary::class` with name `assets.templates`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Implement models, migrations, factories, and seeders that ALL user stories depend on

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

### Tests (write FIRST, verify they FAIL)

- [ ] T007 [P] Write unit tests for TemplateGroup model in `tests/Unit/TemplateModelTest.php`: test fillable fields, `templates()` HasMany relationship, `casts()` method, factory definition, slug uniqueness
- [ ] T008 [P] Write unit tests for AssetTemplate model in `tests/Unit/TemplateModelTest.php`: test fillable fields, `group()` BelongsTo relationship, category cast to AssetCategory enum, factory definition, display_order default

### Implementation

- [ ] T009 [P] Implement `template_groups` migration in `database/migrations/*_create_template_groups_table.php`: id, name (string 255), slug (string 255, unique), icon (string 100, nullable), display_order (integer, default 0), timestamps
- [ ] T010 [P] Implement `asset_templates` migration in `database/migrations/*_create_asset_templates_table.php`: id, template_group_id (foreignIdFor with cascadeOnDelete), name (string 255), description (text, nullable), category (string), location (string 255), display_order (integer, default 0), timestamps
- [ ] T011 [P] Implement TemplateGroup model in `app/Models/TemplateGroup.php`: fillable array, `templates()` HasMany relationship returning `HasMany<AssetTemplate>`, no casts needed (all string/int fields)
- [ ] T012 [P] Implement AssetTemplate model in `app/Models/AssetTemplate.php`: fillable array, `group()` BelongsTo relationship returning `BelongsTo<TemplateGroup>`, `casts()` method casting category to `AssetCategory::class`
- [ ] T013 [P] Implement TemplateGroupFactory in `database/factories/TemplateGroupFactory.php`: definition with realistic group name, slug from name, sequential display_order
- [ ] T014 [P] Implement AssetTemplateFactory in `database/factories/AssetTemplateFactory.php`: definition with realistic template name, random AssetCategory, random location from predefined list, optional description, sequential display_order. Requires `template_group_id`
- [ ] T015 Implement TemplateGroupSeeder in `database/seeders/TemplateGroupSeeder.php`: create 8 groups (Kitchen, Bathroom, Laundry Room, Living Areas, Bedroom, HVAC & Climate, Electrical & Safety, Exterior & Garage) with slugs, icons, and display_order per data-model.md
- [ ] T016 Implement AssetTemplateSeeder in `database/seeders/AssetTemplateSeeder.php`: create 30+ templates distributed across all 8 groups with correct categories and locations per data-model.md (depends on T015)
- [ ] T017 Register TemplateGroupSeeder and AssetTemplateSeeder in `database/seeders/DatabaseSeeder.php` (call after existing AssetSeeder)
- [ ] T018 Run migrations and seeders, verify unit tests pass via `php artisan test --compact tests/Unit/TemplateModelTest.php`

**Checkpoint**: Models, migrations, factories, and seeders complete. Database populated with 8 groups and 30+ templates. Unit tests green.

---

## Phase 3: User Story 1 — Browse and Select Templates (Priority: P1) 🎯 MVP

**Goal**: Users can navigate to the template library, see templates organized by group, select one or more, and see the "already owned" badge on templates matching existing assets.

**Independent Test**: Browse the template library, select a template, confirm selection count updates and "already owned" badge appears for matching assets.

### Tests (write FIRST, verify they FAIL)

- [ ] T019 [P] [US1] Write feature test: authenticated user can access template library page in `tests/Feature/TemplateLibraryTest.php`
- [ ] T020 [P] [US1] Write feature test: template library displays all template groups with their templates in `tests/Feature/TemplateLibraryTest.php`
- [ ] T021 [P] [US1] Write feature test: user can select a template and selectedTemplateIds updates in `tests/Feature/TemplateLibraryTest.php`
- [ ] T022 [P] [US1] Write feature test: user can select multiple templates and selectedCount reflects correct count in `tests/Feature/TemplateLibraryTest.php`
- [ ] T023 [P] [US1] Write feature test: user can deselect a template via toggleTemplate in `tests/Feature/TemplateLibraryTest.php`
- [ ] T024 [P] [US1] Write feature test: "already owned" badge shows when user has an asset with same name as a template in `tests/Feature/TemplateLibraryTest.php`
- [ ] T025 [P] [US1] Write feature test: unauthenticated user is redirected from template library page in `tests/Feature/TemplateLibraryTest.php`

### Implementation

- [ ] T026 [US1] Implement TemplateLibrary component step 1 (browse) in `app/Livewire/Assets/TemplateLibrary.php`: properties ($step, $selectedTemplateIds, $expandedGroups), computed properties (templateGroups with eager-loaded templates, ownedAssetNames, selectedCount), actions (toggleTemplate, addTemplate, removeSelectedTemplate)
- [ ] T027 [US1] Implement template library browse view in `resources/views/livewire/assets/template-library.blade.php`: header with "Back to Assets" link and selected count badge, template groups rendered as expandable sections using Alpine.js, each template as a `flux:card` with `flux:checkbox` for selection and `flux:badge` for "already owned" indicator, Flux UI components throughout
- [ ] T028 [US1] Run feature tests for US1 and verify all pass via `php artisan test --compact --filter="TemplateLibrary" tests/Feature/TemplateLibraryTest.php`

**Checkpoint**: Template library page loads, displays grouped templates, supports selection with count, shows "already owned" badges. MVP browsing functional.

---

## Phase 4: User Story 2 — Customize and Convert Templates to Assets (Priority: P2)

**Goal**: Users can proceed from selection to a review step, customize pre-filled fields on each selected template, remove unwanted items, confirm conversion, and see all items created as real assets.

**Independent Test**: Select a template, proceed to review, modify a field, confirm, and verify the asset appears in the asset list with the customized value.

### Tests (write FIRST, verify they FAIL)

- [ ] T029 [P] [US2] Write feature test: user can proceed to review step with selected templates and see editable fields in `tests/Feature/TemplateLibraryTest.php`
- [ ] T030 [P] [US2] Write feature test: user can edit a customized item field (e.g., name) during review in `tests/Feature/TemplateLibraryTest.php`
- [ ] T031 [P] [US2] Write feature test: user can remove an item from the review step in `tests/Feature/TemplateLibraryTest.php`
- [ ] T032 [P] [US2] Write feature test: confirmConversion creates assets in database with correct field values in `tests/Feature/TemplateLibraryTest.php`
- [ ] T033 [P] [US2] Write feature test: confirmConversion with customized values creates asset with those values (not template defaults) in `tests/Feature/TemplateLibraryTest.php`
- [ ] T034 [P] [US2] Write feature test: confirmConversion with multiple items creates all as separate active assets and sets createdCount in `tests/Feature/TemplateLibraryTest.php`
- [ ] T035 [P] [US2] Write feature test: confirmConversion validates all items and shows errors if a required field is cleared in `tests/Feature/TemplateLibraryTest.php`
- [ ] T036 [P] [US2] Write feature test: confirmConversion is atomic — no assets created if any item fails validation in `tests/Feature/TemplateLibraryTest.php`
- [ ] T037 [P] [US2] Write feature test: user can go back from review to browse without losing selectedTemplateIds in `tests/Feature/TemplateLibraryTest.php`

### Implementation

- [ ] T038 [US2] Implement TemplateLibrary component step 2 (review) in `app/Livewire/Assets/TemplateLibrary.php`: proceedToReview() hydrates customizedItems from selectedTemplateIds, removeCustomizedItem() removes by index, backToBrowse() returns to step 1. Use AssetValidationRules concern for validation
- [ ] T039 [US2] Implement TemplateLibrary component step 3 (confirm) in `app/Livewire/Assets/TemplateLibrary.php`: confirmConversion() validates all customizedItems, creates assets in DB transaction via Auth::user()->assets()->create(), sets createdCount, transitions to step 3. backToAssets() redirects to assets.index
- [ ] T040 [US2] Implement template library review view (step 2) in `resources/views/livewire/assets/template-library.blade.php`: list of customized items with editable `flux:input`, `flux:select`, `flux:textarea` fields per item, remove button per item, "Back" and "Create Assets" buttons, validation error display keyed per item index
- [ ] T041 [US2] Implement template library confirmation view (step 3) in `resources/views/livewire/assets/template-library.blade.php`: success message showing createdCount, "Back to Assets" button linking to assets.index
- [ ] T042 [US2] Run feature tests for US2 and verify all pass via `php artisan test --compact --filter="TemplateLibrary" tests/Feature/TemplateLibraryTest.php`

**Checkpoint**: Full template-to-asset conversion flow works: browse → select → review/customize → confirm → assets created. US1 and US2 both independently functional.

---

## Phase 5: User Story 3 — Browse Templates by Group (Priority: P3)

**Goal**: Users can expand/collapse template groups, see item counts per group, and navigate between groups while preserving their selections.

**Independent Test**: Expand a group, verify only its templates show. Select items from one group, collapse it, expand another group, verify previous selections preserved.

### Tests (write FIRST, verify they FAIL)

- [ ] T043 [P] [US3] Write feature test: template groups display item count in `tests/Feature/TemplateLibraryTest.php`
- [ ] T044 [P] [US3] Write feature test: user can expand and collapse individual groups via expandedGroups property in `tests/Feature/TemplateLibraryTest.php`
- [ ] T045 [P] [US3] Write feature test: selections are preserved when toggling group expansion in `tests/Feature/TemplateLibraryTest.php`
- [ ] T046 [P] [US3] Write feature test: selecting the same template multiple times creates independent entries in selectedTemplateIds (FR-014) in `tests/Feature/TemplateLibraryTest.php`

### Implementation

- [ ] T047 [US3] Add toggleGroup action to TemplateLibrary component in `app/Livewire/Assets/TemplateLibrary.php`: manages expandedGroups array to track which groups are expanded/collapsed
- [ ] T048 [US3] Update template library browse view in `resources/views/livewire/assets/template-library.blade.php`: add expand/collapse toggle per group header with Alpine.js, display template count badge per group, conditionally show/hide group contents based on expandedGroups state
- [ ] T049 [US3] Run feature tests for US3 and verify all pass via `php artisan test --compact --filter="TemplateLibrary" tests/Feature/TemplateLibraryTest.php`

**Checkpoint**: All three user stories independently functional. Group browsing with expand/collapse and preserved selections working.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: AssetList integration, empty state update, and final quality pass

- [ ] T050 [P] Write feature test: AssetList empty state shows "Add from Templates" as primary CTA and "Add Manually" as secondary in `tests/Feature/TemplateLibraryTest.php`
- [ ] T051 [P] Write feature test: AssetList header includes "Add from Templates" button that links to template library in `tests/Feature/TemplateLibraryTest.php`
- [ ] T052 Update AssetList component in `app/Livewire/Assets/AssetList.php`: no logic changes needed — view-only updates
- [ ] T053 Update asset list view in `resources/views/livewire/assets/asset-list.blade.php`: add "Add from Templates" `flux:button` in header area linking to `route('assets.templates')`, update empty state to promote templates as primary CTA with "Add Manually" as secondary (FR-016)
- [ ] T054 Run all feature tests to verify no regressions via `php artisan test --compact`
- [ ] T055 Run code formatting via `vendor/bin/pint --dirty`
- [ ] T056 Final verification: run full test suite via `php artisan test --compact` and confirm all green

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 (scaffolded files) — BLOCKS all user stories
- **User Story 1 (Phase 3)**: Depends on Phase 2 (models, migrations, seed data)
- **User Story 2 (Phase 4)**: Depends on Phase 3 (browse/select must exist before review/convert)
- **User Story 3 (Phase 5)**: Depends on Phase 3 (browse view must exist before adding group expand/collapse)
- **Polish (Phase 6)**: Depends on Phase 3 at minimum (template route must exist for AssetList links)

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Phase 2 — No dependencies on other stories
- **User Story 2 (P2)**: Depends on US1 (selection logic must exist before review/convert)
- **User Story 3 (P3)**: Depends on US1 (browse view must exist before adding group toggling). Can run in parallel with US2 since they modify different parts of the component
- **Polish**: Can start after US1 is complete (AssetList just needs to link to template route)

### Within Each User Story

- Tests MUST be written and FAIL before implementation (TDD per constitution)
- Models/computed properties before actions
- Component logic before view templates
- Story tests must pass before moving to next priority

### Parallel Opportunities

- T004 + T005: Test file generation (parallel)
- T007 + T008: Unit tests for both models (parallel)
- T009 + T010: Both migrations (parallel)
- T011 + T012 + T013 + T014: Models and factories (parallel — different files)
- T019–T025: All US1 feature tests (parallel — same file but independent test cases)
- T029–T037: All US2 feature tests (parallel)
- T043–T046: All US3 feature tests (parallel)
- T050 + T051: Polish tests (parallel)
- **US2 and US3 can be worked in parallel** once US1 is complete

---

## Parallel Example: Phase 2 (Foundational)

```bash
# Write both unit test blocks in parallel:
Task T007: "Unit tests for TemplateGroup model"
Task T008: "Unit tests for AssetTemplate model"

# Implement both migrations in parallel:
Task T009: "template_groups migration"
Task T010: "asset_templates migration"

# Implement all models and factories in parallel:
Task T011: "TemplateGroup model"
Task T012: "AssetTemplate model"
Task T013: "TemplateGroupFactory"
Task T014: "AssetTemplateFactory"
```

## Parallel Example: US2 + US3 After US1

```bash
# After US1 is complete, both can proceed in parallel:
# Developer A: US2 (review/customize/convert)
Task T029-T042: Review step, conversion logic, confirmation view

# Developer B: US3 (group browsing enhancements)
Task T043-T049: Expand/collapse groups, item counts, selection preservation
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (scaffolding)
2. Complete Phase 2: Foundational (models, migrations, seeders)
3. Complete Phase 3: User Story 1 (browse & select)
4. **STOP and VALIDATE**: User can navigate to template library, see grouped templates, select items, see "already owned" badges
5. Deploy/demo if ready — delivers core template browsing value

### Incremental Delivery

1. Setup + Foundational → Database populated, models tested
2. Add US1 (Browse & Select) → Test independently → Deploy (MVP!)
3. Add US2 (Customize & Convert) → Test independently → Deploy (full template-to-asset flow!)
4. Add US3 (Group Browsing) → Test independently → Deploy (polished browsing UX)
5. Add Polish (AssetList integration) → Test → Deploy (complete feature)
6. Each story adds value without breaking previous stories

---

## Notes

- [P] tasks = different files, no dependencies on incomplete tasks
- [Story] label maps task to specific user story for traceability
- TDD is mandatory per constitution: write tests first, verify red, then implement to green
- Use `php artisan test --compact` with specific file or filter for efficiency
- Run `vendor/bin/pint --dirty` before finalizing (T055)
- Commit after each phase checkpoint
- All Artisan commands include `--no-interaction`
- Reuse existing `AssetValidationRules` concern — do not duplicate validation logic
- All user-facing strings wrapped in `__()`
- All UI uses Flux UI components (`flux:card`, `flux:checkbox`, `flux:badge`, `flux:button`, `flux:input`, `flux:select`, `flux:textarea`)
