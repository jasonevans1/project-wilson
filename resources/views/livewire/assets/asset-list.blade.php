<section class="max-w-2xl mx-auto px-4 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Assets') }}</flux:heading>

        <div class="flex items-center gap-4">
            <flux:switch wire:model.live="showArchived" :label="__('Show Archived')" />
            <flux:button variant="primary" wire:click="openCreateForm">
                {{ __('Add Asset') }}
            </flux:button>
        </div>
    </div>

    {{-- Create form panel --}}
    @if ($showCreateForm)
        <livewire:assets.asset-form />
    {{-- Detail panel --}}
    @elseif ($selectedAssetId)
        <livewire:assets.asset-detail :asset="$this->assets->first(fn ($a) => $a->id === $selectedAssetId) ?? \Illuminate\Support\Facades\Auth::user()->assets()->findOrFail($selectedAssetId)" />
    {{-- Empty state --}}
    @elseif ($this->assets->isEmpty())
        <div class="text-center py-16">
            <flux:text class="text-lg">{{ __('No assets yet.') }}</flux:text>
            <flux:text class="mt-2">{{ __('Click "Add Asset" to add your first home asset.') }}</flux:text>
        </div>
    {{-- Asset list --}}
    @else
        <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
            @foreach ($this->assets as $asset)
                <button
                    type="button"
                    wire:click="selectAsset({{ $asset->id }})"
                    class="w-full text-left px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors"
                >
                    <div class="flex items-center justify-between">
                        <flux:text class="font-medium">{{ $asset->name }}</flux:text>
                        @if ($asset->status === \App\Enums\AssetStatus::Archived)
                            <flux:badge color="zinc">{{ __('Archived') }}</flux:badge>
                        @endif
                    </div>
                    <flux:text size="sm" class="mt-0.5">
                        {{ $asset->category->label() }} · {{ $asset->location }}
                    </flux:text>
                </button>
            @endforeach
        </div>

        {{ $this->assets->links() }}
    @endif
</section>
