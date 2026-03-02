<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceReminder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReminderSnoozeController extends Controller
{
    /**
     * Snooze a reminder via a signed URL.
     */
    public function snooze(Request $request, MaintenanceReminder $reminder, int $days): RedirectResponse
    {
        if (! in_array($days, [3, 7], true)) {
            abort(422, 'Invalid snooze duration.');
        }

        $snoozedUntil = today()->addDays($days);
        $dueDate = $reminder->occurrence->due_date;

        if ($snoozedUntil->greaterThanOrEqualTo($dueDate)) {
            return redirect()->back()->withErrors(['snooze' => 'Cannot snooze past the due date.']);
        }

        $reminder->update([
            'snoozed_until' => $snoozedUntil,
            'snooze_count' => $reminder->snooze_count + 1,
            'sent_at' => null,
        ]);

        return redirect()->route('maintenance.reminder.snoozed')
            ->with('snoozed_until', $snoozedUntil->toDateString());
    }

    /**
     * Show the snooze confirmation page.
     */
    public function snoozed(Request $request): View
    {
        return view('reminders.snoozed', [
            'snoozedUntil' => session('snoozed_until'),
        ]);
    }
}
