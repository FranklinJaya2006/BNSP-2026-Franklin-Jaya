<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
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
        $vehicle = Vehicle::find($this->route('id'));
        $plateRequired = $vehicle?->requiresPlate() ?? false;

        return [
            'plate' => [
                Rule::requiredIf($plateRequired),
                'nullable',
                'string',
                'max:20',
            ],
            'make' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'battery_capacity_kwh' => [
                'nullable',
                'numeric',
                'min:0.01',
                Rule::requiredIf($vehicle?->isEV() ?? false),
            ],
        ];
    }
}
