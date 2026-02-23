<section class="max-w-2xl mx-auto px-4 py-8 space-y-6">
    <flux:heading size="xl">{{ __('Maintenance Schedule') }}</flux:heading>

    <div>
        <flux:select wire:model.live="filterAssetId" placeholder="{{ __('All Assets') }}">
            <flux:select.option :value="null">{{ __('All Assets') }}</flux:select.option>
            @foreach ($this->assets as $asset)
                <flux:select.option :value="$asset->id">{{ $asset->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if ($this->occurrences->isEmpty())
        <div class="text-center py-12 space-y-2">
            <flux:text class="text-lg">{{ __('No pending maintenance tasks.') }}</flux:text>
            <flux:text>{{ __('All caught up!') }}</flux:text>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($this->occurrences as $occurrence)
                <flux:card wire:key="occurrence-{{ $occurrence->id }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <flux:text class="font-medium">{{ $occurrence->task->name }}</flux:text>
                            <flux:text size="sm">{{ $occurrence->task->asset->name }}</flux:text>
                            <div class="flex items-center gap-2">
                                <flux:text size="sm">
                                    {{ __('Due:') }} {{ $occurrence->due_date->format('M j, Y') }}
                                </flux:text>
                                @if ($occurrence->isOverdue())
                                    <flux:badge color="red">{{ __('Overdue') }}</flux:badge>
                                @endif
                            </div>
                        </div>

                        <flux:button
                            variant="ghost"
                            size="sm"
                            wire:click="completeOccurrence({{ $occurrence->id }})"
                            wire:loading.attr="disabled"
                            wire:target="completeOccurrence({{ $occurrence->id }})"
                        >
                            <span wire:loading.remove wire:target="completeOccurrence({{ $occurrence->id }})">
                                {{ __('Mark Complete') }}
                            </span>
                            <span wire:loading wire:target="completeOccurrence({{ $occurrence->id }})">
                                {{ __('Saving…') }}
                            </span>
                        </flux:button>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif
</section>
