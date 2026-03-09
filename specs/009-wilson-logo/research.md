# Research: Wilson Brand Logo for Authentication Pages

**Date**: 2026-03-08 | **Branch**: `009-wilson-logo`

## Findings

### 1. Current Logo Architecture

**Decision**: The existing logo system uses two Blade components as the single source of truth.

| Component | File | Role |
|-----------|------|------|
| `<x-app-logo-icon>` | `resources/views/components/app-logo-icon.blade.php` | Raw SVG element; accepts `$attributes` (class, style, etc.) |
| `<x-app-logo>` | `resources/views/components/app-logo.blade.php` | Wraps `app-logo-icon` in a Flux brand/sidebar.brand context; hardcodes the app name |

All three auth layouts (`card`, `simple`, `split`) reference `<x-app-logo-icon>` directly and read the app name from `config('app.name', 'Laravel')`. Changing `app-logo-icon.blade.php` is sufficient to update the icon on all auth pages automatically.

**Rationale**: Confirmed by reading all three layout files. No per-page logo override exists.

**Alternatives considered**: Per-file SVG replacement was rejected because it would require touching 3+ layout files and risk future drift.

---

### 2. App Name — Already "Wilson"

**Decision**: No `.env` or `config/app.php` changes required for the app name.

**Rationale**: `APP_NAME=Wilson` is already set in `.env`. `config('app.name')` returns "Wilson" in all environments. Auth layouts already output this name via `config('app.name', 'Laravel')`.

**One change still needed**: `resources/views/components/app-logo.blade.php` hardcodes the brand name as `"Laravel Starter Kit"` in the `name` prop for `<flux:brand>` and `<flux:sidebar.brand>`. This must be replaced with `config('app.name')` so the sidebar brand matches.

---

### 3. Wilson SVG Logo Design

**Decision**: A clean geometric "W" monogram rendered as a single filled SVG path on a 40×40 viewBox, using `currentColor` so it inherits the parent element's text color.

**Design rationale**:
- The "W" letterform is immediately legible at 9px–36px rendered sizes (the range used across auth layouts and sidebar)
- Using `currentColor` for fill means a single SVG file handles both light mode (`text-black`) and dark mode (`text-white`) via the existing CSS classes already applied by the auth layouts
- A standalone lettermark (no container box) matches the sizing approach used by the existing Laravel icon
- The geometric construction uses clean straight-line paths (no curves), keeping the file small and rendering crisp at all sizes

**SVG path construction (40×40 viewBox)**:

The W is traced as a single closed filled path. Outer vertices form the classic W silhouette; inner vertices define the stroke thickness (~7 units):

```
M4,4 L13,36 L20,18 L27,36 L36,4 L30,4 L24,30 L20,23 L16,30 L10,4 Z
```

Key coordinates:
- `(4,4)` → `(13,36)` — left outer leg, angled down-right
- `(13,36)` → `(20,18)` — left inner V, angling up to center peak
- `(20,18)` → `(27,36)` — right inner V, angling down to valley
- `(27,36)` → `(36,4)` — right outer leg, angling up-right
- Then inner contour back: `(30,4)` → `(24,30)` → `(20,23)` → `(16,30)` → `(10,4)`

**Alternatives considered**:
- Rounded-rectangle container with W inside: rejected as more complex and the container doesn't scale as cleanly at 9px sizes
- House/roof motif: rejected as too illustrative; lettermark is more versatile
- Using a `<text>` SVG element: rejected because font rendering requires an embedded font or system font availability

---

### 4. Test Strategy

**Decision**: One Pest feature test file (`tests/Feature/WilsonLogoTest.php`) with two tests:
1. Login page renders the Wilson W SVG path (assert response contains a distinctive portion of the SVG path string)
2. Register page renders the Wilson W SVG path

**Rationale**: The simplest meaningful assertion is that the SVG `d` attribute path string (or a distinctive substring) is present in the rendered HTML. This confirms the new logo is actually served without coupling the test to visual rendering.

**Alternatives considered**:
- Playwright E2E screenshot comparison: overkill for a static SVG swap; adds brittle image comparison
- Asserting `config('app.name')` in response: already covered by existing auth tests in the codebase

---

## Resolved Unknowns

| Unknown | Resolution |
|---------|------------|
| Which files reference the logo? | `app-logo-icon.blade.php` (SVG) and `app-logo.blade.php` (name). Auth layouts reference both automatically. |
| Is the app name already "Wilson"? | Yes — `APP_NAME=Wilson` in `.env`. Only the `app-logo.blade.php` hardcoded string needs updating. |
| What SVG design? | Geometric "W" monogram, `currentColor` fill, 40×40 viewBox, single closed path. |
| Do any auth pages use a different logo path? | No — all three layout variants (`card`, `simple`, `split`) use `<x-app-logo-icon>`. |
