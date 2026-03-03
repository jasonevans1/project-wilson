<?php

use App\Livewire\Settings\Notifications;
use App\Models\User;
use Livewire\Livewire;

test('notifications settings page is displayed', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/settings/notifications')->assertOk();
});

test('maintenance reminders can be disabled', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Notifications::class)
        ->set('maintenanceRemindersEnabled', false)
        ->call('updateNotificationPreferences')
        ->assertHasNoErrors();

    expect($user->refresh()->maintenance_reminders_enabled)->toBeFalse();
});

test('maintenance reminders can be re-enabled', function () {
    $user = User::factory()->withRemindersDisabled()->create();
    $this->actingAs($user);

    Livewire::test(Notifications::class)
        ->set('maintenanceRemindersEnabled', true)
        ->call('updateNotificationPreferences')
        ->assertHasNoErrors();

    expect($user->refresh()->maintenance_reminders_enabled)->toBeTrue();
});

test('notifications page requires authentication', function () {
    $this->get('/settings/notifications')->assertRedirect('/login');
});
