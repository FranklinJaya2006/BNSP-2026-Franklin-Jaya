<?php

namespace App\DTOs;

use Carbon\Carbon;

final readonly class RentalQuote
{
    public function __construct(
        public Carbon $endDate,
        public float $rentFee,
        public float $electricityFee,
        public float $total,
        public ?float $estimatedTopupKwh,
    ) {}

    /**
     * @param  array{
     *   end_date: Carbon,
     *   rent_fee: float,
     *   electricity_fee: float,
     *   total: float,
     *   estimated_topup_kwh: float|null,
     * }  $result
     */
    public static function fromBillingResult(array $result): self
    {
        return new self(
            endDate: $result['end_date'],
            rentFee: (float) $result['rent_fee'],
            electricityFee: (float) $result['electricity_fee'],
            total: (float) $result['total'],
            estimatedTopupKwh: $result['estimated_topup_kwh'] === null
                ? null
                : (float) $result['estimated_topup_kwh'],
        );
    }

    /**
     * @return array{
     *   end_date: Carbon,
     *   rent_fee: float,
     *   electricity_fee: float,
     *   total: float,
     *   estimated_topup_kwh: float|null,
     * }
     */
    public function toArray(): array
    {
        return [
            'end_date' => $this->endDate,
            'rent_fee' => $this->rentFee,
            'electricity_fee' => $this->electricityFee,
            'total' => $this->total,
            'estimated_topup_kwh' => $this->estimatedTopupKwh,
        ];
    }
}
