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
}
