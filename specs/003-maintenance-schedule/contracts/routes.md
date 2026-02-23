# Route Contracts: Maintenance Schedule & Task Management

**File**: `routes/maintenance.php` (required from `routes/web.php`)
**Middleware**: `['auth', 'verified']` on all routes

## Route Definitions

| Name | Method | URI | Livewire Component | Description |
|------|--------|-----|--------------------|-------------|
| `maintenance.schedule` | GET | `/maintenance` | `MaintenanceSchedule` | Consolidated schedule view for all assets |
| `maintenance.asset` | GET | `/assets/{asset}/maintenance` | `MaintenanceTaskList` | Per-asset task list and history |

### Route Parameters

**`/assets/{asset}/maintenance`**
- `{asset}` — the `Asset` model ID; must belong to the authenticated user (enforced via route model binding + policy or manual check in component)

### Navigation Integration

- Add "Maintenance" link to the main navigation (alongside "Assets")
- On the asset detail panel, add a "Maintenance" tab or button linking to `route('maintenance.asset', $asset)`
