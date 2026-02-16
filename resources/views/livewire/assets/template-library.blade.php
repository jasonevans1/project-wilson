<section class="max-w-2xl mx-auto px-4 py-8 space-y-6">
    {{-- ═══ Step 1: Browse ═══ --}}
    @if ($step === 1)
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('Template Library') }}</flux:heading>

            <div class="flex items-center gap-3">
                @if ($this->selectedCount > 0)
                    <flux:badge color="blue">{{ $this->selectedCount }} {{ __('selected') }}</flux:badge>
                @endif
                <flux:button variant="ghost" wire:click="backToAssets">
                    {{ __('Back to Assets') }}
                </flux:button>
            </div>
        </div>

        <flux:text>{{ __('Select templates to quickly add common home items.') }}</flux:text>

        {{-- Template groups --}}
        @forelse ($this->templateGroups as $group)
            <div wire:key="group-{{ $group->id }}" x-data="{ open: true }">
                <button
                    type="button"
                    x-on:click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-zinc-50 dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                >
                    <div class="flex items-center gap-2">
                        <flux:heading size="lg">{{ $group->name }}</flux:heading>
                        <flux:badge color="zinc" size="sm">{{ $group->templates->count() }}</flux:badge>
                    </div>
                    <flux:icon
                        name="chevron-down"
                        variant="mini"
                        class="transition-transform"
                        x-bind:class="open ? 'rotate-180' : ''"
                    />
                </button>

                <div x-show="open" x-collapse class="mt-2 space-y-2">
                    @foreach ($group->templates as $template)
                        @php
                            $isSelected = in_array($template->id, $selectedTemplateIds);
                            $isOwned = in_array(strtolower($template->name), $this->ownedAssetNames);
                        @endphp
                        <flux:card wire:key="template-{{ $template->id }}" size="sm">
                            <div class="flex items-center gap-3">
                                <flux:checkbox
                                    wire:click="toggleTemplate({{ $template->id }})"
                                    :checked="$isSelected"
                                />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <flux:text class="font-medium">{{ $template->name }}</flux:text>
                                        @if ($isOwned)
                                            <flux:badge color="amber" size="sm">{{ __('Already owned') }}</flux:badge>
                                        @endif
                                    </div>
                                    <flux:text size="sm">
                                        {{ $template->category->label() }} · {{ $template->location }}
                                    </flux:text>
                                </div>
                            </div>
                        </flux:card>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-16">
                <flux:text class="text-lg">{{ __('No templates available.') }}</flux:text>
            </div>
        @endforelse

        {{-- Sticky footer for proceeding --}}
        @if ($this->selectedCount > 0)
            <div class="sticky bottom-4 flex justify-end">
                <flux:button variant="primary" wire:click="proceedToReview">
                    {{ __('Review') }} ({{ $this->selectedCount }})
                </flux:button>
            </div>
        @endif

    {{-- ═══ Step 2: Review & Customize ═══ --}}
    @elseif ($step === 2)
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('Review & Customize') }}</flux:heading>
            <flux:badge color="blue">{{ count($customizedItems) }} {{ __('items') }}</flux:badge>
        </div>

        <flux:text>{{ __('Customize the details before adding these items as assets.') }}</flux:text>

        @foreach ($customizedItems as $index => $item)
            <flux:card wire:key="review-item-{{ $index }}">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:heading size="lg">{{ __('Item') }} {{ $index + 1 }}</flux:heading>
                        <flux:button variant="ghost" size="sm" wire:click="removeCustomizedItem({{ $index }})">
                            {{ __('Remove') }}
                        </flux:button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>{{ __('Name') }}</flux:label>
                            <flux:input wire:model="customizedItems.{{ $index }}.name" />
                            <flux:error name="customizedItems.{{ $index }}.name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Category') }}</flux:label>
                            <flux:select wire:model="customizedItems.{{ $index }}.category">
                                <option value="">{{ __('Select category') }}</option>
                                @foreach (\App\Enums\AssetCategory::cases() as $cat)
                                    <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="customizedItems.{{ $index }}.category" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Location') }}</flux:label>
                            <flux:input wire:model="customizedItems.{{ $index }}.location" />
                            <flux:error name="customizedItems.{{ $index }}.location" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Purchase Date') }}</flux:label>
                            <flux:input type="date" wire:model="customizedItems.{{ $index }}.purchaseDate" />
                            <flux:error name="customizedItems.{{ $index }}.purchaseDate" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Install Date') }}</flux:label>
                            <flux:input type="date" wire:model="customizedItems.{{ $index }}.installDate" />
                            <flux:error name="customizedItems.{{ $index }}.installDate" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Warranty Expiration') }}</flux:label>
                            <flux:input type="date" wire:model="customizedItems.{{ $index }}.warrantyExpirationDate" />
                            <flux:error name="customizedItems.{{ $index }}.warrantyExpirationDate" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>{{ __('Notes') }}</flux:label>
                        <flux:textarea wire:model="customizedItems.{{ $index }}.notes" rows="2" />
                        <flux:error name="customizedItems.{{ $index }}.notes" />
                    </flux:field>
                </div>
            </flux:card>
        @endforeach

        <div class="flex items-center justify-between">
            <flux:button variant="ghost" wire:click="backToBrowse">
                {{ __('Back') }}
            </flux:button>
            <flux:button variant="primary" wire:click="confirmConversion">
                {{ __('Create Assets') }} ({{ count($customizedItems) }})
            </flux:button>
        </div>

    {{-- ═══ Step 3: Confirmation ═══ --}}
    @elseif ($step === 3)
        <div class="text-center py-16 space-y-4">
            <flux:icon name="check-circle" variant="outline" class="mx-auto size-16 text-green-500" />
            <flux:heading size="xl">{{ __('Assets Created!') }}</flux:heading>
            <flux:text class="text-lg">
                {{ trans_choice(':count asset was successfully created.|:count assets were successfully created.', $createdCount, ['count' => $createdCount]) }}
            </flux:text>
            <flux:button variant="primary" wire:click="backToAssets">
                {{ __('Back to Assets') }}
            </flux:button>
        </div>
    @endif
</section>
