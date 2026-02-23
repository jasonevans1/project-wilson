<?php

use App\Livewire\Maintenance\MaintenanceSchedule;
use App\Livewire\Maintenance\MaintenanceTaskList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('maintenance', MaintenanceSchedule::class)->name('maintenance.schedule');
    Route::livewire('assets/{asset}/maintenance', MaintenanceTaskList::class)->name('maintenance.asset');
});
