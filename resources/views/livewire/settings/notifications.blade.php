<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Notification Settings') }}</flux:heading>

    <x-settings.layout :heading="__('Notifications')" :subheading="__('Manage your email notification preferences')">
        <form wire:submit="updateNotificationPreferences" class="my-6 w-full space-y-6">
            <div>
                <flux:switch
                    wire:model="maintenanceRemindersEnabled"
                    :label="__('Maintenance reminder emails')"
                />
                <flux:text class="mt-2">
                    {{ __('Receive email reminders for upcoming scheduled maintenance tasks.') }}
                </flux:text>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="notification-preferences-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
