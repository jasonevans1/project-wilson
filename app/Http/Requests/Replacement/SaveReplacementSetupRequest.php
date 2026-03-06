<?php

namespace App\Http\Requests\Replacement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaveReplacementSetupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->asset->user_id === Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'expectedLifespanYears' => ['required', 'integer', 'min:1', 'max:100'],
            'installDate' => ['required', 'date', 'before_or_equal:today'],
        ];
    }
}
