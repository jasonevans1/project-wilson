<?php

namespace App\Livewire\ReplacementTracking;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ReplacementDashboard extends Component
{
    public ?int $setupAssetId = null;

    public ?int $recordingAssetId = null;

    public function openSetupForm(int $assetId): void
    {
        $this->setupAssetId = $assetId;
        $this->recordingAssetId = null;
    }

    public function closeSetupForm(): void
    {
        $this->setupAssetId = null;
    }

    public function openRecordForm(int $assetId): void
    {
        $this->recordingAssetId = $assetId;
        $this->setupAssetId = null;
    }

    public function closeRecordForm(): void
    {
        $this->recordingAssetId = null;
    }

    #[On('tracking-configured')]
    public function handleTrackingConfigured(): void
    {
        unset($this->trackedAssets, $this->untrackedAssets);
        $this->setupAssetId = null;
    }

    #[On('replacement-recorded')]
    public function handleReplacementRecorded(): void
    {
        unset($this->trackedAssets, $this->untrackedAssets);
        $this->recordingAssetId = null;
    }

    /**
     * @return Collection<int, \App\Models\Asset>
     */
    #[Computed]
    public function trackedAssets(): Collection
    {
        return Auth::user()
            ->assets()
            ->with(['replacementEvents' => fn ($q) => $q->latest('installed_at')->limit(1)])
            ->get()
            ->filter(fn ($a) => $a->install_date && $a->expected_lifespan_years)
            ->sortBy(function ($a) {
                $replacementDate = $a->install_date->copy()->addYears($a->expected_lifespan_years);

                return (int) now()->diffInDays($replacementDate, false);
            })
            ->values();
    }

    /**
     * @return Collection<int, \App\Models\Asset>
     */
    #[Computed]
    public function untrackedAssets(): Collection
    {
        return Auth::user()
            ->assets()
            ->get()
            ->filter(fn ($a) => ! $a->install_date || ! $a->expected_lifespan_years)
            ->sortBy('name')
            ->values();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.replacement-tracking.replacement-dashboard', [
            'trackedAssets' => $this->trackedAssets,
            'untrackedAssets' => $this->untrackedAssets,
        ])->layout('layouts.app');
    }
}
