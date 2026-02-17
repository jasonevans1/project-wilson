# Research: Asset Template System

**Feature**: 002-asset-template-system
**Date**: 2026-02-16

---

## Decision 1: Template Data Storage

**Decision**: Database-backed models (TemplateGroup, AssetTemplate) populated via seeders.

**Rationale**: Templates need to be queryable (filter by group, count per group, match against existing assets for "already owned" badge). Database storage aligns with the existing Eloquent-first architecture and enables eager loading. Seeders provide a clean, versionable way to manage the template library.

**Alternatives considered**:
- **Config/array-based**: Simpler but lacks query capabilities for grouping, counting, and "already owned" matching. Would require loading entire library into memory.
- **JSON file**: Similar limitations to config-based. No relationship traversal.

---

## Decision 2: Multi-Step Flow Architecture

**Decision**: Single Livewire component (`TemplateLibrary`) with a `$step` property managing three steps: browse, review, confirm.

**Rationale**: Follows the existing pattern where `AssetList` manages multiple states via conditional rendering. A single component avoids the complexity of cross-component state management for selected templates. Livewire's reactive properties handle step transitions naturally.

**Alternatives considered**:
- **Separate route per step**: Over-engineered for a 3-step flow. Requires URL-based state or session persistence for selected templates.
- **Nested child components per step**: Adds event communication overhead for shared state (selected templates). Livewire docs recommend avoiding unnecessary nesting.
- **Modal-based flow**: Template library is too content-rich for a modal. A dedicated page provides better UX for browsing 30+ items.

---

## Decision 3: Template Selection State Management

**Decision**: Store selected template IDs as a public array property (`$selectedTemplateIds`). On transition to review step, hydrate into an editable array of field values (`$customizedItems`).

**Rationale**: Keeping IDs during browsing is lightweight and survives Livewire re-renders. Hydrating to editable arrays only when entering review avoids premature data duplication. The `$customizedItems` array holds all field values per item, enabling independent editing of duplicate selections.

**Alternatives considered**:
- **Session-based storage**: Unnecessary complexity; Livewire properties persist across requests within the same component lifecycle.
- **Eager hydration on select**: Wasteful — users may select and deselect many times before proceeding to review.

---

## Decision 4: "Already Owned" Badge Implementation

**Decision**: Computed property on TemplateLibrary that loads the authenticated user's asset names and compares against template names during rendering.

**Rationale**: Simple name-based matching is sufficient per the spec clarification. A computed property memoizes the query for the request lifecycle. The asset count is typically small (< 100 per user), so an in-memory comparison is efficient.

**Alternatives considered**:
- **Database join/subquery**: Over-engineered for the expected data volume.
- **Real-time check per template**: N+1 problem; batch loading is better.

---

## Decision 5: Template-to-Asset Conversion

**Decision**: Reuse the existing `AssetValidationRules` concern to validate each customized item, then batch-create assets via `Auth::user()->assets()->create()` in a loop within a database transaction.

**Rationale**: Reuses existing validation infrastructure (FR-011). Database transaction ensures atomicity — either all assets are created or none (preventing partial conversions on validation failure). The existing ownership pattern (`Auth::user()->assets()->create()`) automatically sets `user_id`.

**Alternatives considered**:
- **Bulk insert**: Faster but bypasses model events and doesn't return created models for confirmation.
- **Queue-based creation**: Unnecessary for < 20 items. Adds complexity without benefit.

---

## Decision 6: Route and Navigation

**Decision**: Dedicated route `assets/templates` rendering the `TemplateLibrary` component. Accessible via a button on the asset list page and promoted in the empty state.

**Rationale**: Follows the existing routing pattern (`routes/assets.php`). A dedicated page provides sufficient space for browsing grouped templates. The existing `AssetList` component already has conditional rendering for empty state, making it straightforward to add a template promotion CTA.

**Alternatives considered**:
- **Tab within AssetList**: Overloads an already complex component. The template flow is a distinct user journey.
- **Modal overlay**: Too constrained for 30+ templates organized in groups.

---

## Decision 7: Flux UI Components for Template Cards

**Decision**: Use `flux:card` for template items with `flux:checkbox` for selection, `flux:badge` for "already owned" indicator, and Alpine.js `x-show` for expandable groups.

**Rationale**: Flux UI Free v2 provides card, checkbox, and badge components that match the existing UI language. Flux does not have a dedicated accordion component in the free tier, but Alpine.js expandable sections with heading buttons provide equivalent functionality while staying consistent with the existing Alpine.js usage patterns in the codebase.

**Alternatives considered**:
- **flux:navlist.group expandable**: Designed for navigation, not content cards. Wrong semantic context.
- **Custom component**: Unnecessary when standard Flux + Alpine.js covers the need.
