# Testing Configuration

## Test Framework
Pest 4 (`pestphp/pest`) with `pestphp/pest-plugin-laravel`

## TDD Methodology

Each task follows strict Red → Green → Refactor:

1. Write failing test for one requirement
2. Write minimum code to pass
3. Refactor while tests stay green
4. Repeat for next requirement
5. Commit when task complete

## Commands

Run inside the DDEV web container (`ddev exec ...`) or via `php artisan` directly if already inside the container/sandbox.

```bash
# Run all tests
php artisan test --compact

# Run a specific test file or filter
php artisan test --compact --filter=testName
php artisan test --compact tests/Feature/Path/To/FileTest.php

# Run with coverage
php artisan test --coverage
```

## Parallel Execution
- No parallel test runner installed (`brianium/paratest` is not a dependency) — tests run sequentially.
- Add `paratest` and use `php artisan test --parallel` if suite speed becomes an issue.

## Test File Locations
- Unit tests: `tests/Unit/` (mirrors `app/` structure, e.g. `tests/Unit/Services`, `tests/Unit/Enums`)
- Feature tests: `tests/Feature/` (grouped by feature area, e.g. `tests/Feature/Maintenance`, `tests/Feature/ServiceRecords`, `tests/Feature/Auth`)

## Coverage Requirements
- New code must have tests; no fixed minimum percentage enforced.

## Test Naming Convention
- Test files: `{Feature}Test.php` (Pest still uses PHPUnit test class file naming)
- Test cases: Pest functional style — `it('does something', function () { ... })` / `test('does something', ...)`
