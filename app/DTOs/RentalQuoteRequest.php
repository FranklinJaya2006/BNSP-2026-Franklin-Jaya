<?php

namespace App\DTOs;

use App\Enums\RentalType;
use Carbon\Carbon;

final readonly class RentalQuoteRequest
{
    public function __construct(
        public RentalType $rentalType,
        public Carbon $startDate,
        public int $days = 1,
        public float $batteryCapacityKwh = 0,
        public float $remainingPct = 0,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $rentalType = RentalType::from($data['rental_type']);

        return new self(
            rentalType: $rentalType,
            startDate: Carbon::parse($data['start_date']),
            days: $rentalType->isMonthly() ? 1 : (int) ($data['days'] ?? 1),
            batteryCapacityKwh: (float) ($data['battery_capacity_kwh'] ?? 0),
            remainingPct: (float) ($data['remaining_pct'] ?? 0),
        );
    }

    public function effectiveDays(): int
    {
        return $this->rentalType->isMonthly() ? 1 : max(1, $this->days);
    }
}
