<section class="max-w-2xl mx-auto px-4 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ $asset->name }} — {{ __('Service Records') }}</flux:heading>

        @if (! $showForm)
            <flux:button variant="primary" wire:click="showCreateForm">
                {{ __('Add Service Record') }}
            </flux:button>
        @endif
    </div>

    @if ($showForm)
        <flux:card>
            <form wire:submit="saveRecord" class="space-y-5">
                <flux:heading size="lg">{{ __('New Service Record') }}</flux:heading>

                <flux:input
                    wire:model="serviceDate"
                    :label="__('Service Date')"
                    type="date"
                    required
                    :error="$errors->first('serviceDate')"
                />

                <flux:select
                    wire:model="serviceType"
                    :label="__('Service Type')"
                    required
                    :error="$errors->first('serviceType')"
                >
                    <flux:select.option value="">{{ __('Select type...') }}</flux:select.option>
                    @foreach (\App\Enums\ServiceType::cases() as $case)
                        <flux:select.option :value="$case->value">{{ $case->label() }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:textarea
                    wire:model="description"
                    :label="__('Description')"
                    rows="4"
                    required
                    :error="$errors->first('description')"
                />

                <flux:input
                    wire:model="providerName"
                    :label="__('Provider / Contractor')"
                    type="text"
                    :error="$errors->first('providerName')"
                />

                <flux:input
                    wire:model="cost"
                    :label="__('Cost')"
                    type="number"
                    step="0.01"
                    min="0"
                    :error="$errors->first('cost')"
                />

                <flux:checkbox
                    wire:model.live="underWarranty"
                    :label="__('Under Warranty')"
                />

                @if ($underWarranty)
                    <flux:input
                        wire:model="warrantyExpiresOn"
                        :label="__('Warranty Expires')"
                        type="date"
                        :error="$errors->first('warrantyExpiresOn')"
                    />
                @endif

                <div class="flex items-center gap-2 pt-2">
                    <flux:button variant="primary" type="submit">
                        {{ __('Save Record') }}
                    </flux:button>
                    <flux:button variant="ghost" type="button" wire:click="cancelForm">
                        {{ __('Cancel') }}
                    </flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    @if ($this->records->isEmpty())
        <div class="text-center py-12 space-y-2">
            <flux:text class="text-lg">{{ __('No service records yet.') }}</flux:text>
            <flux:text>{{ __('Add the first record above.') }}</flux:text>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($this->records as $record)
                <flux:card wire:key="record-{{ $record->id }}">
                    @if ($editingRecordId === $record->id)
                        <form wire:submit="updateRecord" class="space-y-5">
                            <flux:heading size="lg">{{ __('Edit Service Record') }}</flux:heading>

                            <flux:input
                                wire:model="serviceDate"
                                :label="__('Service Date')"
                                type="date"
                                required
                                :error="$errors->first('serviceDate')"
                            />

                            <flux:select
                                wire:model="serviceType"
                                :label="__('Service Type')"
                                required
                                :error="$errors->first('serviceType')"
                            >
                                <flux:select.option value="">{{ __('Select type...') }}</flux:select.option>
                                @foreach (\App\Enums\ServiceType::cases() as $case)
                                    <flux:select.option :value="$case->value">{{ $case->label() }}</flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:textarea
                                wire:model="description"
                                :label="__('Description')"
                                rows="4"
                                required
                                :error="$errors->first('description')"
                            />

                            <flux:input
                                wire:model="providerName"
                                :label="__('Provider / Contractor')"
                                type="text"
                                :error="$errors->first('providerName')"
                            />

                            <flux:input
                                wire:model="cost"
                                :label="__('Cost')"
                                type="number"
                                step="0.01"
                                min="0"
                                :error="$errors->first('cost')"
                            />

                            <flux:checkbox
                                wire:model.live="underWarranty"
                                :label="__('Under Warranty')"
                            />

                            @if ($underWarranty)
                                <flux:input
                                    wire:model="warrantyExpiresOn"
                                    :label="__('Warranty Expires')"
                                    type="date"
                                    :error="$errors->first('warrantyExpiresOn')"
                                />
                            @endif

                            <div class="flex items-center gap-2 pt-2">
                                <flux:button variant="primary" type="submit">
                                    {{ __('Save Changes') }}
                                </flux:button>
                                <flux:button variant="ghost" type="button" wire:click="cancelEdit">
                                    {{ __('Cancel') }}
                                </flux:button>
                            </div>
                        </form>
                    @else
                        <div class="flex items-start justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <flux:badge>{{ $record->service_type->label() }}</flux:badge>
                                    <flux:text size="sm">{{ $record->service_date->format('M j, Y') }}</flux:text>
                                    @if ($record->service_date->isFuture())
                                        <flux:badge color="yellow">{{ __('Future Date') }}</flux:badge>
                                    @endif
                                </div>
                                <flux:text>{{ $record->description }}</flux:text>
                                @if ($record->provider_name)
                                    <flux:text size="sm">{{ $record->provider_name }}</flux:text>
                                @endif
                                @if ($record->cost !== null)
                                    <flux:text size="sm">${{ number_format($record->cost, 2) }}</flux:text>
                                @endif
                            </div>
                            <div class="flex items-center gap-1">
                                <flux:button icon="pencil" size="sm" variant="ghost" wire:click="startEdit({{ $record->id }})" />
                                <flux:button
                                    variant="danger"
                                    size="sm"
                                    wire:click="deleteRecord({{ $record->id }})"
                                    wire:confirm="{{ __('Delete this service record? This cannot be undone.') }}"
                                >
                                    {{ __('Delete') }}
                                </flux:button>
                            </div>
                        </div>
                    @endif
                </flux:card>
            @endforeach
        </div>
    @endif
</section>
