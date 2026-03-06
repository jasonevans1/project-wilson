<?php

namespace App\Livewire\Assets;

use App\Enums\AssetStatus;
use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Prop;
use Livewire\Component;

class AssetDetail extends Component
{
    #[Prop]
    public Asset $asset;

    public bool $confirmingArchive = false;

    public bool $editMode = false;

    public bool $showingReplacementSetup = false;

    public bool $showingRecordReplacement = false;

    /**
     * Open the replacement setup form.
     */
    public function openSetupForm(): void
    {
        $this->showingReplacementSetup = true;
        $this->showingRecordReplacement = false;
    }

    /**
     * Close the replacement setup form without saving.
     */
    #[On('close-setup-form')]
    public function handleCloseSetupForm(): void
    {
        $this->showingReplacementSetup = false;
    }

    /**
     * Refresh the asset and close the setup form after tracking is configured.
     */
    #[On('tracking-configured')]
    public function handleTrackingConfigured(): void
    {
        $this->asset->refresh();
        $this->showingReplacementSetup = false;
    }

    /**
     * Open the record replacement form.
     */
    public function openRecordForm(): void
    {
        $this->showingRecordReplacement = true;
        $this->showingReplacementSetup = false;
    }

    /**
     * Close the record replacement form without saving.
     */
    #[On('close-record-form')]
    public function handleCloseRecordForm(): void
    {
        $this->showingRecordReplacement = false;
    }

    /**
     * Refresh the asset and close the record form after replacement is recorded.
     */
    #[On('replacement-recorded')]
    public function handleReplacementRecorded(): void
    {
        $this->asset->refresh();
        $this->showingRecordReplacement = false;
    }

    /**
     * Close the detail panel and notify the parent.
     */
    public function closePanel(): void
    {
        $this->dispatch('close-panel');
    }

    /**
     * Enter edit mode, rendering the asset form pre-populated with current data.
     */
    public function startEdit(): void
    {
        $this->editMode = true;
    }

    /**
     * Exit edit mode without persisting changes.
     */
    public function cancelEdit(): void
    {
        $this->editMode = false;
    }

    /**
     * Refresh the asset from the database and return to read-only detail.
     */
    public function handleAssetUpdated(): void
    {
        $this->asset->refresh();
        $this->editMode = false;
    }

    /**
     * Show the archive confirmation modal.
     */
    public function initiateArchive(): void
    {
        $this->confirmingArchive = true;
    }

    /**
     * Archive the asset after ownership verification.
     */
    public function archive(): void
    {
        $asset = Auth::user()->assets()->findOrFail($this->asset->id);
        $asset->status = AssetStatus::Archived;
        $asset->save();

        $this->asset->refresh();
        $this->confirmingArchive = false;
        $this->dispatch('asset-archived');
    }

    /**
     * Dismiss the archive confirmation without persisting.
     */
    public function cancelArchive(): void
    {
        $this->confirmingArchive = false;
    }

    /**
     * Restore an archived asset back to active after ownership verification.
     */
    public function restore(): void
    {
        $asset = Auth::user()->assets()->findOrFail($this->asset->id);
        $asset->status = AssetStatus::Active;
        $asset->save();

        $this->asset->refresh();
        $this->dispatch('asset-restored');
    }
}
