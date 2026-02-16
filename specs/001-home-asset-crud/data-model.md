# Data Model: Home Asset CRUD

**Feature**: 001-home-asset-crud
**Date**: 2026-02-02

---

## Enums

### AssetCategory

Backed enum (string). Represents the fixed set of asset categories.

| Case | Value |
|------|-------|
| Appliance | `appliance` |
| Hvac | `hvac` |
| Plumbing | `plumbing` |
| Electrical | `electrical` |
| Roofing | `roofing` |
| Flooring | `flooring` |
| Exterior | `exterior` |
| Other | `other` |

### AssetStatus

Backed enum (string). Represents the lifecycle state of an asset.

| Case | Value |
|------|-------|
| Active | `active` |
| Archived | `archived` |

---

## Entities

### Asset

| Field | Type | Nullable | Default | Notes |
|-------|------|----------|---------|-------|
| id | bigint (auto-increment) | — | — | Primary key |
| user_id | bigint (foreign key → users.id) | — | — | Owner; cascades on delete |
| name | string (255) | — | — | Required |
| category | string (enum: AssetCategory) | — | — | Required; cast to enum on model |
| location | string (255) | — | — | Required |
| purchase_date | date | yes | null | Optional |
| install_date | date | yes | null | Optional |
| warranty_expiration_date | date | yes | null | Optional |
| notes | text | yes | null | Optional; max 2 000 chars enforced at validation |
| status | string (enum: AssetStatus) | — | `active` | Cast to enum; defaults to Active on create |
| created_at | timestamp | yes | auto | Laravel convention |
| updated_at | timestamp | yes | auto | Laravel convention |

### Relationships

- **User → Asset**: one-to-many. `User::assets()` returns `HasMany<Asset>`. Scoped to the authenticated user in all queries.
- **Asset → User**: many-to-one. `Asset::user()` returns `BelongsTo<User>`.

### State Transitions

```
         ┌──────────┐
  create │          │ archive
   ─────►│  Active  │────────►  Archived
         │          │◄────────
         └──────────┘  restore
```

- **Active → Archived**: triggered by the archive action (requires confirmation).
- **Archived → Active**: triggered by the restore action (no confirmation required — it is a reversible, low-risk action).
- No other transitions exist. Permanent deletion is not permitted (FR-015).

---

## Validation Rules (enforced at the Livewire layer via AssetValidationRules concern)

| Field | Rules |
|-------|-------|
| name | `required`, `string`, `max:255` |
| category | `required`, `enum:App\Enums\AssetCategory` |
| location | `required`, `string`, `max:255` |
| purchase_date | `nullable`, `date` |
| install_date | `nullable`, `date` |
| warranty_expiration_date | `nullable`, `date` |
| notes | `nullable`, `string`, `max:2000` |

---

## Seeding

The `AssetFactory` will ship with:
- A `definition()` that generates realistic asset data using Faker (random name drawn from a predefined list of home asset names, random category, random location from a small set like Kitchen / Bathroom / Basement / Garage / Attic / Living Room, random optional dates, optional lorem notes).
- An `archived()` state that sets `status` to `Archived`.

The `AssetSeeder` will create 5 assets for the default user (a mix of active and archived) so that the feature is immediately explorable after `php artisan db:seed`.
