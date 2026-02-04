<div>
    <form wire:submit="save" class="space-y-5">
        <flux:input
            wire:model="name"
            :label="__('Name')"
            type="text"
            required
            :error="$errors->first('name')"
        />

        <flux:select
            wire:model="category"
            :label="__('Category')"
            required
            :error="$errors->first('category')"
        >
            <flux:select.option value="" disabled>{{ __('Select a category') }}</flux:select.option>
            @foreach (\App\Enums\AssetCategory::cases() as $case)
                <flux:select.option :value="$case->value">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input
            wire:model="location"
            :label="__('Location')"
            type="text"
            required
            :error="$errors->first('location')"
        />

        <flux:input
            wire:model="purchaseDate"
            :label="__('Purchase Date')"
            type="date"
            :error="$errors->first('purchaseDate')"
        />

        <flux:input
            wire:model="installDate"
            :label="__('Install Date')"
            type="date"
            :error="$errors->first('installDate')"
        />

        <flux:input
            wire:model="warrantyExpirationDate"
            :label="__('Warranty Expiration Date')"
            type="date"
            :error="$errors->first('warrantyExpirationDate')"
        />

        <flux:textarea
            wire:model="notes"
            :label="__('Notes')"
            rows="3"
            :error="$errors->first('notes')"
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
</div>
