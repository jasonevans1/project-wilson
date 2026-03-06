<?php

namespace App\Notifications;

use App\Enums\ReplacementAlertType;
use App\Models\Asset;
use App\Models\AssetReplacementAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ReplacementAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Asset $asset,
        public readonly ReplacementAlertType $alertType,
        public readonly AssetReplacementAlert $alert,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->alertType) {
            ReplacementAlertType::TwoYear => "{$this->asset->name} — Replacement Due in ~2 Years",
            ReplacementAlertType::OneYear => "{$this->asset->name} — Replacement Due Soon",
            ReplacementAlertType::Overdue => "{$this->asset->name} — Past Expected Replacement Date",
        };

        $replacementDate = $this->asset->install_date->copy()->addYears($this->asset->expected_lifespan_years);
        $daysRemaining = (int) now()->diffInDays($replacementDate, false);
        $yearsRemaining = round(abs($daysRemaining) / 365, 1);

        $dashboardUrl = route('replacement-tracking');

        $mail = (new MailMessage)
            ->subject($subject)
            ->markdown('mail.replacement-alert', [
                'asset' => $this->asset,
                'alertType' => $this->alertType,
                'replacementDate' => $replacementDate,
                'daysRemaining' => $daysRemaining,
                'yearsRemaining' => $yearsRemaining,
                'dashboardUrl' => $dashboardUrl,
                'dismissUrl' => $this->alertType === ReplacementAlertType::Overdue
                    ? URL::signedRoute('replacement.alert.dismiss', ['alert' => $this->alert->id], now()->addDays(30))
                    : null,
            ]);

        return $mail;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
