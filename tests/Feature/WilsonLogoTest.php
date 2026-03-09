<?php

it('displays the Wilson W logo on the login page', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('M20,2 L38,18', false);
});

it('displays the Wilson W logo on the register page', function () {
    $this->get(route('register'))
        ->assertSuccessful()
        ->assertSee('M20,2 L38,18', false);
});

it('displays the Wilson W logo on the forgot password page', function () {
    $this->get(route('password.request'))
        ->assertSuccessful()
        ->assertSee('M20,2 L38,18', false);
});
