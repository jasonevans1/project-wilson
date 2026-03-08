<?php

use App\Http\Controllers\ReplacementAlertDismissController;
use App\Livewire\ReplacementTracking\ReplacementDashboard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('replacement-tracking', ReplacementDashboard::class)->name('replacement-tracking');
});

Route::middleware(['signed'])->group(function () {
    Route::get('replacement-tracking/alerts/{alert}/dismiss', ReplacementAlertDismissController::class)
        ->name('replacement.alert.dismiss');
});
