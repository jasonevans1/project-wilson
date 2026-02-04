<div class="space-y-5">
    @if ($editMode)
        <livewire:assets.asset-form :asset="$asset" @asset-updated="handleAssetUpdated" @close-panel="cancelEdit" />
    @else
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ $asset->name }}</flux:heading>

            <div class="flex items-center gap-2">
                <flux:button variant="ghost" size="sm" wire:click="closePanel">
                    {{ __('Back') }}
                </flux:button>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <flux:text size="xs" class="uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                    {{ __('Category') }}
                </flux:text>
                <flux:text class="mt-0.5">{{ $asset->category->label() }}</flux:text>
            </div>

            <div>
                <flux:text size="xs" class="uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                    {{ __('Location') }}
                </flux:text>
                <flux:text class="mt-0.5">{{ $asset->location }}</flux:text>
            </div>

            @if ($asset->purchase_date)
                <div>
                    <flux:text size="xs" class="uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        {{ __('Purchase Date') }}
                    </flux:text>
                    <flux:text class="mt-0.5">{{ $asset->purchase_date->format('Y-m-d') }}</flux:text>
                </div>
            @endif

            @if ($asset->install_date)
                <div>
                    <flux:text size="xs" class="uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        {{ __('Install Date') }}
                    </flux:text>
                    <flux:text class="mt-0.5">{{ $asset->install_date->format('Y-m-d') }}</flux:text>
                </div>
            @endif

            @if ($asset->warranty_expiration_date)
                <div>
                    <flux:text size="xs" class="uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        {{ __('Warranty Expiration') }}
                    </flux:text>
                    <flux:text class="mt-0.5">{{ $asset->warranty_expiration_date->format('Y-m-d') }}</flux:text>
                </div>
            @endif
        </div>

        @if ($asset->notes)
            <div>
                <flux:text size="xs" class="uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                    {{ __('Notes') }}
                </flux:text>
                <flux:text class="mt-0.5">{{ $asset->notes }}</flux:text>
            </div>
        @endif

        <div class="flex items-center gap-2 pt-2 border-t border-zinc-200 dark:border-zinc-700">
            <flux:button variant="ghost" size="sm" wire:click="startEdit">
                {{ __('Edit') }}
            </flux:button>
            <flux:button variant="ghost" size="sm">
                {{ __('Archive') }}
            </flux:button>
        </div>
    @endif
</div>
