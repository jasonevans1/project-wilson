<?php

namespace App\Concerns;

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
            'category' => ['required', 'enum:App\Enums\AssetCategory'],
            'location' => ['required', 'string', 'max:255'],
            'purchaseDate' => ['nullable', 'date'],
            'installDate' => ['nullable', 'date'],
            'warrantyExpirationDate' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
