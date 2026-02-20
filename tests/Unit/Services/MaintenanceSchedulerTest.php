<?php

use App\Enums\RecurrenceUnit;
use App\Services\MaintenanceScheduler;
use Carbon\Carbon;

it('calculates the next due date for daily recurrence', function () {
    $scheduler = new MaintenanceScheduler;
    $base = Carbon::parse('2026-02-01');

    $next = $scheduler->nextDueDate($base, RecurrenceUnit::Daily, 1);

    expect($next->toDateString())->toBe('2026-02-02');
});

it('calculates the next due date with a daily multiplier', function () {
    $scheduler = new MaintenanceScheduler;
    $base = Carbon::parse('2026-02-01');

    $next = $scheduler->nextDueDate($base, RecurrenceUnit::Daily, 7);

    expect($next->toDateString())->toBe('2026-02-08');
});

it('calculates the next due date for weekly recurrence', function () {
    $scheduler = new MaintenanceScheduler;
    $base = Carbon::parse('2026-02-01');

    $next = $scheduler->nextDueDate($base, RecurrenceUnit::Weekly, 1);

    expect($next->toDateString())->toBe('2026-02-08');
});

it('calculates the next due date for weekly recurrence with a multiplier', function () {
    $scheduler = new MaintenanceScheduler;
    $base = Carbon::parse('2026-02-01');

    $next = $scheduler->nextDueDate($base, RecurrenceUnit::Weekly, 2);

    expect($next->toDateString())->toBe('2026-02-15');
});

it('calculates the next due date for monthly recurrence', function () {
    $scheduler = new MaintenanceScheduler;
    $base = Carbon::parse('2026-01-15');

    $next = $scheduler->nextDueDate($base, RecurrenceUnit::Monthly, 1);

    expect($next->toDateString())->toBe('2026-02-15');
});

it('calculates the next due date for monthly recurrence with a multiplier', function () {
    $scheduler = new MaintenanceScheduler;
    $base = Carbon::parse('2026-01-15');

    $next = $scheduler->nextDueDate($base, RecurrenceUnit::Monthly, 3);

    expect($next->toDateString())->toBe('2026-04-15');
});

it('handles month-end overflow without drifting past the month', function () {
    $scheduler = new MaintenanceScheduler;
    $base = Carbon::parse('2026-01-31');

    $next = $scheduler->nextDueDate($base, RecurrenceUnit::Monthly, 1);

    expect($next->toDateString())->toBe('2026-02-28');
});

it('calculates the next due date for yearly recurrence', function () {
    $scheduler = new MaintenanceScheduler;
    $base = Carbon::parse('2026-02-19');

    $next = $scheduler->nextDueDate($base, RecurrenceUnit::Yearly, 1);

    expect($next->toDateString())->toBe('2027-02-19');
});

it('handles leap year overflow for yearly recurrence', function () {
    $scheduler = new MaintenanceScheduler;
    $base = Carbon::parse('2024-02-29');

    $next = $scheduler->nextDueDate($base, RecurrenceUnit::Yearly, 1);

    expect($next->toDateString())->toBe('2025-02-28');
});

it('does not mutate the original base date', function () {
    $scheduler = new MaintenanceScheduler;
    $base = Carbon::parse('2026-01-15');
    $originalDate = $base->toDateString();

    $scheduler->nextDueDate($base, RecurrenceUnit::Monthly, 1);

    expect($base->toDateString())->toBe($originalDate);
});
