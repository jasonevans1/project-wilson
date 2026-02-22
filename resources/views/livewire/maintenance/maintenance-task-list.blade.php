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
                                @if ($editingOccurrenceId === $task->pendingOccurrence->id)
                                    <div class="flex items-center gap-2 mt-1">
                                        <flux:input
                                            type="date"
                                            wire:model="editDueDate"
                                            size="sm"
                                        />
                                        <flux:button size="sm" wire:click="saveOccurrenceDueDate">{{ __('Save') }}</flux:button>
                                        <flux:button size="sm" variant="ghost" wire:click="cancelEditDueDate">{{ __('Cancel') }}</flux:button>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2">
                                        <flux:text size="sm">
                                            {{ __('Due:') }} {{ $task->pendingOccurrence->due_date->format('M j, Y') }}
                                        </flux:text>
                                        @if ($task->pendingOccurrence->isOverdue())
                                            <flux:badge color="red">{{ __('Overdue') }}</flux:badge>
                                        @endif
                                        <flux:button
                                            icon="pencil"
                                            size="sm"
                                            variant="ghost"
                                            wire:click="startEditDueDate({{ $task->pendingOccurrence->id }})"
                                        />
                                    </div>
                                @endif
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if ($task->pendingOccurrence)
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    wire:click="completeOccurrence({{ $task->pendingOccurrence->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="completeOccurrence({{ $task->pendingOccurrence->id }})"
                                    wire:key="complete-{{ $task->pendingOccurrence->id }}"
                                >
                                    <span wire:loading.remove wire:target="completeOccurrence({{ $task->pendingOccurrence->id }})">
                                        {{ __('Mark Complete') }}
                                    </span>
                                    <span wire:loading wire:target="completeOccurrence({{ $task->pendingOccurrence->id }})">
                                        {{ __('Saving…') }}
                                    </span>
                                </flux:button>
                            @endif

                            <flux:button
                                variant="ghost"
                                size="sm"
                                wire:click="deactivateTask({{ $task->id }})"
                                wire:confirm="{{ __('Deactivate this task? Its history will be preserved.') }}"
                            >
                                {{ __('Deactivate') }}
                            </flux:button>
                        </div>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif
</section>
