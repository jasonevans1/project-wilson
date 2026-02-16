<?php

namespace App\Livewire\Assets;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class AssetList extends Component
{
    use WithPagination;
    public bool $showArchived = false;

    public ?int $selectedAssetId = null;

    public bool $showCreateForm = false;

    public bool $showSuccess = false;

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
    #[On('close-panel')]
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
     * Toggle the archived filter and reset the panel state.
     */
    public function toggleArchived(): void
    {
        $this->showArchived = ! $this->showArchived;
        $this->handleToggleChanged();
    }

    /**
     * Handle the toggle change to clear selected asset and forms.
     */
    public function handleToggleChanged(): void
    {
        $this->selectedAssetId = null;
        $this->showCreateForm = false;
        $this->resetPage();
    }

    /**
     * Show the success banner when a new asset is created.
     */
    #[On('asset-created')]
    public function handleAssetCreated(): void
    {
        $this->showCreateForm = false;
        $this->showSuccess = true;
    }

    /**
     * Reset the selected asset when its status changes so the list refreshes.
     */
    #[On('asset-archived')]
    #[On('asset-restored')]
    public function handleAssetStatusChange(): void
    {
        $this->selectedAssetId = null;

        // If the current page is now empty, reset to page 1
        if ($this->assets()->isEmpty() && $this->assets()->currentPage() > 1) {
            $this->resetPage();
        }
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
