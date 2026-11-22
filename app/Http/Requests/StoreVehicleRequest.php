<?php

namespace App\Http\Requests;

use App\Enums\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
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
            'type' => ['required', Rule::enum(VehicleType::class)],
            'plate' => ['nullable', 'string', 'max:20', 'required_unless:type,sepeda'],
            'make' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'battery_capacity_kwh' => [
                'nullable',
                'numeric',
                'min:0.01',
                'required_if:type,mobil_listrik',
            ],
        ];
    }
}
