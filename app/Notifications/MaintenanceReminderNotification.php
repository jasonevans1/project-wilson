<?php

namespace App\Notifications;

use App\Enums\ReminderType;
use App\Models\MaintenanceReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly MaintenanceReminder $reminder) {}

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
        $occurrence = $this->reminder->occurrence;
        $task = $occurrence->task;
        $asset = $task->asset;
        $reminderType = $this->reminder->reminder_type;

        $subject = match ($reminderType) {
            ReminderType::ThirtyDay => "Upcoming maintenance: {$task->name} for {$asset->name}",
            ReminderType::SevenDay => "Reminder: {$task->name} for {$asset->name} due in 7 days",
            ReminderType::OneDay => "Urgent: {$task->name} for {$asset->name} due tomorrow",
        };

        return (new MailMessage)
            ->subject($subject)
            ->markdown('mail.maintenance-reminder', [
                'userName' => $notifiable->name,
                'assetName' => $asset->name,
                'taskName' => $task->name,
                'taskDescription' => $task->description,
                'dueDate' => $occurrence->due_date,
                'reminderType' => $reminderType,
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
