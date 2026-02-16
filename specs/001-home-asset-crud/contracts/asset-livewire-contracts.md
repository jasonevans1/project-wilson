# Livewire Component Contracts: Home Asset CRUD

**Feature**: 001-home-asset-crud
**Date**: 2026-02-02

These contracts define the public interface of each Livewire component. They are the boundary that tests and other components rely on. Internal implementation details are omitted.

---

## AssetList

**Route**: `GET /assets` → `App\Livewire\Assets\AssetList`
**Middleware**: `auth`, `verified`
**View**: `livewire/assets/asset-list.blade.php`
**Purpose**: Displays the paginated list of the authenticated user's assets. Owns the active/archived toggle and the "open detail" / "open create form" state transitions.

### Public Properties

| Property | Type | Default | Bound to |
|----------|------|---------|----------|
| `showArchived` | `bool` | `false` | Toggle switch in view |
| `selectedAssetId` | `?int` | `null` | Internal state; when set, `AssetDetail` is rendered |
| `showCreateForm` | `bool` | `false` | Internal state; when true, `AssetForm` (create mode) is rendered |

### Public Actions

| Action | Parameters | Triggered by | Behaviour |
|--------|-----------|--------------|-----------|
| `toggleArchived()` | — | Toggle switch | Flips `showArchived`; resets `selectedAssetId` and `showCreateForm` |
| `selectAsset(int $id)` | asset id | Click on list row | Sets `selectedAssetId`; hides create form |
| `openCreateForm()` | — | "Add Asset" button | Sets `showCreateForm = true`; clears `selectedAssetId` |
| `closePanel()` | — | Back / close button in detail or form | Resets `selectedAssetId` and `showCreateForm` |

### Computed Properties

| Property | Type | Description |
|----------|------|-------------|
| `assets` | `LengthAwarePaginator` | Paginated collection of the user's assets filtered by `showArchived`. Eager-loads nothing (Asset has no relationships to traverse). |

### Events listened to

| Event | Action taken |
|-------|--------------|
| `asset-created` | Resets `showCreateForm`; refreshes `assets` |
| `asset-updated` | Refreshes `assets` |
| `asset-archived` | Resets `selectedAssetId`; refreshes `assets` |
| `asset-restored` | Refreshes `assets` |

---

## AssetForm

**Rendered by**: `AssetList` (not routed independently)
**View**: `livewire/assets/asset-form.blade.php`
**Purpose**: A single form component that handles both create and update. Driven by an optional `$asset` prop.

### Public Properties

| Property | Type | Default | Notes |
|----------|------|---------|-------|
| `name` | `string` | `''` | Wire-model bound |
| `category` | `string` | `''` | Wire-model bound; string value of the enum |
| `location` | `string` | `''` | Wire-model bound |
| `purchaseDate` | `?string` | `null` | Wire-model bound; date string or null |
| `installDate` | `?string` | `null` | Wire-model bound |
| `warrantyExpirationDate` | `?string` | `null` | Wire-model bound |
| `notes` | `?string` | `null` | Wire-model bound |

### Props

| Prop | Type | Required | Notes |
|------|------|----------|-------|
| `asset` | `?App\Models\Asset` | no | If provided, form runs in update mode and pre-populates all fields. If null, create mode. |

### Public Actions

| Action | Triggered by | Behaviour |
|--------|--------------|-----------|
| `save()` | Form submit | Validates via `AssetValidationRules`. In create mode: creates an Asset scoped to `Auth::user()`, dispatches `asset-created`. In update mode: updates the existing Asset, dispatches `asset-updated`. |
| `cancel()` | Cancel button | Dispatches `close-panel` to parent (no data change). |

### Events dispatched

| Event | When |
|-------|------|
| `asset-created` | After a successful create |
| `asset-updated` | After a successful update |
| `close-panel` | When cancel is clicked |

---

## AssetDetail

**Rendered by**: `AssetList` (not routed independently)
**View**: `livewire/assets/asset-detail.blade.php`
**Purpose**: Displays all fields of a single asset. Provides buttons to edit (swap to form), archive, or restore.

### Props

| Prop | Type | Required | Notes |
|------|------|----------|-------|
| `asset` | `App\Models\Asset` | yes | The asset to display |

### Public Properties

| Property | Type | Default | Notes |
|----------|------|---------|-------|
| `confirmingArchive` | `bool` | `false` | Controls the archive confirmation modal |
| `editMode` | `bool` | `false` | When true, renders `AssetForm` in update mode instead of the read-only detail |

### Public Actions

| Action | Triggered by | Behaviour |
|--------|--------------|-----------|
| `startEdit()` | Edit button | Sets `editMode = true` |
| `cancelEdit()` | Cancel in form | Sets `editMode = false` |
| `initiateArchive()` | Archive button | Sets `confirmingArchive = true` (shows modal) |
| `archive()` | Confirm button in modal | Sets asset status to Archived, saves, dispatches `asset-archived` |
| `cancelArchive()` | Cancel button in modal | Sets `confirmingArchive = false` |
| `restore()` | Restore button (visible only when asset is archived) | Sets asset status to Active, saves, dispatches `asset-restored` |

### Events dispatched

| Event | When |
|-------|------|
| `asset-archived` | After archive is confirmed and saved |
| `asset-restored` | After restore is saved |
| `close-panel` | When back button is clicked |

### Events listened to

| Event | Action taken |
|-------|--------------|
| `asset-updated` | Refreshes the local `$asset` prop from the database; sets `editMode = false` |

---

## Route Contract

| Method | Path | Named Route | Middleware | Component |
|--------|------|-------------|------------|-----------|
| GET | `/assets` | `assets.index` | `auth`, `verified` | `AssetList` |

All sub-interactions (detail, create, update, archive, restore) are handled reactively within the component tree. No additional HTTP routes are required.

---

## Navigation Contract

A new sidebar item is added to the "Platform" group in `resources/views/layouts/app/sidebar.blade.php`:

```
icon: wrench
label: Assets
href: route('assets.index')
current: request()->routeIs('assets.*')
wire:navigate: true
```
