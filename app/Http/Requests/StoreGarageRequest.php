<?php

namespace App\Http\Requests;

use App\Enums\GarageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreGarageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $this->merge([
                'code' => strtoupper((string) $this->input('code')),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['nullable', Rule::prohibitedIf($this->integer('quantity', 1) > 1), 'string', 'max:10', 'unique:garages,code', 'regex:/^[SCE][0-9]{3}$/'],
            'quantity' => ['sometimes', 'required', 'integer', 'between:1,999'],
            'type' => ['required', Rule::enum(GarageType::class)],
            'daily_rate' => ['required', 'numeric', 'min:0'],
            'electricity_rate_kwh' => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:type,ev',
            ],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $type = GarageType::tryFrom((string) $this->input('type'));
                $code = (string) $this->input('code');

                if ($type === null || $code === '' || $code === null) {
                    return;
                }

                if (substr($code, 0, 1) !== $type->codePrefix()) {
                    $validator->errors()->add(
                        'code',
                        "Kode garasi tipe {$type->label()} harus diawali {$type->codePrefix()}.",
                    );
                }
            },
        ];
    }
}
