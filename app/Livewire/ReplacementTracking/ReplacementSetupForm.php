<?php

namespace App\Livewire\ReplacementTracking;

use App\Models\Asset;
use App\Services\AssetLifespanDefaults;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Prop;
use Livewire\Component;

class ReplacementSetupForm extends Component
{
    #[Prop]
    public Asset $asset;

    public ?int $expectedLifespanYears = null;

    public ?string $installDate = null;

    public function mount(Asset $asset): void
    {
        $this->expectedLifespanYears = $asset->expected_lifespan_years
            ?? AssetLifespanDefaults::forCategory($asset->category);
        $this->installDate = $asset->install_date?->format('Y-m-d');
    }

    public function save(): void
    {
        abort_if($this->asset->user_id !== Auth::id(), 403);

        $this->validate([
            'expectedLifespanYears' => ['required', 'integer', 'min:1', 'max:100'],
            'installDate' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $this->asset->update([
            'expected_lifespan_years' => $this->expectedLifespanYears,
            'install_date' => $this->installDate,
        ]);

        $this->dispatch('tracking-configured');
    }

    public function cancel(): void
    {
        $this->dispatch('close-setup-form');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.replacement-tracking.replacement-setup-form');
    }
}
