# Tasks: Wilson Brand Logo for Authentication Pages

**Input**: Design documents from `/specs/009-wilson-logo/`
**Prerequisites**: plan.md ✓, spec.md ✓, research.md ✓, quickstart.md ✓

**Organization**: Tasks are grouped by user story. US1 (login page) delivers the MVP — implementing the shared `app-logo-icon.blade.php` SVG automatically satisfies US2 and US3 at the rendering level. US2 and US3 add test coverage and the sidebar brand name fix.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3)

---

## Phase 1: Setup

**Purpose**: Create the Pest test file that will be populated in subsequent phases.

- [x] T001 Create Pest feature test file via `ddev exec --raw php artisan make:test --pest --no-interaction WilsonLogoTest` producing `tests/Feature/WilsonLogoTest.php`

---

## Phase 2: User Story 1 — Wilson Logo on Login Page (Priority: P1) 🎯 MVP

**Goal**: The login page renders the Wilson "W" SVG monogram in place of the Laravel cube icon.

**Independent Test**: Navigate to `/login` — the Wilson W path string is present in the page source; the Laravel cube path string is absent.

- [x] T002 [US1] Write failing test case asserting the Wilson W SVG `d` attribute substring (`M4,4 L13,36`) is present in the login page response in `tests/Feature/WilsonLogoTest.php` (confirm Red before proceeding)
- [x] T003 [US1] Replace entire SVG content in `resources/views/components/app-logo-icon.blade.php` with the Wilson W monogram (viewBox `0 0 40 40`, single closed path `M4,4 L13,36 L20,18 L27,36 L36,4 L30,4 L24,30 L20,23 L16,30 L10,4 Z`, `fill="currentColor"`) — confirm T002 test now passes (Green)

**Checkpoint**: Login page displays the Wilson W mark. Run `ddev exec --raw php artisan test --compact --filter=WilsonLogoTest` to verify.

---

## Phase 3: User Story 2 — Wilson Logo on Registration Page (Priority: P2)

**Goal**: The registration page renders the same Wilson W monogram, confirming consistent branding across the login/register pair.

**Independent Test**: Navigate to `/register` — Wilson W path string present; implementation is already done via the shared component from US1.

- [x] T004 [US2] Add test case asserting the Wilson W SVG `d` attribute substring (`M4,4 L13,36`) is present in the register page response in `tests/Feature/WilsonLogoTest.php` — test should pass immediately since the shared component was updated in T003

**Checkpoint**: Both login and register tests pass. The shared component change is confirmed to cover both routes.

---

## Phase 4: User Story 3 — All Auth Pages + Sidebar Brand Name (Priority: P3)

**Goal**: All authentication pages display the Wilson logo. The sidebar brand name reads "Wilson" instead of the hardcoded "Laravel Starter Kit".

**Independent Test**: Load any auth page (forgot-password, verify-email) — Wilson W path present. Load the authenticated dashboard — sidebar brand shows "Wilson".

- [x] T005 [US3] Replace the two hardcoded `name="Laravel Starter Kit"` attribute strings in `resources/views/components/app-logo.blade.php` with `:name="config('app.name')"` on both `<flux:brand>` and `<flux:sidebar.brand>` elements
- [x] T006 [US3] Add test case asserting the Wilson W SVG `d` attribute substring is present in the forgot-password page response in `tests/Feature/WilsonLogoTest.php` (covers all auth layout variants)

**Checkpoint**: All three user stories are complete. Full test suite passes.

---

## Phase 5: Polish

**Purpose**: Code formatting and final validation.

- [x] T007 Run `ddev exec --raw vendor/bin/pint --dirty` and commit any formatting fixes to `resources/views/components/app-logo-icon.blade.php` and `resources/views/components/app-logo.blade.php`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately
- **US1 (Phase 2)**: Depends on T001 (test file must exist before writing tests)
- **US2 (Phase 3)**: Depends on T003 (shared component must be updated first)
- **US3 (Phase 4)**: Can proceed after T001; T005 and T006 are independent of each other [P]
- **Polish (Phase 5)**: Depends on all implementation tasks complete

### User Story Dependencies

- **US1 (P1)**: Starts after T001. No dependency on US2 or US3.
- **US2 (P2)**: Depends on T003 (shared component). Test-only phase — no new implementation.
- **US3 (P3)**: T005 (brand name fix) is independent of US1/US2. T006 (test) depends on T001 only.

### Parallel Opportunities

- T005 and T006 within US3 can be worked in parallel — they touch different files

---

## Parallel Example: US3

```bash
# Both can run simultaneously:
Task T005: Fix brand name in resources/views/components/app-logo.blade.php
Task T006: Add forgot-password test to tests/Feature/WilsonLogoTest.php
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (T001)
2. Complete Phase 2: US1 (T002, T003)
3. **STOP and VALIDATE**: `ddev exec --raw php artisan test --compact --filter=WilsonLogoTest`
4. Login page shows Wilson W mark — MVP delivered

### Incremental Delivery

1. T001 → T002 → T003: Login page branded (MVP)
2. T004: Register page confirmed
3. T005 + T006: Sidebar fixed and remaining auth pages confirmed
4. T007: Polish and format

---

## Notes

- The Wilson W SVG path to use: `M4,4 L13,36 L20,18 L27,36 L36,4 L30,4 L24,30 L20,23 L16,30 L10,4 Z`
- The distinctive test substring to assert: `M4,4 L13,36` (unique to the Wilson mark)
- `APP_NAME=Wilson` is already set in `.env` — no environment changes needed
- No migrations, no models, no controllers — pure Blade/SVG change
- TDD cycle enforced by constitution: write test → confirm Red → implement → confirm Green
