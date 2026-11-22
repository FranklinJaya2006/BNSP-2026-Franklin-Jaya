<?php

namespace App\Http\Requests;

use App\DTOs\RentalQuoteRequest;
use App\Enums\RentalType;
use App\Services\BillingService;
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
        $maxDays = app(BillingService::class)->maxDailyDays();
        $lastStartDate = Carbon::today()->addDays(RentalQuoteRequest::MAX_ADVANCE_DAYS)->toDateString();

        return [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'garage_id' => ['required', 'integer', 'exists:garages,id'],
            'rental_type' => ['required', Rule::enum(RentalType::class)],
            'start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:'.$lastStartDate],
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
