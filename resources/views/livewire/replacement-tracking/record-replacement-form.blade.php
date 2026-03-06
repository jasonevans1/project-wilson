<flux:card>
    <form wire:submit="save" class="space-y-5">
        <flux:heading size="sm">{{ __('Record Replacement') }}</flux:heading>

        <flux:input
            wire:model="installedAt"
            :label="__('New Installation Date')"
            type="date"
            required
            :error="$errors->first('installedAt')"
        />

        <flux:input
            wire:model="expectedLifespanYears"
            :label="__('Expected Lifespan (Years)')"
            type="number"
            min="1"
            max="100"
            :error="$errors->first('expectedLifespanYears')"
        />

        <flux:input
            wire:model="cost"
            :label="__('Replacement Cost (optional)')"
            type="number"
            min="0"
            step="0.01"
            :error="$errors->first('cost')"
        />

        <flux:textarea
            wire:model="notes"
            :label="__('Notes (optional)')"
            rows="3"
            :error="$errors->first('notes')"
        />

        <div class="flex items-center gap-3 pt-2">
            <flux:button variant="primary" type="submit">
                {{ __('Save') }}
            </flux:button>
            <flux:button variant="ghost" type="button" wire:click="$parent.closeRecordForm()">
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </form>
</flux:card>
