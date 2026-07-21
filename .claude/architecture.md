# Architecture

## Directory Structure
- `app/Models/` - Eloquent models
- `app/Livewire/` - Livewire 4 components, grouped by feature (`Assets`, `Maintenance`, `ServiceRecords`, `Dashboard`, `ReplacementTracking`, `Settings`, `Actions`)
- `app/Actions/` - Single-purpose action classes (includes `app/Actions/Fortify` for auth actions)
- `app/Services/` - Business logic services
- `app/Http/Controllers/` - HTTP request handlers
- `app/Http/Requests/` - Form request validation
- `app/Notifications/` - Notification classes (e.g. maintenance reminder emails)
- `app/Enums/` - Enum types
- `app/Concerns/` - Shared traits
- `resources/views/livewire/` - Blade views for Livewire components, mirroring `app/Livewire/` structure
- `resources/views/flux/` - Flux UI component customizations
- `tests/Unit/` - Unit tests (mirrors `app/` structure)
- `tests/Feature/` - Feature/integration tests, grouped by feature area

## Patterns Used
- Livewire components as the primary UI/interaction layer (no separate SPA/API frontend)
- Action classes for discrete business operations (Fortify auth actions, notification-triggering actions)
- Form Requests for validation where plain HTTP controllers are used
- Eloquent relationships over raw queries/joins

## Conventions
- Livewire components and their Blade views mirror each other 1:1 by feature folder.
- Tests mirror source structure under `tests/Unit` and `tests/Feature`.
- Flux UI (`livewire/flux`) components used for UI primitives; Tailwind CSS v4 for styling.
- Alpine.js used only for client-side interaction Livewire doesn't cover.

## Key Integrations
- **Laravel Fortify** - headless auth, including TOTP-based two-factor authentication.
- **Mailgun** (via `symfony/mailgun-mailer` + `symfony/http-client`) - transactional email (maintenance reminders).
- **Laravel Cloud** - deployment target; GitHub Actions pipeline lints, tests, and deploys on push to `main`.
- **DDEV** - local development environment; run project commands via `ddev exec`.
