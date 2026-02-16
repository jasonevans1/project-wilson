# Livewire Component Contracts: Asset Template System

**Feature**: 002-asset-template-system
**Date**: 2026-02-16

---

## TemplateLibrary Component

**Path**: `app/Livewire/Assets/TemplateLibrary.php`
**View**: `resources/views/livewire/assets/template-library.blade.php`
**Route**: `GET /assets/templates` → `assets.templates`

### Purpose

Multi-step wizard for browsing, selecting, customizing, and converting asset templates into home assets.

### Properties

```
step: int = 1                          # Current wizard step (1=browse, 2=review, 3=confirm)
selectedTemplateIds: array = []         # Template IDs selected during browsing
customizedItems: array = []             # Editable field arrays for review step
expandedGroups: array = []              # Track which groups are expanded in browse view
createdCount: int = 0                   # Number of assets created (for confirmation step)
```

### Computed Properties

```
templateGroups(): Collection<TemplateGroup>
    # All groups with their templates, ordered by display_order
    # Eager loads: templates (ordered by display_order)

ownedAssetNames(): array
    # Array of lowercase asset names owned by authenticated user
    # Used for "already owned" badge comparison

selectedCount(): int
    # Count of selectedTemplateIds (convenience for UI badge)
```

### Actions

```
toggleTemplate(int $templateId): void
    # Add/remove template ID from selectedTemplateIds
    # Does not prevent duplicates — each call toggles one instance

addTemplate(int $templateId): void
    # Add template ID to selectedTemplateIds (allows duplicates for FR-014)

removeSelectedTemplate(int $index): void
    # Remove a specific selection by index from selectedTemplateIds

proceedToReview(): void
    # Transition from step 1 → 2
    # Hydrate customizedItems from selectedTemplateIds
    # Each item: {templateId, name, category, location, purchaseDate, installDate, warrantyExpirationDate, notes}

removeCustomizedItem(int $index): void
    # Remove item from customizedItems during review (FR-006)

backToBrowse(): void
    # Transition from step 2 → 1
    # Clear customizedItems (re-hydrated on next proceedToReview)

confirmConversion(): void
    # Validate all customizedItems using AssetValidationRules
    # Create assets in DB transaction via Auth::user()->assets()->create()
    # Set createdCount and transition to step 3

backToAssets(): void
    # Redirect to assets.index route
```

### Events Dispatched

```
assets-created    # After successful batch conversion (listened by AssetList if navigated back)
```

### Validation

Uses `AssetValidationRules` concern for each item in `customizedItems` during `confirmConversion()`. Validation errors are keyed per item index (e.g., `customizedItems.0.name`).

---

## AssetList Component Updates

**Path**: `app/Livewire/Assets/AssetList.php` (existing)

### Changes Required

1. **Empty state update** (FR-016): When user has no active assets, the empty state view promotes the template flow as the primary action:
   - Primary CTA: "Add from Templates" → links to `route('assets.templates')`
   - Secondary CTA: "Add Manually" → opens create form (existing behavior)

2. **Header button addition**: Add an "Add from Templates" button alongside the existing "Add Asset" button in the header area.

---

## Route Registration

**File**: `routes/assets.php`

```
Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('assets', AssetList::class)->name('assets.index');        # existing
    Route::livewire('assets/templates', TemplateLibrary::class)->name('assets.templates');  # new
});
```
