# Quickstart: Asset Template System

**Feature**: 002-asset-template-system
**Date**: 2026-02-16

---

## Prerequisites

- Feature 001-home-asset-crud fully implemented (Asset model, AssetCategory enum, AssetValidationRules concern, AssetList/AssetForm/AssetDetail Livewire components)
- Branch `002-asset-template-system` checked out

## New Files to Create

### Models & Database

| File | Command |
|------|---------|
| `app/Models/TemplateGroup.php` | `php artisan make:model TemplateGroup -mfs --no-interaction` |
| `app/Models/AssetTemplate.php` | `php artisan make:model AssetTemplate -mfs --no-interaction` |
| `database/migrations/*_create_template_groups_table.php` | Created by make:model |
| `database/migrations/*_create_asset_templates_table.php` | Created by make:model |
| `database/factories/TemplateGroupFactory.php` | Created by make:model |
| `database/factories/AssetTemplateFactory.php` | Created by make:model |
| `database/seeders/TemplateGroupSeeder.php` | Created by make:model |
| `database/seeders/AssetTemplateSeeder.php` | Created by make:model |

### Livewire Component

| File | Command |
|------|---------|
| `app/Livewire/Assets/TemplateLibrary.php` | `php artisan make:livewire Assets/TemplateLibrary --no-interaction` |
| `resources/views/livewire/assets/template-library.blade.php` | Created by make:livewire |

### Tests

| File | Command |
|------|---------|
| `tests/Feature/TemplateLibraryTest.php` | `php artisan make:test --pest TemplateLibraryTest --no-interaction` |
| `tests/Unit/TemplateModelTest.php` | `php artisan make:test --pest --unit TemplateModelTest --no-interaction` |

## Existing Files to Modify

| File | Change |
|------|--------|
| `routes/assets.php` | Add template library route |
| `app/Livewire/Assets/AssetList.php` | Add "Add from Templates" button, update empty state |
| `resources/views/livewire/assets/asset-list.blade.php` | Update view with template button and empty state CTA |
| `database/seeders/DatabaseSeeder.php` | Call TemplateGroupSeeder and AssetTemplateSeeder |

## Implementation Order

1. **Models & Migrations** — TemplateGroup and AssetTemplate with relationships, casts, factories
2. **Seeders** — Populate 8 groups and 30+ templates
3. **Unit Tests** — Model relationships, enum casts, factory states
4. **TemplateLibrary Component** — Multi-step wizard (browse → review → confirm)
5. **Feature Tests** — Full user journey: browse, select, customize, convert
6. **AssetList Updates** — Template button in header, empty state promotion
7. **Integration Tests** — End-to-end flow including navigation between AssetList and TemplateLibrary
8. **Pint** — `vendor/bin/pint --dirty` before finalizing

## Key Patterns to Follow

- **Enum casts**: Use `casts()` method on models (not `$casts` property)
- **Validation**: Reuse `AssetValidationRules` concern for template-to-asset conversion
- **Ownership**: `Auth::user()->assets()->create()` for automatic user_id assignment
- **Computed properties**: Use `#[Computed]` for templateGroups and ownedAssetNames
- **UI**: Flux UI components (`flux:card`, `flux:checkbox`, `flux:badge`, `flux:button`)
- **Translations**: Wrap all user-facing strings in `__()`
- **Array validation rules**: Follow existing convention from AssetValidationRules
- **Events**: Dispatch `assets-created` after batch conversion
