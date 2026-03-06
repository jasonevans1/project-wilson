<?php

namespace App\Livewire\ReplacementTracking;

use App\Models\Asset;
use App\Models\AssetReplacementAlert;
use App\Models\AssetReplacementEvent;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Prop;
use Livewire\Component;

class RecordReplacementForm extends Component
{
    #[Prop]
    public Asset $asset;

    public string $installedAt = '';

    public ?string $cost = null;

    public ?string $notes = null;

    public ?int $expectedLifespanYears = null;

    public function mount(Asset $asset): void
    {
        $this->expectedLifespanYears = $asset->expected_lifespan_years;
    }

    public function save(): void
    {
        abort_if($this->asset->user_id !== Auth::id(), 403);

        $this->validate([
            'installedAt' => ['required', 'date', 'before_or_equal:today'],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'expectedLifespanYears' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        AssetReplacementEvent::create([
            'asset_id' => $this->asset->id,
            'installed_at' => $this->installedAt,
            'cost' => $this->cost ?: null,
            'notes' => $this->notes ?: null,
            'expected_lifespan_years' => $this->expectedLifespanYears,
        ]);

        $this->asset->update([
            'install_date' => $this->installedAt,
            'expected_lifespan_years' => $this->expectedLifespanYears ?? $this->asset->expected_lifespan_years,
        ]);

        AssetReplacementAlert::where('asset_id', $this->asset->id)->delete();

        $this->dispatch('replacement-recorded');
    }

    public function cancel(): void
    {
        $this->dispatch('close-record-form');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.replacement-tracking.record-replacement-form');
    }
}
