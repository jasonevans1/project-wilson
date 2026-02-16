<?php

namespace App\Livewire\Assets;

use App\Concerns\AssetValidationRules;
use App\Models\AssetTemplate;
use App\Models\TemplateGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TemplateLibrary extends Component
{
    use AssetValidationRules;

    /** @var int Current wizard step (1=browse, 2=review, 3=confirm) */
    public int $step = 1;

    /** @var list<int> */
    public array $selectedTemplateIds = [];

    /** @var list<array<string, mixed>> Editable field arrays for review step */
    public array $customizedItems = [];

    /** @var list<int> Track which group IDs are expanded */
    public array $expandedGroups = [];

    /** @var int Number of assets created (for confirmation step) */
    public int $createdCount = 0;

    /**
     * All template groups with their templates, ordered by display_order.
     *
     * @return Collection<int, TemplateGroup>
     */
    #[Computed]
    public function templateGroups(): Collection
    {
        return TemplateGroup::query()
            ->with(['templates' => fn ($q) => $q->orderBy('display_order')])
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Array of lowercase asset names owned by authenticated user.
     *
     * @return list<string>
     */
    #[Computed]
    public function ownedAssetNames(): array
    {
        return Auth::user()->assets()
            ->pluck('name')
            ->map(fn (string $name) => strtolower($name))
            ->all();
    }

    /**
     * Count of selected templates.
     */
    #[Computed]
    public function selectedCount(): int
    {
        return count($this->selectedTemplateIds);
    }

    /**
     * Toggle a template ID in/out of the selection.
     */
    public function toggleTemplate(int $templateId): void
    {
        $index = array_search($templateId, $this->selectedTemplateIds);

        if ($index !== false) {
            array_splice($this->selectedTemplateIds, $index, 1);
            $this->selectedTemplateIds = array_values($this->selectedTemplateIds);
        } else {
            $this->selectedTemplateIds[] = $templateId;
        }
    }

    /**
     * Add a template ID to selection (allows duplicates for FR-014).
     */
    public function addTemplate(int $templateId): void
    {
        $this->selectedTemplateIds[] = $templateId;
    }

    /**
     * Remove a specific selection by index.
     */
    public function removeSelectedTemplate(int $index): void
    {
        array_splice($this->selectedTemplateIds, $index, 1);
        $this->selectedTemplateIds = array_values($this->selectedTemplateIds);
    }

    /**
     * Transition from step 1 to step 2. Hydrate customizedItems from selectedTemplateIds.
     */
    public function proceedToReview(): void
    {
        $templates = AssetTemplate::query()
            ->whereIn('id', $this->selectedTemplateIds)
            ->get()
            ->keyBy('id');

        $this->customizedItems = [];

        foreach ($this->selectedTemplateIds as $templateId) {
            $template = $templates->get($templateId);

            if ($template) {
                $this->customizedItems[] = [
                    'templateId' => $template->id,
                    'name' => $template->name,
                    'category' => $template->category->value,
                    'location' => $template->location,
                    'purchaseDate' => null,
                    'installDate' => null,
                    'warrantyExpirationDate' => null,
                    'notes' => null,
                ];
            }
        }

        $this->step = 2;
    }

    /**
     * Remove an item from customizedItems during review.
     */
    public function removeCustomizedItem(int $index): void
    {
        array_splice($this->customizedItems, $index, 1);
        $this->customizedItems = array_values($this->customizedItems);
    }

    /**
     * Go back from review to browse. Preserves selectedTemplateIds.
     */
    public function backToBrowse(): void
    {
        $this->customizedItems = [];
        $this->step = 1;
    }

    /**
     * Validate all customized items and create assets in a DB transaction.
     */
    public function confirmConversion(): void
    {
        $rules = [];
        $baseRules = $this->assetRules();

        foreach ($this->customizedItems as $index => $item) {
            foreach ($baseRules as $field => $fieldRules) {
                $rules["customizedItems.{$index}.{$field}"] = $fieldRules;
            }
        }

        $this->validate($rules);

        $count = 0;

        DB::transaction(function () use (&$count) {
            foreach ($this->customizedItems as $item) {
                Auth::user()->assets()->create([
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'location' => $item['location'],
                    'purchase_date' => ! empty($item['purchaseDate']) ? $item['purchaseDate'] : null,
                    'install_date' => ! empty($item['installDate']) ? $item['installDate'] : null,
                    'warranty_expiration_date' => ! empty($item['warrantyExpirationDate']) ? $item['warrantyExpirationDate'] : null,
                    'notes' => $item['notes'] ?? null,
                ]);
                $count++;
            }
        });

        $this->createdCount = $count;
        $this->step = 3;
        $this->dispatch('assets-created');
    }

    /**
     * Navigate back to the assets index page.
     */
    public function backToAssets(): void
    {
        $this->redirect(route('assets.index'));
    }
}
