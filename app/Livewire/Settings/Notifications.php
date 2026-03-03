<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Notifications extends Component
{
    public bool $maintenanceRemindersEnabled = true;

    public function mount(): void
    {
        $this->maintenanceRemindersEnabled = Auth::user()->maintenance_reminders_enabled;
    }

    public function updateNotificationPreferences(): void
    {
        Auth::user()->update([
            'maintenance_reminders_enabled' => $this->maintenanceRemindersEnabled,
        ]);

        $this->dispatch('notification-preferences-updated');
    }
}
