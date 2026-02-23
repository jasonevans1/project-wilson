<?php

namespace App\Http\Requests\Maintenance;

use App\Enums\RecurrenceUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveMaintenanceTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'asset_id' => [
                'required',
                'integer',
                Rule::exists('assets', 'id')->where('user_id', $this->user()->id),
            ],
            'recurrence_unit' => ['required', Rule::enum(RecurrenceUnit::class)],
            'recurrence_count' => ['required', 'integer', 'min:1', 'max:365'],
            'description' => ['nullable', 'string', 'max:2000'],
            'start_date' => ['nullable', 'date'],
        ];
    }
}
