<?php

namespace Database\Seeders;

use App\DTOs\RentalQuoteRequest;
use App\Enums\RentalStatus;
use App\Enums\RentalType;
use App\Enums\VehicleType;
use App\Models\Garage;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\RentalService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RentalSeeder extends Seeder
{
    public function run(RentalService $rentals): void
    {
        $proofPaths = [];

        try {
            DB::transaction(function () use ($rentals, &$proofPaths): void {
                $tenant = User::where('email', UserSeeder::DEMO_TENANT_EMAIL)->lockForUpdate()->firstOrFail();
                $admin = User::where('email', UserSeeder::DEMO_ADMIN_EMAIL)->firstOrFail();

                if (! $tenant->isApproved() || $tenant->isAdmin() || ! $admin->isAdmin() || ! $admin->canLogin()) {
                    $this->command?->warn('Sewa demo dilewati karena status akun demo sudah berubah.');

                    return;
                }

                $today = Carbon::today();
                $startedYesterday = $today->copy()->subDay();
                foreach ([
                    [VehicleType::Sepeda, null, 'S001', new RentalQuoteRequest(RentalType::Harian, $today->copy()->subWeek(), 2), RentalStatus::Completed],
                    [VehicleType::SepedaMotor, VehicleSeeder::MOTORCYCLE_PLATE, 'S001', new RentalQuoteRequest(RentalType::Harian, $startedYesterday, 4), RentalStatus::Active],
                    [VehicleType::MobilBensin, VehicleSeeder::GASOLINE_CAR_PLATE, 'C001', new RentalQuoteRequest(RentalType::Bulanan, $startedYesterday), RentalStatus::Extended],
                    [VehicleType::MobilListrik, VehicleSeeder::ELECTRIC_CAR_PLATE, 'E001', new RentalQuoteRequest(RentalType::Harian, $startedYesterday, 3, remainingPct: 35), RentalStatus::Active],
                ] as [$type, $plate, $code, $request, $status]) {
                    $vehicleQuery = Vehicle::ownedBy($tenant->id)->where('type', $type->value)->where('plate', $plate);
                    if ($plate === null) {
                        $vehicleQuery->where('model', VehicleSeeder::BICYCLE_MODEL);
                    }

                    $vehicle = $vehicleQuery->firstOrFail();
                    $garage = Garage::where('code', $code)->firstOrFail();
                    $this->seedRental($rentals, $tenant, $admin, $vehicle, $garage, $request, $status, $proofPaths);
                }
            });
        } catch (Throwable $exception) {
            if ($proofPaths !== []) {
                Storage::disk('private')->delete($proofPaths);
            }

            throw $exception;
        }
    }

    /** @param list<string> $proofPaths */
    private function seedRental(
        RentalService $rentals,
        User $tenant,
        User $admin,
        Vehicle $vehicle,
        Garage $garage,
        RentalQuoteRequest $request,
        RentalStatus $status,
        array &$proofPaths,
    ): void {
        if ($vehicle->rentals()->ownedBy($tenant->id)->forGarage($garage->id)->where('rental_type', $request->rentalType->value)->exists()) {
            return;
        }

        $end = $rentals->quote($vehicle, $garage, $request)->endDate;
        $finalEnd = $status === RentalStatus::Extended ? $end->copy()->addDays(3) : $end;

        if (! $garage->is_active || ! $garage->isAvailableFor($request->startDate->toDateString(), $finalEnd->toDateString())
            || $vehicle->rentals()->overlapping($request->startDate, $finalEnd)->whereIn('status', [RentalStatus::Active->value, RentalStatus::Extended->value])->exists()) {
            $this->command?->warn("Sewa demo untuk {$vehicle->model} dilewati karena jadwal bentrok atau garasi {$garage->code} nonaktif.");

            return;
        }

        $rental = $rentals->create($tenant, $vehicle, $garage, $request, $this->demoProof($proofPaths));

        if ($status === RentalStatus::Extended) {
            $rentals->extend($rental, $admin, $this->demoProof($proofPaths), $finalEnd, $end);
        }

        if ($status === RentalStatus::Completed) {
            $rental->update(['status' => $status->value]);
        }
    }

    /** @param list<string> $proofPaths */
    private function demoProof(array &$proofPaths): UploadedFile
    {
        $proof = UploadedFile::fake()
            ->createWithContent('bukti-bayar-demo.png', file_get_contents(__DIR__.'/bukti-bayar-demo.png'));
        $proofPaths[] = $proof->hashName('payment-proofs');

        return $proof;
    }
}
