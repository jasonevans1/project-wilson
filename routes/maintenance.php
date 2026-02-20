<?php

use App\Livewire\Maintenance\MaintenanceTaskList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('assets/{asset}/maintenance', MaintenanceTaskList::class)->name('maintenance.asset');
});
