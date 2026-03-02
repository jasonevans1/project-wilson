<?php

use App\Http\Controllers\ReminderSnoozeController;
use Illuminate\Support\Facades\Route;

// Snooze routes — signed URLs, no authentication required
Route::get(
    '/maintenance/reminders/{reminder}/snooze/{days}',
    [ReminderSnoozeController::class, 'snooze']
)->middleware('signed')->name('maintenance.reminder.snooze');

Route::get(
    '/maintenance/reminders/snoozed',
    [ReminderSnoozeController::class, 'snoozed']
)->name('maintenance.reminder.snoozed');
