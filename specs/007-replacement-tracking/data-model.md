# Data Model: Replacement Tracking

**Branch**: `007-replacement-tracking` | **Date**: 2026-03-04

## New Entities

### AssetReplacementEvent

Records a single replacement instance for an asset. Multiple records per asset constitute the full replacement history.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Primary key |
| asset_id | bigint | FK → assets.id, cascade delete, not null | The asset that was replaced |
| installed_at | date | not null | New installation date (drives timeline reset) |
| cost | decimal(10,2) | nullable | Actual replacement cost |
| notes | text | nullable | Free-text notes (brand, contractor, etc.) |
| expected_lifespan_years | unsigned smallint | nullable | Lifespan captured at time of replacement (snapshot; asset's active lifespan may differ) |
| created_at | timestamp | auto | When the replacement record was logged |
| updated_at | timestamp | auto | Last update timestamp |

**Indexes**:
- `(asset_id, installed_at DESC)` — for ordering history and finding the most recent replacement

---

### AssetReplacementAlert

Tracks which replacement alerts have been sent to prevent duplicates. One row per asset per alert type per active replacement cycle. Rows are **deleted** when a replacement event is recorded (starting a new cycle).

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Primary key |
| asset_id | bigint | FK → assets.id, cascade delete, not null | The asset this alert is for |
| alert_type | string (enum) | not null | One of: `two_year`, `one_year`, `overdue` |
| sent_at | timestamp | nullable | When the alert notification was dispatched |
| dismissed_at | timestamp | nullable | When the user dismissed the overdue alert via email link |
| created_at | timestamp | auto | Record creation timestamp |
| updated_at | timestamp | auto | Record update timestamp |

**Unique constraint**: `(asset_id, alert_type)` — one alert record per asset per type per cycle.

**Indexes**:
- `(asset_id, alert_type)` — covered by unique constraint
- `(sent_at)` — for finding unsent alerts during daily run

---

### ReplacementAlertType Enum

| Case | Value | Description |
|------|-------|-------------|
| TwoYear | `two_year` | Fires when ≤ 2 years remaining before expected replacement date |
| OneYear | `one_year` | Fires when ≤ 1 year remaining before expected replacement date |
| Overdue | `overdue` | Fires when asset has passed its expected replacement date |

---

## Existing Entities (Modified)

### Asset (existing — new columns via migration)

Two new columns added:

| New Field | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| expected_lifespan_years | unsigned smallint | nullable | User-configured (or default) expected lifespan in whole years |
| replacement_alerts_enabled | boolean | default true, not null | Per-asset opt-out from replacement alert notifications |

**Existing field used**:

| Field | Usage |
|-------|-------|
| install_date | Starting point for replacement date calculation: `install_date + expected_lifespan_years` |
| category | Drives default lifespan lookup via `AssetLifespanDefaults::forCategory()` |
| user_id | Authorization; also used by notification command to look up the user |
| name | Included in alert notification content |

---

### User (existing — new column via migration)

| New Field | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| replacement_alerts_enabled | boolean | default true, not null | Global opt-out from all replacement alert notifications |

---

## Relationships

```
User (1) ──→ (Many) Asset
Asset (1) ──→ (Many) AssetReplacementEvent
Asset (1) ──→ (Many) AssetReplacementAlert   [max 3 active rows via unique constraint]

Asset traversal for alerts:
  Asset ──→ User   (via asset.user_id — for notification delivery)
```

---

## Computed Values (Not Stored)

These values are calculated at runtime from stored data; they are never persisted.

| Computed Value | Formula |
|----------------|---------|
| Expected replacement date | `install_date + expected_lifespan_years` (in years) |
| Days remaining | `expected_replacement_date - today()` |
| Years remaining | `days_remaining / 365.25` |
| Useful life % consumed | `(today - install_date) / (expected_lifespan_years * 365.25) * 100` |
| Is overdue | `expected_replacement_date < today()` |
| Is tracked | `expected_lifespan_years IS NOT NULL AND install_date IS NOT NULL` |

---

## State Transitions

### AssetReplacementAlert Lifecycle

```
[Not yet created]  →  no row in table for this (asset, alert_type)
        │
        └──→ [Pending]  →  row created, sent_at = null
                  │
                  ├──→ [Sent]  →  sent_at = timestamp
                  │         │
                  │         ├──→ [Dismissed]  →  dismissed_at = timestamp  (Overdue type only)
                  │         │
                  │         └──→ [Cycle Reset]  →  row DELETED when replacement event recorded
                  │
                  └──→ [Skipped]  →  asset opted out or user opted out (row never created)
```

---

## Validation Rules

| Entity | Field | Rules |
|--------|-------|-------|
| Asset | expected_lifespan_years | nullable, integer, min:1, max:100 |
| Asset | install_date | nullable, date, before_or_equal:today |
| AssetReplacementEvent | asset_id | required, exists:assets,id |
| AssetReplacementEvent | installed_at | required, date, before_or_equal:today |
| AssetReplacementEvent | cost | nullable, numeric, min:0, max:9999999.99 |
| AssetReplacementEvent | expected_lifespan_years | nullable, integer, min:1, max:100 |
| AssetReplacementAlert | asset_id | required, exists:assets,id |
| AssetReplacementAlert | alert_type | required, valid ReplacementAlertType enum value |
| AssetReplacementAlert | dismissed_at | nullable, date |
