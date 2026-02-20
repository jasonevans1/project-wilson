<flux:card>
    <form wire:submit="save" class="space-y-5">
        <flux:heading size="lg">{{ __('Add Maintenance Task') }}</flux:heading>

        <flux:input
            wire:model="name"
            :label="__('Task Name')"
            type="text"
            required
            :error="$errors->first('name')"
        />

        <flux:textarea
            wire:model="description"
            :label="__('Description')"
            rows="3"
            :error="$errors->first('description')"
        />

        <div class="grid grid-cols-2 gap-4">
            <flux:select
                wire:model="recurrenceUnit"
                :label="__('Recurrence')"
                required
                :error="$errors->first('recurrenceUnit')"
            >
                @foreach (\App\Enums\RecurrenceUnit::cases() as $case)
                    <flux:select.option :value="$case->value">{{ ucfirst($case->value) }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input
                wire:model="recurrenceCount"
                :label="__('Every (count)')"
                type="number"
                min="1"
                max="365"
                required
                :error="$errors->first('recurrenceCount')"
            />
        </div>

        <flux:input
            wire:model="startDate"
            :label="__('Start Date')"
            type="date"
            :error="$errors->first('startDate')"
        />

        <div class="pt-2">
            <flux:button variant="primary" type="submit">
                {{ __('Add Task') }}
            </flux:button>
        </div>
    </form>
</flux:card>
