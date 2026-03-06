<div>
    @if ($editMode)
        <livewire:assets.asset-form :asset="$asset" @asset-updated="handleAssetUpdated" @close-panel="cancelEdit" />
    @else
        <flux:card>
        <div class="space-y-5">
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

        <div class="pt-2 border-t border-zinc-200 dark:border-zinc-700">
            <flux:text size="xs" class="uppercase tracking-wide text-zinc-500 dark:text-zinc-400 mb-2">
                {{ __('Replacement') }}
            </flux:text>

            @if ($showingReplacementSetup)
                <livewire:replacement-tracking.replacement-setup-form :asset="$asset" />
            @elseif ($showingRecordReplacement)
                <livewire:replacement-tracking.record-replacement-form :asset="$asset" />
            @elseif ($asset->install_date && $asset->expected_lifespan_years)
                @php
                    $replacementDate = $asset->install_date->copy()->addYears($asset->expected_lifespan_years);
                    $daysRemaining   = (int) now()->diffInDays($replacementDate, false);
                    $yearsRemaining  = round($daysRemaining / 365, 1);
                    $usefulLifePct   = max(0, min(100, round(($daysRemaining / ($asset->expected_lifespan_years * 365)) * 100)));
                @endphp

                <div class="space-y-2">
                    <flux:text>
                        {{ __('Expected replacement:') }} <strong>{{ $replacementDate->format('Y') }}</strong>
                    </flux:text>

                    @if ($daysRemaining >= 0)
                        <flux:text>{{ $yearsRemaining }} {{ __('years remaining') }}</flux:text>
                    @else
                        <flux:badge color="red">{{ abs($yearsRemaining) }} {{ __('years overdue') }}</flux:badge>
                    @endif

                    <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $usefulLifePct }}%"></div>
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:button variant="ghost" size="sm" wire:click="openSetupForm">
                            {{ __('Edit') }}
                        </flux:button>
                        <flux:button variant="ghost" size="sm" wire:click="openRecordForm">
                            {{ __('Record Replacement') }}
                        </flux:button>
                    </div>
                </div>

                {{-- Replacement history --}}
                @php $history = $asset->replacementEvents()->latest('installed_at')->get(); @endphp
                @if ($history->isNotEmpty())
                    <div class="mt-3 space-y-1">
                        <flux:text size="xs" class="uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                            {{ __('Replacement History') }}
                        </flux:text>
                        @foreach ($history as $event)
                            <div class="flex items-center justify-between text-sm py-1 border-b border-zinc-100 dark:border-zinc-700 last:border-0">
                                <flux:text>{{ $event->installed_at->format('Y-m-d') }}</flux:text>
                                <flux:text class="text-zinc-500 dark:text-zinc-400">
                                    {{ $event->cost ? '$'.number_format($event->cost, 2) : __('Not recorded') }}
                                </flux:text>
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    {{ __('Replacement tracking not yet configured.') }}
                </flux:text>
                <div class="mt-2">
                    <flux:button variant="ghost" size="sm" wire:click="openSetupForm">
                        {{ __('Set Up') }}
                    </flux:button>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-2 pt-2 border-t border-zinc-200 dark:border-zinc-700">
            <flux:button variant="ghost" size="sm" wire:click="startEdit">
                {{ __('Edit') }}
            </flux:button>

            <flux:button variant="ghost" size="sm" :href="route('maintenance.asset', $asset)" wire:navigate>
                {{ __('View Maintenance') }}
            </flux:button>

            <flux:button variant="ghost" size="sm" :href="route('service-records.index', $asset)" wire:navigate>
                {{ __('View Service Records') }}
            </flux:button>

            @if ($asset->status === \App\Enums\AssetStatus::Active)
                <flux:modal.trigger name="confirm-archive">
                    <flux:button variant="ghost" size="sm">
                        {{ __('Archive') }}
                    </flux:button>
                </flux:modal.trigger>
            @else
                <flux:button variant="ghost" size="sm" wire:click="restore">
                    {{ __('Restore') }}
                </flux:button>
            @endif
        </div>
        </div>
        </flux:card>

        <flux:modal name="confirm-archive" wire:model="confirmingArchive" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Archive this asset?') }}</flux:heading>
                    <flux:text class="mt-2">
                        {{ __('This asset will be moved to the archived list and will no longer appear in your active assets.') }}
                    </flux:text>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button variant="danger" wire:click="archive">{{ __('Archive') }}</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
