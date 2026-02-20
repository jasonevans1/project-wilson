<section class="max-w-2xl mx-auto px-4 py-8 space-y-6">
    <flux:heading size="xl">{{ $asset->name }} — {{ __('Maintenance Tasks') }}</flux:heading>

    <livewire:maintenance.maintenance-task-form :asset-id="$asset->id" />

    @if ($this->tasks->isEmpty())
        <div class="text-center py-12 space-y-2">
            <flux:text class="text-lg">{{ __('No maintenance tasks yet.') }}</flux:text>
            <flux:text>{{ __('Add the first task above.') }}</flux:text>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($this->tasks as $task)
                <flux:card wire:key="task-{{ $task->id }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <flux:text class="font-medium">{{ $task->name }}</flux:text>
                            <flux:text size="sm">
                                {{ __('Every') }} {{ $task->recurrence_count }} {{ ucfirst($task->recurrence_unit->value) }}
                            </flux:text>
                            @if ($task->pendingOccurrence)
                                <flux:text size="sm">
                                    {{ __('Due:') }} {{ $task->pendingOccurrence->due_date->format('M j, Y') }}
                                    @if ($task->pendingOccurrence->isOverdue())
                                        <flux:badge color="red" class="ml-1">{{ __('Overdue') }}</flux:badge>
                                    @endif
                                </flux:text>
                            @endif
                        </div>

                        <flux:button
                            variant="ghost"
                            size="sm"
                            wire:click="deactivateTask({{ $task->id }})"
                            wire:confirm="{{ __('Deactivate this task? Its history will be preserved.') }}"
                        >
                            {{ __('Deactivate') }}
                        </flux:button>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif
</section>
