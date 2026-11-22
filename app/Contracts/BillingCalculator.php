<?php

namespace App\Contracts;

use Carbon\Carbon;

interface BillingCalculator
{
    public function dailyEndDate(Carbon $startDate, int $days): Carbon;

    public function monthlyEndDate(Carbon $startDate): Carbon;

    public function extensionEndDate(Carbon $startAnchor, int $extensionNumber): Carbon;

    public function maxDailyDays(?Carbon $startDate = null): int;

    public function assertDailyDuration(Carbon $startDate, int $days): void;

    public function assertMonthlyDuration(int $months): void;

    public function calcDailyRentFee(int $days, float $dailyRate): float;

    public function calcMonthlyRentFee(float $dailyRate): float;

    public function calcTopupKwh(float $capacityKwh, float $remainingPct): float;

    public function calcElectricityFee(float $topupKwh, float $ratePerKwh): float;

    public function calcTotal(float $rentFee, float $electricityFee = 0): float;

    public function daysBetween(Carbon $start, Carbon $end): int;

    public function hasOverlap(
        Carbon $aStart,
        Carbon $aEnd,
        Carbon $bStart,
        Carbon $bEnd,
    ): bool;

    public function isValidNik(string $nik): bool;

    /**
     * @return array{
     *   end_date: Carbon,
     *   rent_fee: float,
     *   electricity_fee: float,
     *   total: float,
     *   estimated_topup_kwh: float|null,
     * }
     */
    public function calculate(
        string $rentalType,
        Carbon $startDate,
        int $days,
        float $dailyRate,
        bool $isEV = false,
        float $capacityKwh = 0,
        float $remainingPct = 0,
        float $electricityRateKwh = 0,
        int $months = 1,
    ): array;
}
