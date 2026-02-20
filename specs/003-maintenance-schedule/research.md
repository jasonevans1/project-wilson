# Research: Maintenance Schedule & Task Management

**Branch**: `003-maintenance-schedule` | **Date**: 2026-02-19

All NEEDS CLARIFICATION items from the Technical Context are resolved below.

---

## 1. Carbon Date Arithmetic — Unit + Count Recurrence

**Decision**: Use Carbon's `addDays()`, `addWeeks()`, `addMonthsNoOverflow()`, and `addYearsNoOverflow()` methods with the integer `recurrence_count` multiplier.

**Rationale**: Carbon 3 (shipped with Laravel 12) maps cleanly to the four `RecurrenceUnit` values. The critical choice is `addMonthsNoOverflow()` over `addMonths()` — the latter overflows (e.g., Jan 31 + 1 month = Mar 3 on a 28-day February); `NoOverflow` clamps to the last valid day of the target month. Same concern applies for `addYearsNoOverflow()` with Feb 29 on non-leap years.

| RecurrenceUnit | Carbon method |
|---|---|
| `Daily` | `$date->addDays($count)` |
| `Weekly` | `$date->addWeeks($count)` |
| `Monthly` | `$date->addMonthsNoOverflow($count)` |
| `Yearly` | `$date->addYearsNoOverflow($count)` |

The base date for next-occurrence calculation is always the prior `due_date` (the scheduled date), not `completed_at`. This prevents schedule drift when tasks are completed early or late.

**Alternatives considered**: Storing a `CarbonInterval` string — more flexible but harder to validate, display, and describe to users in plain language.

---

## 2. "One Active Occurrence at a Time" Service Pattern

**Decision**: `MaintenanceScheduler` service class with a `completeOccurrence()` method that wraps marking complete + generating the next occurrence in a single `DB::transaction()`.

**Rationale**: Atomicity is critical — if generating the next occurrence fails after marking the current one complete, the task would have no pending occurrence and fall off the schedule. Wrapping both in a transaction prevents this orphan state. The service is injected into Livewire component actions via the method signature (Laravel's automatic DI).

```php
// Pseudocode — not implementation:
public function completeOccurrence(MaintenanceOccurrence $occurrence): MaintenanceOccurrence
{
    return DB::transaction(function () use ($occurrence) {
        $occurrence->update(['completed_at' => now()]);
        return $this->generateNextOccurrence(
            $occurrence->task,
            $occurrence->due_date  // always based on due_date, not now()
        );
    });
}
```

**Alternatives considered**: Eloquent model observer — harder to test in isolation and hides business logic in implicit lifecycle hooks, making the flow non-obvious when reading component code.

---

## 3. Soft Deactivation Pattern

**Decision**: Local scope using the `#[Scope]` attribute in Laravel 12 — NOT a global scope.

**Rationale**: A global scope silently filters every query, risking accidental omission of inactive tasks where they are legitimately needed (e.g., task history views that show deactivated tasks). A local scope makes the filtering intent explicit at every call site: `$user->maintenanceTasks()->active()->get()`.

Laravel 12 uses the attribute-based scope syntax:
```php
#[Scope]
protected function active(Builder $query): void
{
    $query->where('is_active', true);
}
```

History views that need to show completed occurrences of deactivated tasks query through the task's `occurrences()` relationship without the `active` scope, preserving full history visibility.

**Alternatives considered**: Global scope — appropriate only if inactive records should never appear in any query (analogous to soft-deletes). For a feature where history must remain visible, a local scope is safer.

---

## 4. Livewire 4 Inline "Mark as Complete" Action

**Decision**: `wire:click="completeOccurrence({{ $occurrence->id }})"` on a button in the list row, with `wire:key` on each row element and `wire:loading wire:target` scoped to each individual occurrence ID for per-row loading feedback.

**Rationale**: Livewire 4 supports parameterized `wire:target` scoping, so each row's loading indicator only activates for its own action — not all rows simultaneously. The component action calls `MaintenanceScheduler::completeOccurrence()`, and the `#[Computed]` occurrences property is automatically re-evaluated on the next render, updating only the changed rows reactively.

```blade
{{-- Pseudocode — not final implementation --}}
@foreach ($this->occurrences as $occurrence)
    <div wire:key="occurrence-{{ $occurrence->id }}">
        <flux:button wire:click="completeOccurrence({{ $occurrence->id }})">
            Mark Complete
        </flux:button>
        <flux:badge wire:loading wire:target="completeOccurrence({{ $occurrence->id }})">
            Saving…
        </flux:badge>
    </div>
@endforeach
```

**Alternatives considered**: `wire:model` checkbox — introduces unintended two-way binding and is semantically wrong for a side-effect operation; a dedicated action method is cleaner.

---

## 5. Pest 4 Feature Test Pattern for Livewire Actions

**Decision**: `Livewire::test()` with `->call()` for triggering component actions, combined with Pest `expect($model->fresh()->property)` for database assertions.

**Rationale**: The Livewire testing API's `call()` method invokes component actions exactly as the browser would, including validation. `expect($model->fresh())` is idiomatic Pest 4 style and reads clearly. `actingAs()` handles authentication context.

```php
// Representative pattern — not exhaustive:
uses(RefreshDatabase::class);

it('marks an occurrence complete and generates the next one', function () {
    $user = User::factory()->create();
    $task = MaintenanceTask::factory()->for($user)->create();
    $occurrence = MaintenanceOccurrence::factory()->for($task)->pending()->create();

    Livewire::actingAs($user)
        ->test(MaintenanceSchedule::class)
        ->call('completeOccurrence', $occurrence->id)
        ->assertHasNoErrors();

    expect($occurrence->fresh()->completed_at)->not->toBeNull();
    expect(
        MaintenanceOccurrence::where('maintenance_task_id', $task->id)
            ->whereNull('completed_at')
            ->count()
    )->toBe(1);
});
```

Use `->assertHasNoErrors()` to catch validation failures and `->assertDispatched()` if the action emits events. Pure date-arithmetic logic in `MaintenanceScheduler` is covered by fast unit tests without DB access.

**Alternatives considered**: `assertDatabaseHas()` — functional but less expressive than Pest's `expect()` chain for complex assertions.

---

## Summary of Resolved Decisions

| Topic | Decision |
|---|---|
| Monthly/yearly overflow | `addMonthsNoOverflow()` / `addYearsNoOverflow()` |
| Completion + next generation | Single `DB::transaction()` in `MaintenanceScheduler` |
| Soft deactivation | Local `#[Scope]` attribute; no global scope |
| Inline completion UX | `wire:click` + per-row `wire:loading wire:target` |
| Test pattern | `Livewire::test()->call()` + `expect($model->fresh())` |
