<?php

namespace App\Livewire\Assets;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AssetList extends Component
{
    public bool $showArchived = false;

    public ?int $selectedAssetId = null;

    public bool $showCreateForm = false;

    /**
     * Open the create form and clear any selected asset.
     */
    public function openCreateForm(): void
    {
        $this->showCreateForm = true;
        $this->selectedAssetId = null;
    }

    /**
     * Close the active panel (create form or detail).
     */
    public function closePanel(): void
    {
        $this->showCreateForm = false;
        $this->selectedAssetId = null;
    }

    /**
     * Select an asset to view its detail.
     */
    public function selectAsset(int $id): void
    {
        $this->selectedAssetId = $id;
        $this->showCreateForm = false;
    }

    /**
     * Toggle between active and archived asset views.
     */
    public function toggleArchived(): void
    {
        $this->showArchived = ! $this->showArchived;
        $this->selectedAssetId = null;
        $this->showCreateForm = false;
    }

    /**
     * Get the paginated list of assets filtered by status.
     */
    #[Computed]
    public function assets(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $status = $this->showArchived
            ? \App\Enums\AssetStatus::Archived
            : \App\Enums\AssetStatus::Active;

        return Auth::user()->assets()
            ->where('status', $status)
            ->latest()
            ->paginate(15);
    }
}
