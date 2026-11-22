<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGarageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'daily_rate' => ['required', 'numeric', 'min:0'],
            'electricity_rate_kwh' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
