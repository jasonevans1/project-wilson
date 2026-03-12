<section class="max-w-4xl mx-auto px-4 py-8 space-y-6">
    <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>

    <div class="grid gap-4 sm:grid-cols-2">

        {{-- Assets Panel --}}
        <flux:card class="flex flex-col gap-3">
            <div class="flex items-center gap-2">
                <flux:icon name="wrench" class="size-5 text-slate-500 dark:text-slate-400" />
                <flux:heading size="sm" class="uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    {{ __('Assets') }}
                </flux:heading>
            </div>

            @if ($this->assetCount > 0)
                <flux:text class="text-3xl font-bold">{{ $this->assetCount }}</flux:text>
                <flux:text size="sm" class="text-slate-500 dark:text-slate-400">
                    {{ trans_choice('active asset|active assets', $this->assetCount) }}
                </flux:text>
            @else
                <flux:text class="text-slate-500 dark:text-slate-400">
                    {{ __('No assets tracked yet.') }}
                </flux:text>
            @endif

            <div class="mt-auto pt-2">
                <flux:button variant="ghost" size="sm" :href="route('assets.index')" wire:navigate>
                    @if ($this->assetCount > 0)
                        {{ __('View assets') }}
                    @else
                        {{ __('Add your first asset') }}
                    @endif
                </flux:button>
            </div>
        </flux:card>

        {{-- Maintenance Panel --}}
        <flux:card class="flex flex-col gap-3">
            <div class="flex items-center gap-2">
                <flux:icon name="calendar-days" class="size-5 text-slate-500 dark:text-slate-400" />
                <flux:heading size="sm" class="uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    {{ __('Maintenance') }}
                </flux:heading>
            </div>

            @if ($this->overdueMaintenanceCount > 0 || $this->upcomingMaintenanceCount > 0)
                <div class="flex flex-wrap gap-4">
                    @if ($this->overdueMaintenanceCount > 0)
                        <flux:badge color="red" size="lg">
                            {{ $this->overdueMaintenanceCount }} {{ __('overdue') }}
                        </flux:badge>
                    @endif
                    @if ($this->upcomingMaintenanceCount > 0)
                        <flux:badge color="zinc" size="lg">
                            {{ $this->upcomingMaintenanceCount }} {{ __('upcoming') }}
                        </flux:badge>
                    @endif
                </div>
                <div class="mt-auto pt-2">
                    <flux:button variant="ghost" size="sm" :href="route('maintenance.schedule')" wire:navigate>
                        {{ __('View schedule') }}
                    </flux:button>
                </div>
            @elseif ($this->hasMaintenanceTasks)
                <flux:text class="text-slate-500 dark:text-slate-400">
                    {{ __('All caught up!') }}
                </flux:text>
                <div class="mt-auto pt-2">
                    <flux:button variant="ghost" size="sm" :href="route('maintenance.schedule')" wire:navigate>
                        {{ __('View schedule') }}
                    </flux:button>
                </div>
            @else
                <flux:text class="text-slate-500 dark:text-slate-400">
                    {{ __('No maintenance tasks scheduled yet.') }}
                </flux:text>
                <div class="mt-auto pt-2">
                    <flux:button variant="ghost" size="sm" :href="route('maintenance.schedule')" wire:navigate>
                        {{ __('Create your first task') }}
                    </flux:button>
                </div>
            @endif
        </flux:card>

        {{-- Service Records Panel --}}
        <flux:card class="flex flex-col gap-3">
            <div class="flex items-center gap-2">
                <flux:icon name="clipboard-document-list" class="size-5 text-slate-500 dark:text-slate-400" />
                <flux:heading size="sm" class="uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    {{ __('Service Records') }}
                </flux:heading>
            </div>

            @if ($this->latestServiceRecord)
                <div>
                    <flux:text class="font-medium">{{ $this->latestServiceRecord->asset->name }}</flux:text>
                    <flux:text size="sm" class="text-slate-500 dark:text-slate-400">
                        {{ $this->latestServiceRecord->service_date->format('M j, Y') }}
                    </flux:text>
                </div>
                <flux:text size="sm" class="text-slate-500 dark:text-slate-400">
                    {{ __('Most recent service') }}
                </flux:text>
            @else
                <flux:text class="text-slate-500 dark:text-slate-400">
                    {{ __('No service records logged yet.') }}
                </flux:text>
            @endif

            <div class="mt-auto pt-2">
                <flux:button variant="ghost" size="sm" :href="route('assets.index')" wire:navigate>
                    @if ($this->latestServiceRecord)
                        {{ __('View assets') }}
                    @else
                        {{ __('Log your first service record') }}
                    @endif
                </flux:button>
            </div>
        </flux:card>

        {{-- Replacement Tracking Panel --}}
        <flux:card class="flex flex-col gap-3">
            <div class="flex items-center gap-2">
                <flux:icon name="arrow-path" class="size-5 text-slate-500 dark:text-slate-400" />
                <flux:heading size="sm" class="uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    {{ __('Replacement Tracking') }}
                </flux:heading>
            </div>

            @if ($this->approachingReplacementCount === null)
                {{-- No tracking configured --}}
                <flux:text class="text-slate-500 dark:text-slate-400">
                    {{ __('No replacement timelines configured.') }}
                </flux:text>
                <div class="mt-auto pt-2">
                    <flux:button variant="ghost" size="sm" :href="route('replacement-tracking')" wire:navigate>
                        {{ __('Set up replacement tracking') }}
                    </flux:button>
                </div>
            @elseif ($this->approachingReplacementCount === 0)
                {{-- Tracking configured, nothing approaching --}}
                <flux:text class="text-slate-500 dark:text-slate-400">
                    {{ __('All assets within lifespan.') }}
                </flux:text>
                <div class="mt-auto pt-2">
                    <flux:button variant="ghost" size="sm" :href="route('replacement-tracking')" wire:navigate>
                        {{ __('View replacement tracking') }}
                    </flux:button>
                </div>
            @else
                <flux:text class="text-3xl font-bold text-amber-600 dark:text-amber-400">
                    {{ $this->approachingReplacementCount }}
                </flux:text>
                <flux:text size="sm" class="text-slate-500 dark:text-slate-400">
                    {{ trans_choice('asset needs attention|assets need attention', $this->approachingReplacementCount) }}
                    (12 {{ __('months') }})
                </flux:text>
                <div class="mt-auto pt-2">
                    <flux:button variant="ghost" size="sm" :href="route('replacement-tracking')" wire:navigate>
                        {{ __('View replacement tracking') }}
                    </flux:button>
                </div>
            @endif
        </flux:card>

    </div>
</section>
