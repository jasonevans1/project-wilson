# Implementation Plan: Replacement Tracking

**Branch**: `007-replacement-tracking` | **Date**: 2026-03-04 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/007-replacement-tracking/spec.md`

## Summary

Deliver end-of-life replacement tracking for home assets. Users configure an expected lifespan (with category-based defaults) and installation date per asset. The system calculates remaining useful life, shows a progress bar and urgency-sorted dashboard, and sends proactive replacement approaching and overdue alert emails via a daily scheduled command. A separate replacement event history log captures date, cost, and notes for each completed replacement. Built entirely on the existing Asset model, notification infrastructure, and Livewire + Flux UI stack.

## Technical Context

**Language/Version**: PHP 8.3
**Primary Dependencies**: Laravel 12, Livewire 4, Flux UI Free v2
**Storage**: MariaDB 10.11 — 2 new tables (`asset_replacement_events`, `asset_replacement_alerts`); 2 existing tables extended (`assets`, `users`)
**Testing**: Pest 4
**Target Platform**: Linux server (DDEV local dev)
**Project Type**: Web application (Laravel monolith)
**Performance Goals**: Dashboard renders all user assets with replacement status in under 2 seconds; daily alert command processes all users' assets within 5 minutes
**Constraints**: Signed URLs for email dismissal actions; database queue driver; eager loading mandatory on relationship traversal
**Scale/Scope**: 2 new models, 3 new Livewire components, 1 notification class, 1 command, 1 controller, 1 enum, 1 service class, 2 Form Requests, 7 test files

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] I. Modularity: each new module has a single, declared purpose
      - `AssetReplacementEvent` model: stores replacement history records
      - `AssetReplacementAlert` model: tracks alert dedup state per cycle
      - `ReplacementAlertType` enum: defines alert threshold types
      - `AssetLifespanDefaults` service: maps AssetCategory to default lifespan years (read-only reference data)
      - `SendReplacementAlerts` command: daily alert dispatch logic
      - `ReplacementAlertNotification`: email content for all 3 alert types
      - `ReplacementAlertDismissController`: handles signed dismiss URL
      - `ReplacementDashboard` component: unified asset replacement overview page
      - `ReplacementSetupForm` component: inline lifespan configuration for one asset
      - `RecordReplacementForm` component: inline replacement event recording
- [x] II. Separation of Concerns: business logic lives outside controllers and Livewire views; validation is in Form Requests
      - Alert dedup and send logic lives in `SendReplacementAlerts` command, not in the notification class
      - `ReplacementAlertDismissController` is thin (validate signature, set `dismissed_at`, redirect)
      - Validation is in `SaveReplacementSetupRequest` and `RecordReplacementRequest`
      - Computed values (days remaining, %, replacement date) are calculated in component logic / Blade, not in the controller or model
- [x] III. High Cohesion: related domain logic is grouped; no cross-domain files added without justification
      - All new Livewire components are under `App\Livewire\ReplacementTracking\`
      - Enum under `App\Enums\`, service under `App\Services\`, notifications under `App\Notifications\`
      - No new top-level directories — all additions fit the existing Laravel 12 layout
      - `AssetLifespanDefaults` placed in `App\Services\` alongside `MaintenanceScheduler`
- [x] IV. Information Hiding: new public interfaces are minimal; internal helpers are protected/private; all types are declared
      - `AssetLifespanDefaults::forCategory()` is the only public method on the service
      - All Livewire component actions have explicit return types (`void`)
      - `ReplacementAlertDismissController::__invoke()` has a typed return (`RedirectResponse`)
      - PHPDoc array-shape types on factory methods and collection returns
- [x] V. Appropriate Coupling: dependency direction flows inward; no circular dependencies; eager loading applied
      - Dependency direction: `ReplacementDashboard` → `Asset` → `AssetReplacementEvent` (one-directional)
      - Dashboard query: `Auth::user()->assets()->with(['replacementEvents' => ...])`
      - Alert command: `Asset::query()->with('user')` scoped by criteria — no circular back-references
      - `AssetReplacementAlert` rows deleted (not nullified) on replacement event to keep data clean
      - `ReplacementAlertNotification` implements `ShouldQueue` (I/O-heavy mail send)

## Project Structure

### Documentation (this feature)

```text
specs/007-replacement-tracking/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0 research decisions
├── data-model.md        # Phase 1 data model
├── quickstart.md        # Phase 1 implementation guide
├── contracts/
│   ├── livewire-components.md   # Component property/action/event contracts
│   └── alert-command.md         # Command logic + dismissal route contract
└── tasks.md             # Phase 2 output (/speckit.tasks command)
```

### Source Code (repository root)

```text
app/
├── Console/Commands/
│   └── SendReplacementAlerts.php              # Daily alert command
├── Enums/
│   └── ReplacementAlertType.php               # TwoYear, OneYear, Overdue
├── Http/Controllers/
│   └── ReplacementAlertDismissController.php  # Signed dismiss URL handler
├── Http/Requests/
│   └── Replacement/
│       ├── SaveReplacementSetupRequest.php    # Lifespan + install date validation
│       └── RecordReplacementRequest.php       # Replacement event validation
├── Livewire/
│   └── ReplacementTracking/
│       ├── ReplacementDashboard.php           # Main dashboard page component
│       ├── ReplacementSetupForm.php           # Inline lifespan setup form
│       └── RecordReplacementForm.php          # Inline replacement recording form
├── Models/
│   ├── AssetReplacementEvent.php              # Replacement history record
│   └── AssetReplacementAlert.php             # Alert dedup tracking
├── Notifications/
│   └── ReplacementAlertNotification.php       # Alert email (all 3 types)
└── Services/
    └── AssetLifespanDefaults.php              # Default lifespan map by AssetCategory

database/
├── factories/
│   ├── AssetReplacementEventFactory.php
│   └── AssetReplacementAlertFactory.php
└── migrations/
    ├── xxxx_add_replacement_columns_to_assets_table.php      # expected_lifespan_years, replacement_alerts_enabled
    ├── xxxx_add_replacement_alerts_enabled_to_users_table.php
    ├── xxxx_create_asset_replacement_events_table.php
    └── xxxx_create_asset_replacement_alerts_table.php

resources/views/
└── livewire/
    └── replacement-tracking/
        ├── replacement-dashboard.blade.php
        ├── replacement-setup-form.blade.php
        └── record-replacement-form.blade.php

routes/
├── replacement-tracking.php   # Dashboard route + dismiss signed route (new file)
├── web.php                    # + require replacement-tracking.php
└── console.php                # + Schedule replacement:send-alerts daily

tests/
├── Feature/
│   ├── ReplacementDashboardTest.php
│   ├── ReplacementSetupFormTest.php
│   ├── RecordReplacementFormTest.php
│   ├── SendReplacementAlertsTest.php
│   └── ReplacementAlertDismissTest.php
└── Unit/
    ├── ReplacementAlertTypeTest.php
    └── AssetLifespanDefaultsTest.php
```

**Structure Decision**: Follows the existing Laravel 12 streamlined directory layout. All additions use existing top-level directories. New Livewire components are namespaced under `ReplacementTracking\` to group the feature without creating new top-level folders.

## Complexity Tracking

> No constitution violations. All design decisions use standard Laravel patterns established by previous features in this project.
