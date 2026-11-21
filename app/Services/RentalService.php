<?php

namespace App\Services;

use App\Contracts\BillingCalculator;
use App\Contracts\RentalManager;
use App\DTOs\ExtensionQuote;
use App\DTOs\RentalQuote;
use App\DTOs\RentalQuoteRequest;
use App\Enums\RentalStatus;
use App\Models\Garage;
use App\Models\Rental;
use App\Models\RentalExtension;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RentalService implements RentalManager
{
    public function __construct(
        private BillingCalculator $billing,
        private PaymentProofService $paymentProofs,
    ) {}

    public function quote(Vehicle $vehicle, Garage $garage, RentalQuoteRequest $request): RentalQuote
    {
        $this->assertCompatible($vehicle, $garage);

        $capacityKwh = $request->batteryCapacityKwh > 0
            ? $request->batteryCapacityKwh
            : (float) ($vehicle->battery_capacity_kwh ?? 0);

        $result = $this->billing->calculate(
            rentalType: $request->rentalType->value,
            startDate: $request->startDate,
            days: $request->effectiveDays(),
            dailyRate: (float) $garage->daily_rate,
            isEV: $vehicle->isEV(),
            capacityKwh: $capacityKwh,
            remainingPct: $request->remainingPct,
            electricityRateKwh: (float) ($garage->electricity_rate_kwh ?? 0),
        );

        return RentalQuote::fromBillingResult($result);
    }

    public function create(
        User $tenant,
        Vehicle $vehicle,
        Garage $garage,
        RentalQuoteRequest $request,
        UploadedFile $paymentProof,
    ): Rental {
        if (! $tenant->canLogin()) {
            throw new RuntimeException('Akun penyewa belum diverifikasi.');
        }

        $this->assertCompatible($vehicle, $garage);

        return DB::transaction(function () use ($tenant, $vehicle, $garage, $request, $paymentProof) {
            $quote = $this->quote($vehicle, $garage, $request);

            $this->assertGarageAvailable($garage, $request->startDate, $quote->endDate);

            $capacityKwh = $request->batteryCapacityKwh > 0
                ? $request->batteryCapacityKwh
                : (float) ($vehicle->battery_capacity_kwh ?? 0);

            $rental = Rental::create([
                'user_id' => $tenant->id,
                'vehicle_id' => $vehicle->id,
                'garage_id' => $garage->id,
                'rental_type' => $request->rentalType->value,
                'start_date' => $request->startDate->toDateString(),
                'end_date' => $quote->endDate->toDateString(),
                'daily_rate_snapshot' => $garage->daily_rate,
                'rent_fee' => $quote->rentFee,
                'electricity_fee' => $quote->electricityFee,
                'total' => $quote->total,
                'status' => RentalStatus::Active->value,
                'battery_capacity_kwh' => $vehicle->isEV() ? $capacityKwh : null,
                'remaining_pct' => $vehicle->isEV() ? $request->remainingPct : null,
                'estimated_topup_kwh' => $quote->estimatedTopupKwh,
                'unit_kwh_rate_snapshot' => $vehicle->isEV()
                    ? $garage->electricity_rate_kwh
                    : null,
                'start_date_anchor' => $request->startDate->toDateString(),
            ]);

            $this->paymentProofs->storeForRental($rental, $paymentProof);

            return $rental;
        });
    }

    public function quoteExtension(Rental $rental): ExtensionQuote
    {
        $extensionNumber = $rental->extensions()->count() + 1;
        $previousEnd = Carbon::parse($rental->end_date);

        if ($rental->rentalTypeEnum()->isMonthly()) {
            $anchor = Carbon::parse($rental->start_date_anchor ?? $rental->start_date);
            $newEnd = $this->billing->extensionEndDate($anchor, $extensionNumber);
            $fee = $this->billing->calcMonthlyRentFee((float) $rental->daily_rate_snapshot);
        } else {
            $newEnd = $previousEnd->copy()->addDay();
            $fee = $this->billing->calcDailyRentFee(1, (float) $rental->daily_rate_snapshot);
        }

        return new ExtensionQuote($extensionNumber, $previousEnd, $newEnd, $fee);
    }

    public function extend(
        Rental $rental,
        User $reviewer,
        UploadedFile $paymentProof,
    ): RentalExtension {
        return DB::transaction(function () use ($rental, $reviewer, $paymentProof) {
            $quote = $this->quoteExtension($rental);
            $garage = $rental->garage()->firstOrFail();

            $this->assertGarageAvailable(
                $garage,
                $quote->previousEnd->copy()->addDay(),
                $quote->newEnd,
                $rental->id,
            );

            $extension = RentalExtension::create([
                'rental_id' => $rental->id,
                'previous_end' => $quote->previousEnd->toDateString(),
                'new_end' => $quote->newEnd->toDateString(),
                'rent_fee' => $quote->fee,
                'created_by' => $reviewer->id,
            ]);

            $rental->update([
                'end_date' => $quote->newEnd->toDateString(),
                'status' => RentalStatus::Extended->value,
            ]);

            $this->paymentProofs->storeForExtension($rental, $extension, $paymentProof);

            return $extension;
        });
    }

    private function assertCompatible(Vehicle $vehicle, Garage $garage): void
    {
        if ($vehicle->garageType() !== $garage->typeEnum()) {
            throw new RuntimeException('Garasi tidak kompatibel dengan kendaraan ini.');
        }
    }

    private function assertGarageAvailable(
        Garage $garage,
        Carbon $startDate,
        Carbon $endDate,
        ?int $ignoreRentalId = null,
    ): void {
        $conflict = Rental::query()
            ->where('garage_id', $garage->id)
            ->whereIn('status', [
                RentalStatus::Active->value,
                RentalStatus::Extended->value,
            ])
            ->where('start_date', '<=', $endDate->toDateString())
            ->where('end_date', '>=', $startDate->toDateString())
            ->when(
                $ignoreRentalId !== null,
                fn ($query) => $query->where('id', '!=', $ignoreRentalId),
            )
            ->exists();

        if ($conflict) {
            throw new RuntimeException('Garasi sudah dipesan untuk rentang tanggal tersebut.');
        }
    }
}
