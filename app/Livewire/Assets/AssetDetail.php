<?php

namespace App\Livewire\Assets;

use App\Enums\AssetStatus;
use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Prop;
use Livewire\Component;

class AssetDetail extends Component
{
    #[Prop]
    public Asset $asset;

    public bool $confirmingArchive = false;

    public bool $editMode = false;

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
