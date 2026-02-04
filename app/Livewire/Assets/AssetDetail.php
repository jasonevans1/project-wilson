<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
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
}
