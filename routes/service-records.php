<?php

use App\Livewire\ServiceRecords\ServiceRecordList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('assets/{asset}/service-records', ServiceRecordList::class)->name('service-records.index');
});
