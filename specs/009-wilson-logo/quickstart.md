# Quickstart: Wilson Brand Logo

**Branch**: `009-wilson-logo` | **Date**: 2026-03-08

## What You're Building

Swap the generic Laravel cube icon for a custom Wilson "W" SVG monogram on all authentication pages (login, register, forgot password, reset password, verify email, 2FA) and the application sidebar. Two Blade component files change; everything else inherits the update automatically.

## Files to Change

| File | Change |
|------|--------|
| `resources/views/components/app-logo-icon.blade.php` | Replace existing SVG with Wilson W monogram |
| `resources/views/components/app-logo.blade.php` | Replace hardcoded `"Laravel Starter Kit"` with `config('app.name')` |
| `tests/Feature/WilsonLogoTest.php` | New Pest feature test (create via Artisan) |

## Implementation Steps

### Step 1: Write the Failing Test First

```bash
ddev exec --raw php artisan make:test --pest --no-interaction WilsonLogoTest
```

Open `tests/Feature/WilsonLogoTest.php` and write tests that assert:
- The login page response contains a distinctive portion of the new Wilson SVG path
- The register page response contains the same SVG marker

Run tests to confirm they **fail** (Red phase):
```bash
ddev exec --raw php artisan test --compact --filter=WilsonLogoTest
```

### Step 2: Replace the SVG in `app-logo-icon.blade.php`

Replace the entire file contents with the Wilson W monogram:

```blade
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" {{ $attributes }}>
    <path
        fill="currentColor"
        d="M4,4 L13,36 L20,18 L27,36 L36,4 L30,4 L24,30 L20,23 L16,30 L10,4 Z"
    />
</svg>
```

### Step 3: Fix the Brand Name in `app-logo.blade.php`

Replace the two hardcoded `name="Laravel Starter Kit"` strings with `{{ config('app.name') }}`:

- `<flux:sidebar.brand name="Laravel Starter Kit" ...>` → `<flux:sidebar.brand :name="config('app.name')" ...>`
- `<flux:brand name="Laravel Starter Kit" ...>` → `<flux:brand :name="config('app.name')" ...>`

### Step 4: Run Pint

```bash
ddev exec --raw vendor/bin/pint --dirty
```

### Step 5: Run Tests (Green phase)

```bash
ddev exec --raw php artisan test --compact --filter=WilsonLogoTest
```

All tests should now pass.

## Verification

1. Navigate to `/login` — Wilson W mark appears above the form, no cube icon visible
2. Navigate to `/register` — same W mark appears
3. Navigate to the authenticated dashboard — sidebar brand shows "Wilson" with the W mark
4. Toggle dark mode — logo color adapts correctly (white on dark, black on light)

## No Migrations Required

This feature has no database changes.
