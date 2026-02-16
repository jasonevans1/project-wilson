# Implementation Plan: Asset Template System

**Branch**: `002-asset-template-system` | **Date**: 2026-02-16 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/002-asset-template-system/spec.md`

## Summary

Build a template system that lets homeowners quickly add common home items from a pre-built library instead of manually entering each asset. The system provides a multi-step flow: browse templates organized by group, select and customize items, then batch-convert them into real assets. Two new database-backed models (TemplateGroup, AssetTemplate) store the template library as seed data. A single Livewire component (TemplateLibrary) manages the wizard flow, reusing the existing AssetValidationRules concern for conversion validation. The asset list empty state is updated to promote templates for new users.

## Technical Context

**Language/Version**: PHP 8.3
**Primary Dependencies**: Laravel 12, Livewire 4, Flux UI Free v2
**Storage**: MariaDB 10.11 (via Eloquent; 2 new tables: template_groups, asset_templates)
**Testing**: Pest 4 (feature + unit tests)
**Target Platform**: Web application (server-rendered with Livewire)
**Project Type**: Web application (Laravel monolith)
**Performance Goals**: Template library page loads in < 1 second with 30+ templates across 8 groups
**Constraints**: ~30 seed templates, ~8 groups. No external API calls. All data is local.
**Scale/Scope**: Single-user template browsing. Batch creation of ≤ 20 assets per conversion.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] I. Modularity: each new module has a single, declared purpose
      - TemplateGroup: single purpose (template organization)
      - AssetTemplate: single purpose (template blueprint)
      - TemplateLibrary component: single purpose (template wizard flow)
      - All created via `php artisan make:` commands
      - Model + factory + seeder triplet for each new model
- [x] II. Separation of Concerns: business logic lives outside controllers
      and Livewire views; validation is in Form Requests
      - Validation reuses existing AssetValidationRules concern (trait)
      - No raw DB queries; Eloquent-only
      - UI uses Flux components exclusively
- [x] III. High Cohesion: related domain logic is grouped; no cross-domain
      files added without justification
      - New models in app/Models/
      - New component in app/Livewire/Assets/ (same domain as existing asset components)
      - Enums reuse existing AssetCategory (no new enums needed)
      - Casts via `casts()` method
- [x] IV. Information Hiding: new public interfaces are minimal; internal
      helpers are protected/private; all types are declared
      - Models expose only necessary relationships
      - Component actions are public (Livewire requirement); computed properties handle derived data
      - All methods have explicit return types
- [x] V. Appropriate Coupling: dependency direction flows inward
      (domain ← application ← infrastructure); no circular dependencies;
      eager loading applied where relationships are traversed
      - TemplateGroup eager-loads templates
      - TemplateLibrary depends on models (not the reverse)
      - No cross-component direct calls; event-based communication where needed

## Project Structure

### Documentation (this feature)

```text
specs/002-asset-template-system/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── template-livewire-contracts.md
└── tasks.md             # Phase 2 output (/speckit.tasks command)
```

### Source Code (repository root)

```text
app/
├── Models/
│   ├── TemplateGroup.php          # NEW — template group model
│   └── AssetTemplate.php          # NEW — asset template model
├── Livewire/
│   └── Assets/
│       ├── AssetList.php          # MODIFIED — add template button + empty state update
│       └── TemplateLibrary.php    # NEW — multi-step template wizard
├── Concerns/
│   └── AssetValidationRules.php   # EXISTING — reused for conversion validation

database/
├── migrations/
│   ├── *_create_template_groups_table.php    # NEW
│   └── *_create_asset_templates_table.php    # NEW
├── factories/
│   ├── TemplateGroupFactory.php              # NEW
│   └── AssetTemplateFactory.php              # NEW
├── seeders/
│   ├── TemplateGroupSeeder.php               # NEW
│   ├── AssetTemplateSeeder.php               # NEW
│   └── DatabaseSeeder.php                    # MODIFIED — call new seeders

resources/views/livewire/assets/
├── asset-list.blade.php            # MODIFIED — template button + empty state
└── template-library.blade.php      # NEW — wizard view

routes/
└── assets.php                      # MODIFIED — add template route

tests/
├── Feature/
│   └── TemplateLibraryTest.php     # NEW
└── Unit/
    └── TemplateModelTest.php       # NEW
```

**Structure Decision**: Laravel 12 standard monolith structure. All new files follow existing directory conventions. New models, components, and tests are co-located with the existing asset feature files.

## Complexity Tracking

No constitution violations. All design decisions follow existing patterns.
