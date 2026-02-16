# Data Model: Asset Template System

**Feature**: 002-asset-template-system
**Date**: 2026-02-16

---

## Entities

### TemplateGroup

Represents a logical grouping of related asset templates (e.g., "Kitchen", "HVAC & Climate").

| Field | Type | Nullable | Default | Notes |
|-------|------|----------|---------|-------|
| id | bigint (auto-increment) | — | — | Primary key |
| name | string (255) | — | — | Required; display name (e.g., "Kitchen") |
| slug | string (255) | — | — | Required; unique; URL-friendly identifier |
| icon | string (100) | yes | null | Optional; Heroicon name for display |
| display_order | integer | — | 0 | Controls sort order in template library |
| created_at | timestamp | yes | auto | Laravel convention |
| updated_at | timestamp | yes | auto | Laravel convention |

**Indexes**: unique on `slug`, index on `display_order`

### AssetTemplate

Represents a pre-defined blueprint for a common home item.

| Field | Type | Nullable | Default | Notes |
|-------|------|----------|---------|-------|
| id | bigint (auto-increment) | — | — | Primary key |
| template_group_id | bigint (foreign key → template_groups.id) | — | — | Parent group; cascades on delete |
| name | string (255) | — | — | Required; pre-filled asset name |
| description | text | yes | null | Optional; explains what the item is |
| category | string (enum: AssetCategory) | — | — | Required; cast to enum; maps to existing AssetCategory |
| location | string (255) | — | — | Required; suggested location (e.g., "Kitchen") |
| display_order | integer | — | 0 | Controls sort order within group |
| created_at | timestamp | yes | auto | Laravel convention |
| updated_at | timestamp | yes | auto | Laravel convention |

**Indexes**: index on `template_group_id`, index on `display_order`

---

## Relationships

- **TemplateGroup → AssetTemplate**: one-to-many. `TemplateGroup::templates()` returns `HasMany<AssetTemplate>`.
- **AssetTemplate → TemplateGroup**: many-to-one. `AssetTemplate::group()` returns `BelongsTo<TemplateGroup>`.

No relationship to User or Asset — templates are system-wide, not user-specific. The "already owned" check is performed at query time by comparing template names against the authenticated user's asset names.

---

## Validation Rules

Templates are system-managed and read-only to users. Validation applies during seeding/creation only.

| Field | Rules |
|-------|-------|
| name | `required`, `string`, `max:255` |
| description | `nullable`, `string` |
| category | `required`, `enum:App\Enums\AssetCategory` |
| location | `required`, `string`, `max:255` |
| display_order | `required`, `integer`, `min:0` |

For template-to-asset conversion, the existing `AssetValidationRules` concern is reused. The customized items are validated with the same rules as manual asset creation.

---

## Seeding

### TemplateGroupSeeder

Creates the following groups (display_order determines browse order):

| Name | Slug | Icon | Order |
|------|------|------|-------|
| Kitchen | kitchen | — | 1 |
| Bathroom | bathroom | — | 2 |
| Laundry Room | laundry-room | — | 3 |
| Living Areas | living-areas | — | 4 |
| Bedroom | bedroom | — | 5 |
| HVAC & Climate | hvac-climate | — | 6 |
| Electrical & Safety | electrical-safety | — | 7 |
| Exterior & Garage | exterior-garage | — | 8 |

### AssetTemplateSeeder

Creates 30+ templates distributed across groups. Examples:

**Kitchen** (category: Appliance, location: Kitchen):
Refrigerator, Dishwasher, Oven/Range, Microwave, Garbage Disposal

**Bathroom** (mixed categories, location: Bathroom):
Water Heater (Plumbing), Bathroom Exhaust Fan (Electrical), Toilet (Plumbing), Bathroom Faucet (Plumbing)

**HVAC & Climate** (category: Hvac, location varies):
Central Air Conditioner, Furnace, Thermostat, Air Filter, Humidifier

**Exterior & Garage** (category: Exterior, location varies):
Garage Door Opener, Roof (Roofing/Roof), Gutters (Exterior/Exterior), Deck/Patio (Exterior/Backyard), Fence (Exterior/Yard)

*(Full seed data defined during implementation)*

### Factories

- `TemplateGroupFactory`: generates realistic group data with sequential display_order.
- `AssetTemplateFactory`: generates realistic template data with random category and location from predefined lists. Requires a `template_group_id`.

---

## State Diagram

Templates have no state transitions — they are static, read-only seed data. The conversion flow creates new Asset records (which follow the existing Active → Archived state machine from 001-home-asset-crud).

```
AssetTemplate (read-only)
        │
        │ user selects & customizes
        │
        ▼
    Asset (Active)
        │
        │ archive/restore (existing flow)
        │
        ▼
    Asset (Archived)
```
