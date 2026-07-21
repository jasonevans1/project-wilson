# Code Standards

## Style Guide
Laravel Pint default preset (PSR-12 based), configured via `pint.json` if present, otherwise Pint's defaults.

## Linting
```bash
# Check for issues (no changes made)
vendor/bin/pint --test --format agent

# Auto-fix issues
vendor/bin/pint --dirty --format agent
```

Always use `--dirty` to fix only changed files before finalizing a change, per project convention (see CLAUDE.md Pint rules).

## Pre-commit Checks
- Run `vendor/bin/pint --dirty --format agent` on any modified PHP files.
- All tests must pass (`php artisan test --compact`).

## Naming Conventions
- Classes: `PascalCase`
- Methods/variables: `camelCase`, descriptive (e.g. `isRegisteredForDiscounts`, not `discount()`)
- Enum cases: `TitleCase` (e.g. `FavoritePerson`, `Monthly`)
- Constants: `SCREAMING_SNAKE_CASE`
- Files: one class per file, PSR-4 autoloaded under `App\`

## PHP Conventions
- Curly braces required for all control structures, even single-line bodies.
- Constructor property promotion for all `__construct()` methods.
- Explicit return types and parameter type hints on all methods/functions.
- Prefer PHPDoc blocks over inline comments; only comment when logic is exceptionally complex.
- Casts defined via a `casts()` method on models, not the `$casts` property.
