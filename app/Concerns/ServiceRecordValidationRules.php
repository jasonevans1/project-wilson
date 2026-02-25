<?php

namespace App\Concerns;

use App\Enums\ServiceType;
use Illuminate\Validation\Rule;

trait ServiceRecordValidationRules
{
    /**
     * Get the validation rules used to validate service records.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function serviceRecordRules(): array
    {
        return [
            'serviceDate' => ['required', 'date'],
            'serviceType' => ['required', Rule::enum(ServiceType::class)],
            'description' => ['required', 'string', 'max:5000'],
            'providerName' => ['nullable', 'string', 'max:255'],
            'cost' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'underWarranty' => ['boolean'],
            'warrantyExpiresOn' => ['nullable', 'date', 'required_if:underWarranty,true'],
        ];
    }
}
