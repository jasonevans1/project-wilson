# Implementation Plan: Wilson Brand Logo for Authentication Pages

**Branch**: `009-wilson-logo` | **Date**: 2026-03-08 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/009-wilson-logo/spec.md`

## Summary

Replace the generic Laravel starter kit cube icon with a custom Wilson "W" monogram SVG across all authentication pages. The change is confined to two shared Blade component files (`app-logo-icon.blade.php` and `app-logo.blade.php`), ensuring every auth layout and the sidebar brand automatically pick up the new identity without per-page edits. No data model, API contract, or backend logic is required.

## Technical Context

**Language/Version**: PHP 8.3
**Primary Dependencies**: Laravel 12, Livewire 4, Flux UI Free v2, Blade templates
**Storage**: N/A — pure UI change, no persistence
**Testing**: Pest 4 (feature tests asserting logo SVG presence on auth pages)
**Target Platform**: Web (DDEV local; Laravel Cloud production)
**Project Type**: Web application (Laravel MVC + Livewire)
**Performance Goals**: No specific performance targets — inline SVG renders instantly
**Constraints**: SVG must use `currentColor` fill to support both light and dark themes; must be legible at ~36px rendered size
**Scale/Scope**: 2 component files changed; 3 auth layout files and sidebar brand automatically inherit the update

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] I. Modularity: Change is confined to the `app-logo-icon` and `app-logo` components — each has a single declared purpose (render icon; render brand). No new component created for a one-file concern.
- [x] II. Separation of Concerns: Modification is UI-only (Blade/SVG). No business logic, no controller changes, no DB access.
- [x] III. High Cohesion: Logo components live in `resources/views/components/` alongside other shared UI components — correct placement per the existing directory structure.
- [x] IV. Information Hiding: The SVG detail is encapsulated inside the `app-logo-icon` component; consumers do not need to know or change. Public surface of the component is unchanged.
- [x] V. Appropriate Coupling: No new dependencies. Auth layout files already reference `<x-app-logo-icon>` — no coupling changes required.

All gates pass. No complexity tracking required.

## Project Structure

### Documentation (this feature)

```text
specs/009-wilson-logo/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── quickstart.md        # Phase 1 output
└── tasks.md             # Phase 2 output (/speckit.tasks - NOT created here)
```

> No `data-model.md` or `contracts/` directory required — this feature has no data entities or API endpoints.

### Source Code (files affected)

```text
resources/views/components/
├── app-logo-icon.blade.php   # MODIFY: replace SVG with Wilson "W" mark
└── app-logo.blade.php        # MODIFY: replace hardcoded "Laravel Starter Kit" with config('app.name')

# Automatically updated (no direct changes needed):
resources/views/layouts/auth/
├── card.blade.php            # Already uses <x-app-logo-icon> and config('app.name')
├── simple.blade.php          # Already uses <x-app-logo-icon> and config('app.name')
└── split.blade.php           # Already uses <x-app-logo-icon> and config('app.name')
resources/views/layouts/app/
└── sidebar.blade.php         # Uses <x-app-logo> which uses <x-app-logo-icon>

# New test file:
tests/Feature/
└── WilsonLogoTest.php        # Pest feature test asserting logo on login/register pages
```

**Structure Decision**: Single web application. The Laravel 12 streamlined layout is used as shipped. All changes are within the existing `resources/views/components/` and `tests/Feature/` directories.
