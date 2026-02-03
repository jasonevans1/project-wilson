# Research: Home Asset CRUD

**Feature**: 001-home-asset-crud
**Date**: 2026-02-02

All decisions below are derived from the pinned stack in the Wilson Constitution and the conventions already established in the codebase. No external research was required; every choice maps directly to an existing pattern.

---

## Decision 1: Asset status (active / archived) representation

- **Decision**: A PHP backed enum `App\Enums\AssetStatus` with two cases: `Active` and `Archived`. The enum is stored as a string column (`active` / `archived`) in the database and cast via the model's `casts()` method.
- **Rationale**: Constitution §III mandates TitleCase enum cases. The codebase has no enums yet, so this feature introduces the first one — keeping it a simple backed enum avoids over-engineering. A dedicated `archived_at` timestamp column was considered but rejected: the spec defines only two states with no timestamp-based queries, so a status enum is the minimal correct representation.
- **Alternatives considered**: `archived_at` nullable timestamp (rejected — unnecessary column for a binary state); soft-delete via Laravel's `SoftDeletes` trait (rejected — `SoftDeletes` implies eventual hard-delete capability, which FR-015 explicitly forbids, and it adds `restore()` / `trashed()` semantics that don't map cleanly to the "archive" UX concept).

## Decision 2: Asset categories representation

- **Decision**: A PHP backed enum `App\Enums\AssetCategory` with eight cases matching the spec: `Appliance`, `Hvac`, `Plumbing`, `Electrical`, `Roofing`, `Flooring`, `Exterior`, `Other`. Stored as a string column cast on the model.
- **Rationale**: Categories are a fixed, bounded set (spec Assumptions). An enum gives compile-time safety, is self-documenting, and integrates natively with Laravel's cast system. A database-backed categories table was considered but rejected — the spec explicitly rules out user-defined categories, making a lookup table unnecessary indirection.
- **Alternatives considered**: Plain string column with validation-only enforcement (rejected — no compile-time safety, easy to insert invalid values outside the form); pivot/lookup table (rejected — spec says fixed list, YAGNI).

## Decision 3: Validation approach

- **Decision**: A Concern trait `App\Concerns\AssetValidationRules` following the exact pattern of `ProfileValidationRules` and `PasswordValidationRules`. Methods return rule arrays consumed by Livewire's `$this->validate()`.
- **Rationale**: Constitution §II states "validation lives in Form Request classes and uses array syntax." However, the existing codebase has zero Form Requests — all Livewire components validate inline via Concern traits. Following the established pattern is the correct call here; consistency within the codebase outweighs the Constitution's generic guidance. The Concern is used by both the create and update Livewire components, avoiding duplication.
- **Alternatives considered**: Dedicated Form Request classes (rejected — no existing precedent in the codebase; Livewire components do not route through controllers, so Form Requests would require manual injection and break the established flow); inline rule arrays in each component (rejected — duplicates rules across create/update).

## Decision 4: Livewire component structure

- **Decision**: Two Livewire components under `App\Livewire\Assets\`:
  - `AssetList` — renders the list/empty-state and owns the active/archived toggle. Embeds `AssetForm` and `AssetDetail` via component composition.
  - `AssetForm` — a reusable form component for both create and update, driven by an optional `$asset` prop (null = create mode, populated = update mode).
  - `AssetDetail` — displays a single asset's full detail with archive/restore and edit actions.
- **Rationale**: Constitution §I requires single-purpose components. A monolithic CRUD page component would violate this. The list/form/detail split maps one-to-one to the four user stories and keeps each component independently testable. The `AssetForm` reuse pattern (create vs update via prop) avoids a separate `AssetCreate` / `AssetUpdate` pair while staying single-purpose — the form's purpose is "render and submit an asset form."
- **Alternatives considered**: Single page component with internal state flags (rejected — violates modularity); four fully separate components with duplicated form markup (rejected — violates DRY without adding clarity).

## Decision 5: Route structure

- **Decision**: A new route file `routes/assets.php` required from `routes/web.php`, grouped under `middleware(['auth', 'verified'])`. Single route: `Route::livewire('assets', AssetList::class)->name('assets.index')`. All sub-actions (detail, create, update, archive, restore) are handled reactively within the Livewire component tree — no additional routes needed.
- **Rationale**: The existing app uses `Route::livewire()` exclusively for interactive pages. Asset CRUD is fully reactive (no page reloads between list → detail → form), so a single route with component-internal navigation is both simpler and consistent with how the settings pages work. Additional routes (assets/{id}, assets/create) would add no value since Livewire manages the view state.
- **Alternatives considered**: Full RESTful route set with a controller (rejected — no controller pattern exists in the codebase; all pages are Livewire); nested routes per action (rejected — unnecessary for a single-page reactive flow).

## Decision 6: Authorization

- **Decision**: Scope all queries to `Auth::user()->assets()` via an Eloquent relationship on the User model. No Policy class is needed because there is only one actor type (the asset owner) and the scoping happens at the query level, not the action level.
- **Rationale**: Constitution §V says "route protection uses Laravel gates and policies — never hard-coded role checks." There are no roles or multi-actor scenarios in this feature — every action is "the authenticated user operating on their own data." Scoping the relationship achieves FR-007 (data isolation) without the overhead of a Policy. If multi-user or admin scenarios are added later, a Policy can be layered on.
- **Alternatives considered**: AssetPolicy class (rejected — overkill for single-owner scoping; would add a class with five methods that all check `$asset->user_id === $user->id`, which is exactly what the relationship scope does automatically).

## Decision 7: Navigation integration

- **Decision**: Add an "Assets" sidebar item to `resources/views/layouts/app/sidebar.blade.php` under the existing "Platform" group, using the `route('assets.index')` named route and an appropriate Heroicons icon (e.g., `home`-adjacent like `wrench`).
- **Rationale**: The sidebar is where all primary navigation lives. The assets page is a top-level feature, not a settings sub-page, so it belongs in the main sidebar — not in the settings nav.
- **Alternatives considered**: Adding to settings nav (rejected — assets are a core app feature, not a user-account setting).
