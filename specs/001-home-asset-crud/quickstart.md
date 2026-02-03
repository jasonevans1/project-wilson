# Quickstart: Home Asset CRUD

**Feature**: 001-home-asset-crud
**Date**: 2026-02-02

This document is the developer's entry point. It lists every file that will be created or modified, in the order they should be touched during implementation, and explains why each one exists.

---

## New files (create via Artisan)

| # | Command | Output path | What it is |
|---|---------|-------------|------------|
| 1 | `php artisan make:enum AssetCategory --backed=string --no-interaction` | `app/Enums/AssetCategory.php` | Backed enum for the eight fixed asset categories |
| 2 | `php artisan make:enum AssetStatus --backed=string --no-interaction` | `app/Enums/AssetStatus.php` | Backed enum for Active / Archived states |
| 3 | `php artisan make:model Asset -mfs --no-interaction` | `app/Models/Asset.php`, `database/migrations/*_create_assets_table.php`, `database/factories/AssetFactory.php`, `database/seeders/AssetSeeder.php` | The core entity + its migration, factory, and seeder |
| 4 | `php artisan make:class AssetValidationRules --no-interaction` | `app/Concerns/AssetValidationRules.php` | Move into `app/Concerns/`; validation trait used by form component |
| 5 | `php artisan make:livewire assets.asset-list --no-interaction` | `app/Livewire/Assets/AssetList.php` + `resources/views/livewire/assets/asset-list.blade.php` | Page-level list component |
| 6 | `php artisan make:livewire assets.asset-form --no-interaction` | `app/Livewire/Assets/AssetForm.php` + `resources/views/livewire/assets/asset-form.blade.php` | Create / update form |
| 7 | `php artisan make:livewire assets.asset-detail --no-interaction` | `app/Livewire/Assets/AssetDetail.php` + `resources/views/livewire/assets/asset-detail.blade.php` | Single-asset detail + archive/restore/edit |
| 8 | `php artisan make:test --pest AssetCrudTest --no-interaction` | `tests/Feature/AssetCrudTest.php` | Feature tests covering all five user stories |
| 9 | `php artisan make:test --pest --unit AssetModelTest --no-interaction` | `tests/Unit/AssetModelTest.php` | Unit tests for the model (enum casts, relationship, factory states) |

---

## Existing files to modify

| File | Change |
|------|--------|
| `app/Models/User.php` | Add `assets(): HasMany<Asset>` relationship method |
| `routes/web.php` | Add `require __DIR__.'/assets.php';` |
| `routes/assets.php` | **New file** (not via Artisan) — single `Route::livewire` declaration |
| `resources/views/layouts/app/sidebar.blade.php` | Add "Assets" item to the Platform sidebar group |

---

## Implementation order

1. **Enums first** — `AssetCategory`, `AssetStatus`. No dependencies.
2. **Model + migration + factory + seeder** — depends on enums (migration uses string; model casts to enums).
3. **User relationship** — add `assets()` to `User.php`.
4. **Validation concern** — `AssetValidationRules`. No dependencies beyond the enum.
5. **Route file** — `routes/assets.php` + require in `web.php`.
6. **Sidebar nav** — add the link.
7. **Livewire components** — `AssetList`, then `AssetForm`, then `AssetDetail`. List depends on the others being renderable.
8. **Tests** — written *before* each component (TDD per Constitution). Feature tests first, unit tests after.
9. **Pint** — run `vendor/bin/pint --dirty` before any commit.
10. **Seed & smoke** — run `php artisan db:seed` and manually verify the assets page end-to-end.

---

## Key conventions to follow (from the codebase)

- Models use `casts()` method, not `$casts` property.
- Livewire components declare typed public properties (`public string $name = ''`).
- Validation rules live in a Concern trait; components call `$this->validate($this->assetRules())`.
- Views wrap in `<x-layouts::app>` for the sidebar layout.
- Flux components for all UI primitives: `<flux:input>`, `<flux:button>`, `<flux:select>`, `<flux:modal>`, etc.
- Named routes everywhere; `route('assets.index')` in links.
- `wire:navigate` on all internal links for SPA behaviour.
- Enum cases are TitleCase; backed values are lowercase strings.
- Factory states use arrow-function syntax: `fn (array $attributes) => [...]`.
- PHPDoc `@return` on methods that return arrays or structured data.
