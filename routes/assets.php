<?php

use App\Livewire\Assets\AssetList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('assets', AssetList::class)->name('assets.index');
});
