<flux:card>
    <form wire:submit="save" class="space-y-5">
        <flux:heading size="sm">{{ __('Configure Replacement Tracking') }}</flux:heading>

        <flux:input
            wire:model="expectedLifespanYears"
            :label="__('Expected Lifespan (Years)')"
            type="number"
            min="1"
            max="100"
            required
            :error="$errors->first('expectedLifespanYears')"
        />

        <flux:input
            wire:model="installDate"
            :label="__('Installation Date')"
            type="date"
            required
            :error="$errors->first('installDate')"
        />

        <div class="flex items-center gap-3 pt-2">
            <flux:button variant="primary" type="submit">
                {{ __('Save') }}
            </flux:button>
            <flux:button variant="ghost" type="button" wire:click="cancel">
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </form>
</flux:card>
