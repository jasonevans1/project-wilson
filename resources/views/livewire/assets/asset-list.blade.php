<section class="max-w-2xl mx-auto px-4 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Assets') }}</flux:heading>

        <div class="flex items-center gap-4">
            <flux:switch wire:model.live="showArchived" wire:change="handleToggleChanged" :label="__('Show Archived')" />
            <flux:button variant="ghost" href="{{ route('assets.templates') }}">
                {{ __('Add from Templates') }}
            </flux:button>
            <flux:button variant="primary" wire:click="openCreateForm">
                {{ __('Add Asset') }}
            </flux:button>
        </div>
    </div>

    {{-- Success banner --}}
    <div
        x-data="{ visible: $wire.showSuccess }"
        x-init="$watch('$wire.showSuccess', val => { if (val) { visible = true; setTimeout(() => { visible = false; $wire.showSuccess = false; }, 3000); } })"
        x-show="visible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/30 dark:border-green-700 dark:text-green-300"
    >
        {{ __('Asset added.') }}
    </div>

    {{-- Create form panel --}}
    @if ($showCreateForm)
        <livewire:assets.asset-form />
    {{-- Detail panel --}}
    @elseif ($selectedAssetId)
        <livewire:assets.asset-detail :asset="$this->assets->first(fn ($a) => $a->id === $selectedAssetId) ?? \Illuminate\Support\Facades\Auth::user()->assets()->findOrFail($selectedAssetId)" />
    {{-- Empty state --}}
    @elseif ($this->assets->isEmpty())
        <div class="text-center py-16 space-y-4">
            <flux:text class="text-lg">{{ __('No assets yet.') }}</flux:text>
            <flux:text>{{ __('Get started quickly with pre-built templates or add items manually.') }}</flux:text>
            <div class="flex items-center justify-center gap-3 mt-4">
                <flux:button variant="primary" href="{{ route('assets.templates') }}">
                    {{ __('Add from Templates') }}
                </flux:button>
                <flux:button variant="ghost" wire:click="openCreateForm">
                    {{ __('Add Manually') }}
                </flux:button>
            </div>
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
