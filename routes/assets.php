<?php

use App\Livewire\Assets\AssetList;
use App\Livewire\Assets\TemplateLibrary;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('assets', AssetList::class)->name('assets.index');
    Route::livewire('assets/templates', TemplateLibrary::class)->name('assets.templates');
});
