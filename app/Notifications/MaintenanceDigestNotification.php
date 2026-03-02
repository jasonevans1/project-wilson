<?php

namespace App\Notifications;

use App\Models\MaintenanceReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class MaintenanceDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param Collection<int, MaintenanceReminder> $reminders */
    public function __construct(public readonly Collection $reminders) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->reminders->count();

        return (new MailMessage)
            ->subject("Wilson: You have {$count} upcoming maintenance tasks")
            ->markdown('mail.maintenance-digest', [
                'userName' => $notifiable->name,
                'reminders' => $this->reminders,
                'reminderCount' => $count,
                'taskUrl' => route('maintenance.schedule'),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
