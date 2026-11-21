<?php

namespace App\Services;

use App\Enums\GarageType;
use App\Models\Garage;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GarageService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Garage
    {
        $type = GarageType::from($data['type']);

        return DB::transaction(function () use ($data, $type): Garage {
            return Garage::create([
                'code' => $this->resolveCode($data['code'] ?? null, $type),
                'type' => $type->value,
                'daily_rate' => $data['daily_rate'],
                'electricity_rate_kwh' => $type === GarageType::Ev
                    ? ($data['electricity_rate_kwh'] ?? null)
                    : null,
                'is_active' => true,
            ]);
        });
    }

    private function resolveCode(?string $code, GarageType $type): string
    {
        if ($code !== null && trim($code) !== '') {
            return strtoupper(trim($code));
        }

        $prefix = $type->codePrefix();
        $lastCode = Garage::query()
            ->where('type', $type->value)
            ->where('code', 'like', $prefix.'%')
            ->orderByDesc('code')
            ->lockForUpdate()
            ->value('code');

        $nextNumber = $lastCode !== null ? ((int) substr($lastCode, 1)) + 1 : 1;

        if ($nextNumber > 999) {
            throw new RuntimeException('Kode garasi untuk tipe ini sudah mencapai batas 999.');
        }

        return $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Garage $garage, array $data): Garage
    {
        $garage->update([
            'daily_rate' => $data['daily_rate'],
            'electricity_rate_kwh' => $garage->typeEnum() === GarageType::Ev
                ? ($data['electricity_rate_kwh'] ?? $garage->electricity_rate_kwh)
                : null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return $garage->refresh();
    }

    /**
     * @return Collection<int, Garage>
     */
    public function availableFor(Vehicle $vehicle, Carbon $startDate, Carbon $endDate): Collection
    {
        return Garage::query()
            ->where('type', $vehicle->garageType()->value)
            ->where('is_active', true)
            ->get()
            ->filter(fn (Garage $garage): bool => $garage->isAvailableFor(
                $startDate->toDateString(),
                $endDate->toDateString(),
            ))
            ->values();
    }
}
