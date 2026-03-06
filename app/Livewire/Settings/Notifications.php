<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Notifications extends Component
{
    public bool $maintenanceRemindersEnabled = true;

    public bool $replacementAlertsEnabled = true;

    public function mount(): void
    {
        $this->maintenanceRemindersEnabled = Auth::user()->maintenance_reminders_enabled;
        $this->replacementAlertsEnabled = Auth::user()->replacement_alerts_enabled;
    }

    public function updateNotificationPreferences(): void
    {
        Auth::user()->update([
            'maintenance_reminders_enabled' => $this->maintenanceRemindersEnabled,
            'replacement_alerts_enabled' => $this->replacementAlertsEnabled,
        ]);

        $this->dispatch('notification-preferences-updated');
    }
}
