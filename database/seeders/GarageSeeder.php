<?php

namespace Database\Seeders;

use App\Enums\GarageType;
use App\Models\Garage;
use Illuminate\Database\Seeder;

class GarageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['S001', GarageType::Sepeda, 10_000, null, true],
            ['S002', GarageType::Sepeda, 10_000, null, true],
            ['C001', GarageType::Mobil, 35_000, null, true],
            ['C002', GarageType::Mobil, 35_000, null, true],
            ['C003', GarageType::Mobil, 35_000, null, false],
            ['E001', GarageType::Ev, 50_000, 2_500, true],
            ['E002', GarageType::Ev, 50_000, 2_500, true],
        ] as [$code, $type, $dailyRate, $electricityRate, $active]) {
            Garage::firstOrCreate(['code' => $code], [
                'type' => $type->value,
                'daily_rate' => $dailyRate,
                'electricity_rate_kwh' => $electricityRate,
                'is_active' => $active,
            ]);
        }
    }
}
