<?php

namespace App\Console\Commands;

use App\Enums\ReminderType;
use App\Models\MaintenanceOccurrence;
use App\Models\MaintenanceReminder;
use App\Notifications\MaintenanceDigestNotification;
use App\Notifications\MaintenanceReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SendMaintenanceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for upcoming maintenance tasks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $pendingReminders = $this->collectPendingReminders();

        $pendingReminders->groupBy('user_id')->each(function (Collection $userReminders) {
            $user = $userReminders->first()->occurrence->task->user;

            if ($userReminders->count() > 1) {
                $user->notify(new MaintenanceDigestNotification($userReminders));
            } else {
                $user->notify(new MaintenanceReminderNotification($userReminders->first()));
            }

            $userReminders->each(fn ($reminder) => $reminder->update(['sent_at' => now()]));
        });

        return self::SUCCESS;
    }

    /**
     * Collect all pending reminders that need to be sent today across all reminder intervals,
     * including snoozed reminders whose snooze period has expired.
     *
     * @return Collection<int, MaintenanceReminder>
     */
    protected function collectPendingReminders(): Collection
    {
        $today = today();
        $pendingReminders = collect();

        // Part 1: new reminders for each interval
        foreach (ReminderType::cases() as $reminderType) {
            $occurrences = MaintenanceOccurrence::query()
                ->with(['task.user', 'task.asset'])
                ->whereNull('completed_at')
                ->where('due_date', '<=', $today->copy()->addDays($reminderType->daysBeforeDue()))
                ->whereHas('task', fn ($query) => $query
                    ->where('is_active', true)
                    ->whereHas('user', fn ($query) => $query
                        ->whereNotNull('email_verified_at')
                        ->where('maintenance_reminders_enabled', true)
                    )
                )
                ->get();

            foreach ($occurrences as $occurrence) {
                $user = $occurrence->task->user;

                // Skip if already sent
                $alreadySent = MaintenanceReminder::query()
                    ->where('maintenance_occurrence_id', $occurrence->id)
                    ->where('reminder_type', $reminderType)
                    ->whereNotNull('sent_at')
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                // Skip if currently snoozed (snooze has not expired yet, or expires today — Part 2 handles resend)
                $currentlySnoozed = MaintenanceReminder::query()
                    ->where('maintenance_occurrence_id', $occurrence->id)
                    ->where('reminder_type', $reminderType)
                    ->whereNotNull('snoozed_until')
                    ->where('snoozed_until', '>=', $today)
                    ->exists();

                if ($currentlySnoozed) {
                    continue;
                }

                $reminder = MaintenanceReminder::firstOrCreate(
                    [
                        'maintenance_occurrence_id' => $occurrence->id,
                        'reminder_type' => $reminderType,
                    ],
                    [
                        'user_id' => $user->id,
                        'snooze_count' => 0,
                    ]
                );

                $reminder->setRelation('occurrence', $occurrence);

                $pendingReminders->push($reminder);
            }
        }

        // Part 2: snoozed reminders whose snooze period has expired
        $snoozedReminders = MaintenanceReminder::query()
            ->with(['occurrence.task.user', 'occurrence.task.asset'])
            ->whereNull('sent_at')
            ->whereNotNull('snoozed_until')
            ->where('snoozed_until', '<=', $today)
            ->whereHas('occurrence', fn ($query) => $query
                ->whereNull('completed_at')
                ->whereHas('task', fn ($query) => $query
                    ->where('is_active', true)
                    ->whereHas('user', fn ($query) => $query
                        ->whereNotNull('email_verified_at')
                        ->where('maintenance_reminders_enabled', true)
                    )
                )
            )
            ->get()
            ->filter(fn ($reminder) => $reminder->snoozed_until->lt($reminder->occurrence->due_date));

        foreach ($snoozedReminders as $reminder) {
            // Avoid duplicates if the same reminder is already in the collection
            if (! $pendingReminders->contains('id', $reminder->id)) {
                $pendingReminders->push($reminder);
            }
        }

        return $pendingReminders;
    }
}
