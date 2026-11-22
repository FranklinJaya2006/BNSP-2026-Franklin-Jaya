<?php

namespace Database\Seeders;

use App\Enums\VehicleType;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleSeeder extends Seeder
{
    public const string BICYCLE_MODEL = 'Cascade Demo';

    public const string MOTORCYCLE_PLATE = 'DD 9101 DM';

    public const string GASOLINE_CAR_PLATE = 'DD 9102 DM';

    public const string ELECTRIC_CAR_PLATE = 'DD 9103 DM';

    public function run(): void
    {
        DB::transaction(function (): void {
            $tenant = User::where('email', UserSeeder::DEMO_TENANT_EMAIL)->lockForUpdate()->firstOrFail();

            if (! $tenant->isApproved() || $tenant->isAdmin()) {
                $this->command?->warn('Kendaraan demo dilewati karena status akun penyewa sudah berubah.');

                return;
            }

            foreach ([
                [VehicleType::Sepeda, null, 'Polygon', self::BICYCLE_MODEL, null],
                [VehicleType::SepedaMotor, self::MOTORCYCLE_PLATE, 'Honda', 'Vario Demo', null],
                [VehicleType::MobilBensin, self::GASOLINE_CAR_PLATE, 'Toyota', 'Avanza Demo', null],
                [VehicleType::MobilListrik, self::ELECTRIC_CAR_PLATE, 'Hyundai', 'Ioniq 5 Demo', 58],
                [VehicleType::MobilListrik, 'DD 9104 DM', 'Wuling', 'Air ev Demo', 26.7],
            ] as [$type, $plate, $make, $model, $battery]) {
                $identity = ['user_id' => $tenant->id, 'type' => $type->value, 'plate' => $plate];
                if ($plate === null) {
                    $identity['model'] = $model;
                }

                Vehicle::firstOrCreate($identity, [
                    'make' => $make,
                    'model' => $model,
                    'battery_capacity_kwh' => $battery,
                ]);
            }
        });
    }
}
