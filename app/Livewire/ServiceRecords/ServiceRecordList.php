<?php

namespace App\Livewire\ServiceRecords;

use App\Concerns\ServiceRecordValidationRules;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Prop;
use Livewire\Component;

class ServiceRecordList extends Component
{
    use ServiceRecordValidationRules;

    #[Prop]
    public Asset $asset;

    public bool $showForm = false;

    public string $serviceDate = '';

    public string $serviceType = '';

    public string $description = '';

    public string $providerName = '';

    public ?string $cost = null;

    public bool $underWarranty = false;

    public ?string $warrantyExpiresOn = null;

    public function mount(): void
    {
        abort_if($this->asset->user_id !== Auth::id(), 403);
    }

    #[Computed]
    public function records(): Collection
    {
        return $this->asset->serviceRecords()->orderBy('service_date', 'desc')->get();
    }

    public function showCreateForm(): void
    {
        $this->serviceDate = '';
        $this->serviceType = '';
        $this->description = '';
        $this->providerName = '';
        $this->cost = null;
        $this->underWarranty = false;
        $this->warrantyExpiresOn = null;
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
        $this->serviceDate = '';
        $this->serviceType = '';
        $this->description = '';
        $this->providerName = '';
        $this->cost = null;
        $this->underWarranty = false;
        $this->warrantyExpiresOn = null;
    }

    public function saveRecord(): void
    {
        $this->validate($this->serviceRecordRules());

        Auth::user()->serviceRecords()->create([
            'asset_id' => $this->asset->id,
            'service_date' => $this->serviceDate,
            'service_type' => $this->serviceType,
            'description' => $this->description,
            'provider_name' => $this->providerName ?: null,
            'cost' => $this->cost,
            'under_warranty' => $this->underWarranty,
            'warranty_expires_on' => $this->warrantyExpiresOn,
        ]);

        $this->showForm = false;
        $this->serviceDate = '';
        $this->serviceType = '';
        $this->description = '';
        $this->providerName = '';
        $this->cost = null;
        $this->underWarranty = false;
        $this->warrantyExpiresOn = null;
        unset($this->records);
    }
}
