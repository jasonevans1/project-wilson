<section class="max-w-3xl mx-auto px-4 py-8 space-y-8">
    <flux:heading size="xl">{{ __('Replacement Tracking') }}</flux:heading>

    {{-- Tracked Assets --}}
    @if ($trackedAssets->isNotEmpty())
        <div class="space-y-4">
            <flux:heading size="sm" class="text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                {{ __('Tracked Assets') }}
            </flux:heading>

            @foreach ($trackedAssets as $asset)
                @php
                    $replacementDate = $asset->install_date->copy()->addYears($asset->expected_lifespan_years);
                    $daysRemaining   = (int) now()->diffInDays($replacementDate, false);
                    $yearsRemaining  = round($daysRemaining / 365, 1);
                    $usefulLifePct   = max(0, min(100, round(($daysRemaining / ($asset->expected_lifespan_years * 365)) * 100)));
                    $isOverdue       = $daysRemaining < 0;
                @endphp

                <flux:card class="space-y-3">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <flux:heading>{{ $asset->name }}</flux:heading>
                            <flux:text size="sm" class="text-slate-500 dark:text-slate-400">
                                {{ $asset->category->label() }} &middot; {{ $asset->location }}
                            </flux:text>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if ($isOverdue)
                                <flux:badge color="red">{{ __('Overdue') }}</flux:badge>
                            @endif
                            <flux:button variant="ghost" size="sm" wire:click="openSetupForm({{ $asset->id }})">
                                {{ __('Edit') }}
                            </flux:button>
                            <flux:button variant="ghost" size="sm" wire:click="openRecordForm({{ $asset->id }})">
                                {{ __('Record Replacement') }}
                            </flux:button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <flux:text size="xs" class="uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ __('Expected Replacement') }}
                            </flux:text>
                            <flux:text class="mt-0.5 font-medium">{{ $replacementDate->format('Y') }}</flux:text>
                        </div>
                        <div>
                            <flux:text size="xs" class="uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ __('Status') }}
                            </flux:text>
                            <flux:text class="mt-0.5">
                                @if ($isOverdue)
                                    {{ abs($yearsRemaining) }} {{ __('years overdue') }}
                                @else
                                    {{ $yearsRemaining }} {{ __('years remaining') }}
                                @endif
                            </flux:text>
                        </div>
                    </div>

                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $isOverdue ? 'bg-red-500' : 'bg-blue-500' }}"
                             style="width: {{ $usefulLifePct }}%"></div>
                    </div>

                    @if ($setupAssetId === $asset->id)
                        <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                            <livewire:replacement-tracking.replacement-setup-form
                                :asset="$asset"
                                :key="'setup-'.$asset->id"
                            />
                        </div>
                    @endif

                    @if ($recordingAssetId === $asset->id)
                        <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                            <livewire:replacement-tracking.record-replacement-form
                                :asset="$asset"
                                :key="'record-'.$asset->id"
                            />
                        </div>
                    @endif
                </flux:card>
            @endforeach
        </div>
    @endif

    {{-- Untracked Assets --}}
    @if ($untrackedAssets->isNotEmpty())
        <div class="space-y-4">
            <flux:heading size="sm" class="text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                {{ __('Not Yet Tracked') }}
            </flux:heading>

            @foreach ($untrackedAssets as $asset)
                <flux:card class="flex items-center justify-between gap-4">
                    <div>
                        <flux:heading>{{ $asset->name }}</flux:heading>
                        <flux:text size="sm" class="text-slate-500 dark:text-slate-400">
                            {{ $asset->category->label() }} &middot; {{ $asset->location }}
                        </flux:text>
                    </div>

                    <div class="shrink-0">
                        <flux:button variant="primary" size="sm" wire:click="openSetupForm({{ $asset->id }})">
                            {{ __('Set Up') }}
                        </flux:button>
                    </div>
                </flux:card>

                @if ($setupAssetId === $asset->id)
                    <livewire:replacement-tracking.replacement-setup-form
                        :asset="$asset"
                        :key="'setup-'.$asset->id"
                    />
                @endif
            @endforeach
        </div>
    @endif

    @if ($trackedAssets->isEmpty() && $untrackedAssets->isEmpty())
        <flux:card>
            <flux:text class="text-center text-slate-500 dark:text-slate-400">
                {{ __('No assets found. Add an asset to start tracking replacements.') }}
            </flux:text>
        </flux:card>
    @endif
</section>
