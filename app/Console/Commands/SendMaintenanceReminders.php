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
     * Collect all pending reminders that need to be sent today.
     *
     * @return Collection<int, MaintenanceReminder>
     */
    protected function collectPendingReminders(): Collection
    {
        $today = today();
        $pendingReminders = collect();

        $occurrences = MaintenanceOccurrence::query()
            ->with(['task.user', 'task.asset'])
            ->whereNull('completed_at')
            ->where('due_date', '<=', $today->copy()->addDays(ReminderType::ThirtyDay->daysBeforeDue()))
            ->whereHas('task', fn ($query) => $query
                ->where('is_active', true)
                ->whereHas('user', fn ($query) => $query->whereNotNull('email_verified_at'))
            )
            ->get();

        foreach ($occurrences as $occurrence) {
            $user = $occurrence->task->user;

            $alreadySent = MaintenanceReminder::query()
                ->where('maintenance_occurrence_id', $occurrence->id)
                ->where('reminder_type', ReminderType::ThirtyDay)
                ->whereNotNull('sent_at')
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $reminder = MaintenanceReminder::firstOrCreate(
                [
                    'maintenance_occurrence_id' => $occurrence->id,
                    'reminder_type' => ReminderType::ThirtyDay,
                ],
                [
                    'user_id' => $user->id,
                    'snooze_count' => 0,
                ]
            );

            $reminder->setRelation('occurrence', $occurrence);

            $pendingReminders->push($reminder);
        }

        return $pendingReminders;
    }
}
