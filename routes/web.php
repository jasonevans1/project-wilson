<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/assets.php';
require __DIR__.'/maintenance.php';
require __DIR__.'/service-records.php';
require __DIR__.'/reminders.php';
require __DIR__.'/replacement-tracking.php';
