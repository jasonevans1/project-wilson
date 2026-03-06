# Tasks: Replacement Tracking

**Input**: Design documents from `/specs/007-replacement-tracking/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: Required — constitution mandates TDD (Red → Green → Refactor on every changeset).

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Artisan Scaffolding)

**Purpose**: Scaffold all new files via Artisan so subsequent phases only write content, not structure

- [x] T001 Scaffold AssetReplacementEvent model with migration and factory via `php artisan make:model AssetReplacementEvent -mf --no-interaction` in `app/Models/AssetReplacementEvent.php`
- [x] T002 Scaffold AssetReplacementAlert model with migration and factory via `php artisan make:model AssetReplacementAlert -mf --no-interaction` in `app/Models/AssetReplacementAlert.php`
- [x] T003 [P] Scaffold ReplacementDashboard Livewire component via `php artisan make:livewire --class --no-interaction ReplacementTracking/ReplacementDashboard` in `app/Livewire/ReplacementTracking/ReplacementDashboard.php`
- [x] T004 [P] Scaffold ReplacementSetupForm Livewire component via `php artisan make:livewire --class --no-interaction ReplacementTracking/ReplacementSetupForm` in `app/Livewire/ReplacementTracking/ReplacementSetupForm.php`
- [x] T005 [P] Scaffold RecordReplacementForm Livewire component via `php artisan make:livewire --class --no-interaction ReplacementTracking/RecordReplacementForm` in `app/Livewire/ReplacementTracking/RecordReplacementForm.php`
- [x] T006 [P] Scaffold SendReplacementAlerts command via `php artisan make:command SendReplacementAlerts --no-interaction` in `app/Console/Commands/SendReplacementAlerts.php`
- [x] T007 [P] Scaffold ReplacementAlertDismissController via `php artisan make:controller ReplacementAlertDismissController --no-interaction` in `app/Http/Controllers/ReplacementAlertDismissController.php`
- [x] T008 [P] Scaffold ReplacementAlertNotification via `php artisan make:notification ReplacementAlertNotification --no-interaction` in `app/Notifications/ReplacementAlertNotification.php`
- [x] T009 [P] Scaffold SaveReplacementSetupRequest via `php artisan make:request Replacement/SaveReplacementSetupRequest --no-interaction` in `app/Http/Requests/Replacement/SaveReplacementSetupRequest.php`
- [x] T010 [P] Scaffold RecordReplacementRequest via `php artisan make:request Replacement/RecordReplacementRequest --no-interaction` in `app/Http/Requests/Replacement/RecordReplacementRequest.php`

**Checkpoint**: All empty skeleton files in place — ready for content

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Complete migrations, models, enum, and service class — all user stories depend on these

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T011 Create ReplacementAlertType enum with cases TwoYear (`two_year`), OneYear (`one_year`), Overdue (`overdue`) and a `label(): string` method in `app/Enums/ReplacementAlertType.php`
- [x] T012 Create AssetLifespanDefaults service class with a single static method `forCategory(AssetCategory $category): ?int` returning (Appliance:10, Hvac:15, Plumbing:20, Electrical:25, Roofing:20, Flooring:25, Exterior:15, Other:null) per `research.md` R-003 in `app/Services/AssetLifespanDefaults.php`
- [x] T013 Write migration to add `expected_lifespan_years` (unsignedSmallInteger, nullable) and `replacement_alerts_enabled` (boolean, default true, not null) columns to the `assets` table in `database/migrations/2026_03_05_042601_add_replacement_columns_to_assets_table.php`
- [x] T014 Write migration to add `replacement_alerts_enabled` (boolean, default true, not null) column to the `users` table in `database/migrations/2026_03_05_042601_add_replacement_alerts_enabled_to_users_table.php`
- [x] T015 Write `asset_replacement_events` migration with columns: `asset_id` (FK → assets.id, cascade delete), `installed_at` (date, not null), `cost` (decimal 10,2, nullable), `expected_lifespan_years` (unsignedSmallInteger, nullable), `notes` (text, nullable), timestamps; and a composite index on `(asset_id, installed_at)` in `database/migrations/2026_03_05_035914_create_asset_replacement_events_table.php`
- [x] T016 Write `asset_replacement_alerts` migration with columns: `asset_id` (FK → assets.id, cascade delete), `alert_type` (string, not null), `sent_at` (timestamp, nullable), `dismissed_at` (timestamp, nullable), timestamps; unique constraint on `(asset_id, alert_type)`; and index on `sent_at` in `database/migrations/2026_03_05_035921_create_asset_replacement_alerts_table.php`
- [x] T017 Run `php artisan migrate` to apply all four new migrations and verify schema
- [x] T018 [P] Complete AssetReplacementEvent model: fillable (asset_id, installed_at, cost, expected_lifespan_years, notes), casts (installed_at → date, cost → decimal:2, expected_lifespan_years → integer), `asset()` BelongsTo relationship with return type, PHPDoc block in `app/Models/AssetReplacementEvent.php`
- [x] T019 [P] Complete AssetReplacementAlert model: fillable (asset_id, alert_type, sent_at, dismissed_at), casts (alert_type → ReplacementAlertType enum, sent_at → datetime, dismissed_at → datetime), `asset()` BelongsTo relationship with return type, PHPDoc block in `app/Models/AssetReplacementAlert.php`
- [x] T020 [P] Implement AssetReplacementEventFactory default state (asset_id via AssetFactory, installed_at: fake()->dateTimeBetween('-5 years', 'now'), cost: null, notes: null, expected_lifespan_years: null) and states `withCost` (random decimal 500–15000) and `withNotes` (fake()->sentence()) in `database/factories/AssetReplacementEventFactory.php`
- [x] T021 [P] Implement AssetReplacementAlertFactory default state (asset_id via AssetFactory, alert_type: TwoYear, sent_at: null, dismissed_at: null) and states `sent` (sent_at: now()), `dismissed` (sent_at: now(), dismissed_at: now()), `overdue` (alert_type: Overdue) in `database/factories/AssetReplacementAlertFactory.php`
- [x] T022 Update Asset model: add `replacementEvents()` HasMany and `replacementAlerts()` HasMany typed relationships; add `expected_lifespan_years` and `replacement_alerts_enabled` to `$fillable`; add casts for `expected_lifespan_years` (integer) and `replacement_alerts_enabled` (boolean) in `app/Models/Asset.php`
- [x] T023 Update User model: add `replacement_alerts_enabled` to `$fillable` and cast it as boolean; add `replacementAlerts()` hasManyThrough(AssetReplacementAlert, Asset) relationship in `app/Models/User.php`

**Checkpoint**: Migrations applied, models complete, factory states ready — user story phases can begin

---

## Phase 3: User Story 1 — Configure Replacement Lifespan for an Asset (Priority: P1) 🎯 MVP

**Goal**: Users can open an asset, see a default lifespan pre-filled based on asset category, set an installation date, save, and immediately see the calculated replacement year and remaining useful life

**Independent Test**: Open any asset's detail page, navigate to the Replacement section, verify the default lifespan is pre-filled from AssetLifespanDefaults, save a lifespan + install date, and verify the replacement date and remaining-life percentage display correctly

### Tests for User Story 1

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [x] T024 [P] [US1] Create unit test file via `php artisan make:test AssetLifespanDefaultsTest --pest --unit --no-interaction` in `tests/Unit/AssetLifespanDefaultsTest.php`
- [x] T025 [P] [US1] Create feature test file via `php artisan make:test ReplacementSetupFormTest --pest --no-interaction` in `tests/Feature/ReplacementSetupFormTest.php`
- [x] T026 [US1] Write unit test: it returns 10 for Appliance, 15 for Hvac, 20 for Plumbing, 25 for Electrical, 20 for Roofing, 25 for Flooring, 15 for Exterior in `tests/Unit/AssetLifespanDefaultsTest.php`
- [x] T027 [US1] Write unit test: it returns null for AssetCategory::Other in `tests/Unit/AssetLifespanDefaultsTest.php`
- [x] T028 [US1] Write test: component mount pre-fills expectedLifespanYears from asset->expected_lifespan_years when it is already set in `tests/Feature/ReplacementSetupFormTest.php`
- [x] T029 [US1] Write test: component mount pre-fills expectedLifespanYears from AssetLifespanDefaults::forCategory when asset has no lifespan set in `tests/Feature/ReplacementSetupFormTest.php`
- [x] T030 [US1] Write test: component mount pre-fills installDate from asset->install_date when it is already set in `tests/Feature/ReplacementSetupFormTest.php`
- [x] T031 [US1] Write test: save() persists expected_lifespan_years and install_date on the asset and dispatches the tracking-configured event in `tests/Feature/ReplacementSetupFormTest.php`
- [x] T032 [US1] Write test: save() fails validation when expectedLifespanYears is 0 in `tests/Feature/ReplacementSetupFormTest.php`
- [x] T033 [US1] Write test: save() fails validation when expectedLifespanYears exceeds 100 in `tests/Feature/ReplacementSetupFormTest.php`
- [x] T034 [US1] Write test: save() fails validation when installDate is a future date in `tests/Feature/ReplacementSetupFormTest.php`
- [x] T035 [US1] Write test: save() is rejected with 403 when the asset does not belong to the authenticated user in `tests/Feature/ReplacementSetupFormTest.php`

### Implementation for User Story 1

- [x] T036 [US1] Implement SaveReplacementSetupRequest: authorize() verifies asset belongs to Auth::user(); rules() returns array-syntax rules for `expectedLifespanYears` (required|integer|min:1|max:100) and `installDate` (required|date|before_or_equal:today) in `app/Http/Requests/Replacement/SaveReplacementSetupRequest.php`
- [x] T037 [US1] Implement ReplacementSetupForm Livewire component: mount(Asset $asset) pre-fills properties per contract; save() validates via SaveReplacementSetupRequest rules, updates asset->expected_lifespan_years and asset->install_date, dispatches tracking-configured; cancel() dispatches close-setup-form in `app/Livewire/ReplacementTracking/ReplacementSetupForm.php`
- [x] T038 [US1] Implement replacement-setup-form Blade view with Flux UI: `flux:input` for lifespan years (type=number), `flux:input` for install date (type=date), `flux:button` for save and cancel, display validation errors inline in `resources/views/livewire/replacement-tracking/replacement-setup-form.blade.php`
- [x] T039 [US1] Add replacement section to AssetDetail Livewire component: render `<livewire:replacement-tracking.replacement-setup-form :asset="$asset" />` within a new "Replacement" tab or collapsible section; listen for close-setup-form to collapse the section; refresh asset on tracking-configured in `app/Livewire/Assets/AssetDetail.php`
- [x] T040 [US1] Add Replacement section to AssetDetail Blade view: show computed replacement date (install_date + expected_lifespan_years), years remaining, useful life percentage, and progress bar when both fields are set; show "Not yet configured" state when missing; embed ReplacementSetupForm component in `resources/views/livewire/assets/asset-detail.blade.php`
- [x] T041 [US1] Run US1 tests and verify all pass via `php artisan test --compact tests/Unit/AssetLifespanDefaultsTest.php tests/Feature/ReplacementSetupFormTest.php`

**Checkpoint**: Users can configure replacement tracking per asset and see the computed timeline — deliverable as an independently testable MVP

---

## Phase 4: User Story 2 — View Replacement Dashboard / Overview (Priority: P2)

**Goal**: A dedicated "Replacement Tracking" page in the main nav shows all assets in a unified list: tracked assets sorted by urgency (overdue first, then soonest to latest), untracked assets below with a "Set up tracking" inline CTA

**Independent Test**: Navigate to `/replacement-tracking`, verify tracked assets appear with urgency sort (overdue highlighted at top, <1-year assets visually distinguished), verify untracked assets appear below with "Set up tracking" button

### Tests for User Story 2

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [x] T042 [P] [US2] Create feature test file via `php artisan make:test ReplacementDashboardTest --pest --no-interaction` in `tests/Feature/ReplacementDashboardTest.php`
- [x] T043 [US2] Write test: the `/replacement-tracking` route renders and returns 200 for an authenticated user in `tests/Feature/ReplacementDashboardTest.php`
- [x] T044 [US2] Write test: unauthenticated access to `/replacement-tracking` redirects to login in `tests/Feature/ReplacementDashboardTest.php`
- [x] T045 [US2] Write test: tracked assets appear before untracked assets in the list in `tests/Feature/ReplacementDashboardTest.php`
- [x] T046 [US2] Write test: tracked assets are ordered by days remaining ascending — the asset with the fewest days remaining appears first in `tests/Feature/ReplacementDashboardTest.php`
- [x] T047 [US2] Write test: an overdue asset (replacement date in the past) appears at the top of the tracked list in `tests/Feature/ReplacementDashboardTest.php`
- [x] T048 [US2] Write test: untracked assets display a "Set up tracking" call-to-action button in `tests/Feature/ReplacementDashboardTest.php`
- [x] T049 [US2] Write test: assets belonging to other users do not appear on the authenticated user's dashboard in `tests/Feature/ReplacementDashboardTest.php`

### Implementation for User Story 2

- [x] T050 [US2] Create `routes/replacement-tracking.php` with an authenticated middleware group containing `GET /replacement-tracking` → `ReplacementDashboard` component named `replacement-tracking`; include the file from `routes/web.php` in `routes/replacement-tracking.php`
- [x] T051 [US2] Implement ReplacementDashboard Livewire component: `render()` loads Auth::user()->assets()->with(['replacementEvents' => fn($q) => $q->latest('installed_at')->limit(1)]) and splits into tracked (both install_date and expected_lifespan_years set, sorted by days-remaining ASC) and untracked (sorted by name ASC); exposes `$setupAssetId` and `$recordingAssetId` properties; implements openSetupForm, closeSetupForm, openRecordForm, closeRecordForm, handleTrackingConfigured, handleReplacementRecorded actions per contract in `app/Livewire/ReplacementTracking/ReplacementDashboard.php`
- [x] T052 [US2] Implement replacement-dashboard Blade view using Flux UI: page heading "Replacement Tracking", tracked asset rows with asset name, expected replacement year, years remaining label (e.g., "7.2 years remaining" or "Past due by 1.3 years"), `flux:badge` for overdue state, progress bar for useful life %, inline ReplacementSetupForm when $setupAssetId matches, inline RecordReplacementForm when $recordingAssetId matches, untracked asset rows with "Set up tracking" button in `resources/views/livewire/replacement-tracking/replacement-dashboard.blade.php`
- [x] T053 [US2] Add "Replacement Tracking" navigation link to the main app navigation Blade partial pointing to `route('replacement-tracking')`
- [x] T054 [US2] Run US2 tests and verify all pass via `php artisan test --compact tests/Feature/ReplacementDashboardTest.php`

**Checkpoint**: Dedicated replacement tracking dashboard accessible from main nav, showing all assets with urgency sort

---

## Phase 5: User Story 3 — Record an Asset Replacement (Priority: P3)

**Goal**: Users can record a replacement event from the dashboard or asset detail page — capturing new installation date, optional cost, optional notes, and an optionally updated lifespan — which resets the timeline and builds a queryable replacement history

**Independent Test**: Record a replacement on an asset that had prior alert rows; verify the new AssetReplacementEvent exists, asset's install_date updated, all AssetReplacementAlert rows deleted, and the replacement history list displays the new entry with date and cost

### Tests for User Story 3

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [x] T055 [P] [US3] Create feature test file via `php artisan make:test RecordReplacementFormTest --pest --no-interaction` in `tests/Feature/RecordReplacementFormTest.php`
- [x] T056 [US3] Write test: save() creates a new AssetReplacementEvent record with the correct asset_id, installed_at, cost, and notes in `tests/Feature/RecordReplacementFormTest.php`
- [x] T057 [US3] Write test: save() updates the asset's install_date to the submitted installedAt value in `tests/Feature/RecordReplacementFormTest.php`
- [x] T058 [US3] Write test: save() updates the asset's expected_lifespan_years when the form's lifespan field is changed from the pre-filled value in `tests/Feature/RecordReplacementFormTest.php`
- [x] T059 [US3] Write test: save() deletes all existing AssetReplacementAlert rows for the asset (alert cycle reset) in `tests/Feature/RecordReplacementFormTest.php`
- [x] T060 [US3] Write test: save() dispatches the replacement-recorded event in `tests/Feature/RecordReplacementFormTest.php`
- [x] T061 [US3] Write test: save() succeeds when cost and notes are omitted; cost appears as "Not recorded" in the history view in `tests/Feature/RecordReplacementFormTest.php`
- [x] T062 [US3] Write test: save() fails validation when installedAt is a future date in `tests/Feature/RecordReplacementFormTest.php`
- [x] T063 [US3] Write test: save() is rejected with 403 when the asset does not belong to the authenticated user in `tests/Feature/RecordReplacementFormTest.php`
- [x] T064 [US3] Write test: the replacement history section displays all past AssetReplacementEvent records ordered by installed_at descending in `tests/Feature/RecordReplacementFormTest.php`

### Implementation for User Story 3

- [x] T065 [US3] Implement RecordReplacementRequest: authorize() verifies asset belongs to Auth::user(); rules() returns array-syntax rules for installedAt (required|date|before_or_equal:today), cost (nullable|numeric|min:0|max:9999999.99), notes (nullable|string|max:1000), expectedLifespanYears (nullable|integer|min:1|max:100) in `app/Http/Requests/Replacement/RecordReplacementRequest.php`
- [x] T066 [US3] Implement RecordReplacementForm Livewire component: mount(Asset $asset) pre-fills $expectedLifespanYears from asset->expected_lifespan_years; save() validates via RecordReplacementRequest rules, creates AssetReplacementEvent, updates asset install_date and expected_lifespan_years (if field provided), deletes all AssetReplacementAlert rows for the asset, dispatches replacement-recorded; cancel() dispatches close-record-form in `app/Livewire/ReplacementTracking/RecordReplacementForm.php`
- [x] T067 [US3] Implement record-replacement-form Blade view using Flux UI: required date field (installedAt), optional numeric field (cost), optional textarea (notes), lifespan field pre-filled with current value (optional override), save and cancel buttons, validation error display in `resources/views/livewire/replacement-tracking/record-replacement-form.blade.php`
- [x] T068 [US3] Add replacement history display to the asset's replacement section: a list of past AssetReplacementEvent records showing installed_at date, cost (or "Not recorded"), ordered newest first; load via `$asset->replacementEvents()->latest('installed_at')->get()` in `resources/views/livewire/assets/asset-detail.blade.php`
- [x] T069 [US3] Run US3 tests and verify all pass via `php artisan test --compact tests/Feature/RecordReplacementFormTest.php`

**Checkpoint**: Replacement history logging complete — timeline resets on record, alert cycle resets, history displays on asset detail

---

## Phase 6: User Story 4 — Replacement Alerts / Notifications (Priority: P4)

**Goal**: A daily scheduled command sends approaching-replacement and overdue alert emails. Alerts deduplicate per cycle, respect per-user and per-asset opt-out settings, and the overdue alert includes a signed URL allowing the user to dismiss it without logging in

**Independent Test**: Create an asset with a replacement date 1.5 years away, run `replacement:send-alerts`, verify a TwoYear ReplacementAlertNotification was dispatched and an AssetReplacementAlert row was created with sent_at set; run again and verify no duplicate is sent

### Tests for User Story 4

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [x] T070 [P] [US4] Create unit test file via `php artisan make:test ReplacementAlertTypeTest --pest --unit --no-interaction` in `tests/Unit/ReplacementAlertTypeTest.php`
- [x] T071 [P] [US4] Create feature test file via `php artisan make:test SendReplacementAlertsTest --pest --no-interaction` in `tests/Feature/SendReplacementAlertsTest.php`
- [x] T072 [P] [US4] Create feature test file via `php artisan make:test ReplacementAlertDismissTest --pest --no-interaction` in `tests/Feature/ReplacementAlertDismissTest.php`
- [x] T073 [US4] Write unit test: ReplacementAlertType cases have correct string values (two_year, one_year, overdue) and label() returns human-readable strings in `tests/Unit/ReplacementAlertTypeTest.php`
- [x] T074 [US4] Write test: command sends a TwoYear alert for an asset with exactly 730 days remaining (2 years out) in `tests/Feature/SendReplacementAlertsTest.php`
- [x] T075 [US4] Write test: command sends an OneYear alert for an asset with exactly 365 days remaining (1 year out) in `tests/Feature/SendReplacementAlertsTest.php`
- [x] T076 [US4] Write test: command sends an Overdue alert for an asset whose expected replacement date is yesterday in `tests/Feature/SendReplacementAlertsTest.php`
- [x] T077 [US4] Write test: command does not send a duplicate TwoYear alert when an AssetReplacementAlert row with sent_at not null and dismissed_at null already exists for that asset in `tests/Feature/SendReplacementAlertsTest.php`
- [x] T078 [US4] Write test: command does not send alerts for assets where asset->replacement_alerts_enabled is false in `tests/Feature/SendReplacementAlertsTest.php`
- [x] T079 [US4] Write test: command does not send alerts for users where user->replacement_alerts_enabled is false in `tests/Feature/SendReplacementAlertsTest.php`
- [x] T080 [US4] Write test: command does not send alerts for users without a verified email address (email_verified_at is null) in `tests/Feature/SendReplacementAlertsTest.php`
- [x] T081 [US4] Write test: command does not send alerts for assets missing either install_date or expected_lifespan_years in `tests/Feature/SendReplacementAlertsTest.php`
- [x] T082 [US4] Write test: command does not send an Overdue alert when dismissed_at is already set on the existing AssetReplacementAlert row in `tests/Feature/SendReplacementAlertsTest.php`
- [x] T083 [US4] Write test: visiting a valid signed dismiss URL sets dismissed_at on the alert and redirects to /replacement-tracking with a success message in `tests/Feature/ReplacementAlertDismissTest.php`
- [x] T084 [US4] Write test: visiting a dismiss URL with an invalid or expired signature returns HTTP 403 in `tests/Feature/ReplacementAlertDismissTest.php`
- [x] T085 [US4] Write test: visiting a dismiss URL for an already-dismissed alert is idempotent — succeeds without error in `tests/Feature/ReplacementAlertDismissTest.php`

### Implementation for User Story 4

- [x] T086 [US4] Implement ReplacementAlertNotification: implements ShouldQueue; constructor accepts Asset $asset and ReplacementAlertType $alertType with typed parameters; via() returns ['mail']; toMail() builds MailMessage with urgency-differentiated subject (TwoYear: "{name} — Replacement Due in ~2 Years", OneYear: "{name} — Replacement Due Soon", Overdue: "{name} — Past Expected Replacement Date"), email content, and signed dismiss URL for Overdue type via URL::signedRoute('replacement.alert.dismiss', ['alert' => $alert->id], now()->addDays(30)) in `app/Notifications/ReplacementAlertNotification.php`
- [x] T087 [US4] Create replacement alert Blade mail template: asset name heading, expected replacement date, years remaining display (positive or "X years overdue"), view-replacement-tracking action button, conditional dismiss link for Overdue alerts only in `resources/views/mail/replacement-alert.blade.php`
- [x] T088 [US4] Implement SendReplacementAlerts command handle(): signature `replacement:send-alerts`; query all Asset records with install_date and expected_lifespan_years set, belonging to users where email_verified_at not null and replacement_alerts_enabled true, where asset replacement_alerts_enabled is true; eager load `user`; compute days_remaining per asset; check each ReplacementAlertType threshold (TwoYear: days_remaining <= 730, OneYear: days_remaining <= 365, Overdue: days_remaining <= 0); skip if AssetReplacementAlert exists with sent_at not null and dismissed_at null; dispatch ReplacementAlertNotification and upsert AssetReplacementAlert with sent_at = now() in `app/Console/Commands/SendReplacementAlerts.php`
- [x] T089 [US4] Register `replacement:send-alerts` as a daily scheduled command in `routes/console.php`
- [x] T090 [US4] Add signed dismiss route `GET /replacement-tracking/alerts/{alert}/dismiss` named `replacement.alert.dismiss` with `signed` middleware to `routes/replacement-tracking.php`
- [x] T091 [US4] Implement ReplacementAlertDismissController as a single-action invokable controller: validate signed URL (handled by signed middleware), find AssetReplacementAlert by route model binding, set dismissed_at = now() if not already set, redirect to route('replacement-tracking') with a session flash success message; return type RedirectResponse in `app/Http/Controllers/ReplacementAlertDismissController.php`
- [x] T092 [US4] Extend Settings\Notifications Livewire component: add `public bool $replacementAlertsEnabled` property; in mount() set it from Auth::user()->replacement_alerts_enabled; in updateNotificationPreferences() also save replacement_alerts_enabled to the user in `app/Livewire/Settings/Notifications.php`
- [x] T093 [US4] Add a replacement alerts enabled toggle to the notification settings Blade view using Flux UI (`flux:switch` or `flux:checkbox`) labeled "Replacement reminders" below the existing maintenance toggle in `resources/views/livewire/settings/notifications.blade.php`
- [x] T094 [US4] Run US4 tests and verify all pass via `php artisan test --compact tests/Unit/ReplacementAlertTypeTest.php tests/Feature/SendReplacementAlertsTest.php tests/Feature/ReplacementAlertDismissTest.php`

**Checkpoint**: Replacement alert emails sending, deduplicating, respecting opt-out settings, and dismissible via signed URL

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Code quality, formatting, regression validation, and quickstart verification

- [ ] T095 [P] Run `vendor/bin/pint --dirty` to fix formatting across all new and modified PHP files
- [ ] T096 [P] Run full test suite via `php artisan test --compact` to verify no regressions across all prior feature tests
- [ ] T097 Run quickstart.md validation — verify every file listed in quickstart.md exists on disk, every named route resolves, and every factory state defined in quickstart.md is implemented in the factory files

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately; T003–T010 can all run in parallel after T001–T002
- **Foundational (Phase 2)**: Depends on Phase 1 — BLOCKS all user story phases
  - T011–T016 can run in parallel (different files)
  - T018–T021 can run in parallel after T017 (migrate)
  - T022–T023 must run after T018–T019 (reference the new models)
- **User Story Phases (3–6)**: All depend on Phase 2 completion
  - US1 (Phase 3), US2 (Phase 4), US3 (Phase 5) are independent of each other
  - US4 (Phase 6) is independent but its alert cycle reset logic (delete AssetReplacementAlert) is tested together with the US3 RecordReplacementForm
- **Polish (Phase 7)**: Depends on all user stories being complete

### User Story Dependencies

- **US1 (P1)**: Can start immediately after Phase 2 — no dependency on other stories
- **US2 (P2)**: Can start after Phase 2 — composes ReplacementSetupForm from US1 (if US1 done first) but independently testable without it
- **US3 (P3)**: Can start after Phase 2 — RecordReplacementForm deletes AssetReplacementAlert rows (model from Phase 2); independent of US1/US2
- **US4 (P4)**: Can start after Phase 2 — SendReplacementAlerts uses Asset model and ReplacementAlertType from Phase 2; independent of US1–US3

### Within Each User Story

- Tests MUST be written and confirmed failing before implementation begins (TDD)
- Test file scaffolding (marked [P]) can run in parallel
- Form Requests before Livewire components (components call request rules)
- Components before Blade views (views reference component properties)
- Story must be green before advancing to the next phase

### Parallel Opportunities

- Phase 1: T003–T010 can all run in parallel after T001–T002
- Phase 2: T011–T016 in parallel; T018–T021 in parallel (after T017)
- US1: T024 + T025 test scaffolding in parallel; T026 + T027 unit tests in parallel
- US2: T042 test scaffold is independent of US1 work
- US3: T055 test scaffold is independent; T065 (request) + T067 (view) in parallel after T066
- US4: T070 + T071 + T072 test scaffolds all in parallel; T086 + T087 in parallel; T089 + T090 in parallel after T088

---

## Parallel Example: User Story 1

```bash
# Scaffold test files in parallel:
Task T024: Create AssetLifespanDefaultsTest unit test file
Task T025: Create ReplacementSetupFormTest feature test file

# Write unit tests (same file, sequential):
Task T026: Default values per category
Task T027: Null for Other

# Write feature tests (same file, sequential):
Task T028–T035: Component mount, save, validation, auth tests

# Implement (sequential — request before component before views):
Task T036: SaveReplacementSetupRequest
Task T037: ReplacementSetupForm component
Task T038: Blade view for setup form
Task T039: AssetDetail component (add replacement section)
Task T040: AssetDetail Blade view (replacement tab + display)
Task T041: Run all US1 tests
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Artisan scaffolding
2. Complete Phase 2: Foundational (migrations, models, enum, service)
3. Complete Phase 3: User Story 1 (lifespan setup on asset detail)
4. **STOP and VALIDATE**: Run `php artisan test --compact tests/Unit/AssetLifespanDefaultsTest.php tests/Feature/ReplacementSetupFormTest.php`
5. Demo: Open any asset, configure lifespan + install date, verify replacement date and progress bar display

### Incremental Delivery

1. Phase 1 + Phase 2 → Infrastructure ready
2. Phase 3 (US1) → Per-asset lifespan config → Demo
3. Phase 4 (US2) → Replacement dashboard → Demo
4. Phase 5 (US3) → Replacement history recording → Demo
5. Phase 6 (US4) → Alert emails + dismissal → Demo
6. Phase 7 → Full suite green, Pint clean → Ready for merge

### Recommended Sequential Order

US1 → US2 → US3 → US4 is recommended since:
- US2 dashboard composes the ReplacementSetupForm component from US1
- US3 RecordReplacementForm deletes alert rows created in US4 — testing this in US3 requires US4's model to exist (it does — from Phase 2)
- US4 alert command benefits from having real tracked assets set up via US1/US3

---

## Notes

- [P] tasks = different files, no dependencies on incomplete tasks in the same phase
- TDD is non-negotiable per constitution — write each test, run it, confirm red, then implement
- Use `Notification::fake()` in feature tests to assert notification dispatch without sending real mail
- Scope all asset queries to `Auth::user()->assets()` — never query `Asset::query()` directly in Livewire components
- `make:livewire` requires the `--class` flag to generate class-based components (PHP class + Blade view); without it, Livewire 4 creates view-based single-file components in `views/components/`
- Computed values (days remaining, useful life %, replacement date) are PHP calculations — not stored columns
- The `AssetReplacementAlert` row must be deleted (not soft-deleted or nullified) when a replacement event is recorded — this starts a fresh alert cycle
- Commit after each task or logical group
- Stop at each **Checkpoint** to validate the story independently before continuing
