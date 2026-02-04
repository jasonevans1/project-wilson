<?php

namespace App\Livewire\Assets;

use App\Concerns\AssetValidationRules;
use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Prop;
use Livewire\Component;

class AssetForm extends Component
{
    use AssetValidationRules;

    #[Prop(optional: true)]
    public ?Asset $asset = null;

    public string $name = '';

    public string $category = '';

    public string $location = '';

    public ?string $purchaseDate = null;

    public ?string $installDate = null;

    public ?string $warrantyExpirationDate = null;

    public ?string $notes = null;

    /**
     * Mount the component, pre-populating fields if editing an existing asset.
     */
    public function mount(): void
    {
        if ($this->asset) {
            $this->name = $this->asset->name;
            $this->category = $this->asset->category->value;
            $this->location = $this->asset->location;
            $this->purchaseDate = $this->asset->purchase_date?->format('Y-m-d');
            $this->installDate = $this->asset->install_date?->format('Y-m-d');
            $this->warrantyExpirationDate = $this->asset->warranty_expiration_date?->format('Y-m-d');
            $this->notes = $this->asset->notes;
        }
    }

    /**
     * Validate and persist the asset, dispatching the appropriate event.
     */
    public function save(): void
    {
        $validated = $this->validate($this->assetRules());

        $data = [
            'name' => $validated['name'],
            'category' => $validated['category'],
            'location' => $validated['location'],
            'purchase_date' => $validated['purchaseDate'] ?? null,
            'install_date' => $validated['installDate'] ?? null,
            'warranty_expiration_date' => $validated['warrantyExpirationDate'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        if ($this->asset) {
            Auth::user()->assets()->findOrFail($this->asset->id)->update($data);
            $this->dispatch('asset-updated');
        } else {
            Auth::user()->assets()->create($data);
            $this->dispatch('asset-created');
        }
    }

    /**
     * Cancel the form and notify the parent to close the panel.
     */
    public function cancel(): void
    {
        $this->dispatch('close-panel');
    }
}
