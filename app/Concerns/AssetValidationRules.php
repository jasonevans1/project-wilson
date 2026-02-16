<?php

namespace App\Concerns;

use App\Enums\AssetCategory;
use Illuminate\Validation\Rule;

trait AssetValidationRules
{
    /**
     * Get the validation rules used to validate assets.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function assetRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(AssetCategory::class)],
            'location' => ['required', 'string', 'max:255'],
            'purchaseDate' => ['nullable', 'date'],
            'installDate' => ['nullable', 'date'],
            'warrantyExpirationDate' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
