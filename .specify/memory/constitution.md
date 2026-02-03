# Wilson Constitution

## Core Principles

### I. Modularity

Every feature is delivered as a single-purpose module. New files are created
exclusively via `php artisan make:` commands. Every new model ships as a
model + factory + test triplet. Livewire components are scoped to one
domain concern; a component that serves two unrelated screens must be split.

### II. Separation of Concerns

Data access is Eloquent-only — `DB::` is never used outside of migrations.
Request validation lives in Form Request classes and uses array syntax for
rules. The UI layer is Flux UI (`<flux:*>` components); raw HTML form
elements are not permitted where a Flux equivalent exists. Configuration is
read via `config()`; `env()` may only appear inside files under `config/`.
Intra-application links are generated with the `route()` helper and named
routes.

### III. High Cohesion

The project follows the Laravel 12 streamlined directory layout exactly as
shipped. Model casts are declared in a `casts()` method. Enum cases use
TitleCase. Each migration is self-contained and carries every attribute
required by the final column state. Shared behaviour is extracted into named
Concerns placed in the appropriate `Concerns/` directory.

### IV. Information Hiding

Every method and function declares an explicit return type and typed
parameters. Constructors use PHP 8 constructor property promotion. Public
surface area is kept minimal; helpers and intermediate logic are
`protected` or `private`. PHPDoc blocks with array-shape types are added
wherever a method accepts or returns structured data.

### V. Appropriate Coupling

Dependency direction is strictly one-way: domain ← application ←
infrastructure. Inter-component communication is event-based where a direct
call would cross domain boundaries. Eager loading is mandatory on every
query that traverses a relationship. Long-running or I/O-heavy operations
implement `ShouldQueue`. Circular dependencies are forbidden.

## Technology & Testing Standards

### Pinned Stack

| Layer | Technology | Version |
|---|---|---|
| Language | PHP | 8.3 |
| Framework | Laravel | 12 |
| Auth backend | Laravel Fortify | v1 |
| Database | MariaDB | 10.11 |
| Reactive UI | Livewire | 4 |
| Component library | Flux UI Free | v2 |
| Test runner | Pest | 4 |
| Browser tests | Playwright | latest |
| Code formatter | Laravel Pint | v1 |

No dependency may be added or upgraded without explicit approval.

### Test-Driven Development (Non-Negotiable)

Tests are written **first**, verified to fail, and only then is the
implementation written. The Red → Green → Refactor cycle is strictly
enforced on every changeset.

### Test Layers

| Layer | Tool | Scope | Trait / convention |
|---|---|---|---|
| Feature | Pest | HTTP-level behaviour | `RefreshDatabase`, closure-style (`it(...)`) |
| Unit | Pest | Single class / method | Pure logic, no DB |
| E2E / Browser | Playwright | Full user journey | Runs against a seeded database |

### Code Formatting

`vendor/bin/pint --dirty` is executed before every changeset is committed.
Formatting violations block delivery.

## Development Workflow

### Artisan Command Reference

| Purpose | Command | Key flags |
|---|---|---|
| Model + migration + factory + seeder | `make:model` | `-mfs --no-interaction` |
| Feature test (Pest) | `make:test --pest` | `--no-interaction` |
| Unit test (Pest) | `make:test --pest --unit` | `--no-interaction` |
| Form Request | `make:request` | `--no-interaction` |
| Livewire component | `make:livewire` | `--no-interaction` |
| Controller | `make:controller` | `--no-interaction` |
| Queued job | `make:job` | `--no-interaction` |
| Migration | `make:migration` | `--no-interaction` |
| Generic class | `make:class` | `--no-interaction` |

All Artisan commands must include `--no-interaction`.

### Laravel 12 Structural Rules

- Middleware is registered declaratively in `bootstrap/app.php` via
  `Application::configure()->withMiddleware()`. There is no
  `app/Http/Kernel.php`.
- `bootstrap/providers.php` holds application service providers.
- Console commands in `app/Console/Commands/` are auto-discovered; no
  manual registration is required.
- `app/Console/Kernel.php` does not exist; scheduled tasks live in
  `routes/console.php`.

### Eloquent & Data Access

- Use `Model::query()` as the entry point; never `DB::`.
- Declare return-typed relationship methods on every model.
- Eager-load (`with`, `load`) any relation that will be accessed in the
  request lifecycle.
- Complex read queries may use Laravel's query builder, but must still
  start from a model.

### Authentication & Authorization (Fortify)

- Fortify provides the headless auth backend (login, registration,
  password reset, email verification, 2FA).
- Route protection uses Laravel gates and policies — never hard-coded
  role checks in controllers.
- The `developing-with-fortify` skill must be activated before touching
  any auth flow.

### Livewire & Flux UI

- Every interactive page is a Livewire component.
- UI primitives come from Flux UI Free (`<flux:*>`). Raw HTML inputs are
  only used when no Flux equivalent exists.
- The `livewire-development` and `fluxui-development` skills must be
  activated when working on component code.

## Governance

- This Constitution supersedes `CLAUDE.md` for all decisions about code
  shape, structure, and conventions. `CLAUDE.md` remains authoritative for
  tooling invocation (skills, MCP servers, Artisan helpers).
- A **Constitution Check** gate must pass before every feature
  implementation begins, and again after the design phase completes.
- Amendments to this document require: a written description of the
  change, a rationale, a semver bump (MAJOR for principle changes, MINOR
  for clarifications, PATCH for typo fixes), and a corresponding update to
  any dependent templates (e.g., `plan-template.md`).
- If a Constitution Check gate is bypassed, the violation is recorded as a
  tracked technical-debt item in the feature's `Complexity Tracking`
  section before the implementation may proceed.

**Version**: 1.0.0 | **Ratified**: 2026-02-02 | **Last Amended**: 2026-02-02
