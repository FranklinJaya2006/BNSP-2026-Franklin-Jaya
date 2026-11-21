<?php

namespace App\Http\Requests;

use App\Contracts\BillingCalculator;
use App\Enums\RentalType;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviewRentalRequest extends FormRequest
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
        $startDate = Carbon::today();
        if ($this->filled('start_date') && Carbon::hasFormat((string) $this->input('start_date'), 'Y-m-d')) {
            $startDate = Carbon::parse((string) $this->input('start_date'));
        }

        $maxDays = app(BillingCalculator::class)->maxDailyDays($startDate);

        return [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'garage_id' => ['required', 'integer', 'exists:garages,id'],
            'rental_type' => ['required', Rule::enum(RentalType::class)],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'days' => [
                'required_if:rental_type,harian',
                'nullable',
                'integer',
                'min:1',
                'max:'.$maxDays,
            ],
            'battery_capacity_kwh' => ['nullable', 'numeric', 'min:0.01'],
            'remaining_pct' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }
}
