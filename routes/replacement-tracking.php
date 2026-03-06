<?php

use App\Livewire\ReplacementTracking\ReplacementDashboard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('replacement-tracking', ReplacementDashboard::class)->name('replacement-tracking');
});
