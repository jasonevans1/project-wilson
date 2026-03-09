<?php

use App\Models\User;

it('shows the app name and description on the welcome page', function (): void {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee(config('app.name'))
        ->assertSee('Track and manage your home assets');
});

it('shows register and sign in buttons for guests', function (): void {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Register')
        ->assertSee('Sign In')
        ->assertDontSee('Go to Dashboard');
});

it('shows go to dashboard button for authenticated users', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertSuccessful()
        ->assertSee('Go to Dashboard')
        ->assertDontSee('Register')
        ->assertDontSee('Sign In');
});

it('does not show laravel branding on the welcome page', function (): void {
    $this->get('/')
        ->assertSuccessful()
        ->assertDontSee('laravel.com')
        ->assertDontSee('laracasts.com')
        ->assertDontSee("Let's get started");
});
