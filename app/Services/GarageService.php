<?php

namespace App\Services;

use App\Enums\GarageType;
use App\Models\Garage;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Buat kode garasi dan cari garasi yang kosong. */
class GarageService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Garage
    {
        return $this->createBatch($data, 1)->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, Garage>
     */
    public function createBatch(array $data, int $quantity): Collection
    {
        $manualCode = $data['code'] ?? null;
        if ($quantity < 1 || $quantity > 999 || ($quantity > 1 && filled($manualCode))) {
            throw ValidationException::withMessages(['quantity' => 'Masukkan jumlah 1–999. Kode manual hanya untuk satu garasi.']);
        }

        $type = GarageType::from($data['type']);

        return DB::transaction(function () use ($data, $type, $quantity, $manualCode): Collection {
            $attributes = [
                'type' => $type->value,
                'daily_rate' => $data['daily_rate'],
                'electricity_rate_kwh' => $type === GarageType::Ev
                    ? ($data['electricity_rate_kwh'] ?? null)
                    : null,
                'is_active' => true,
            ];

            if (filled($manualCode)) {
                return collect([Garage::create($attributes + ['code' => strtoupper(trim($manualCode))])]);
            }

            $prefix = $type->codePrefix();
            $lastCode = Garage::query()
                ->where('type', $type->value)
                ->where('code', 'like', $prefix.'%')
                ->orderByDesc('code')
                ->lockForUpdate()
                ->value('code');
            $lastNumber = (int) substr($lastCode ?? '', 1);

            if ($lastNumber + $quantity > 999) {
                $remaining = 999 - $lastNumber;
                throw ValidationException::withMessages(['quantity' => "Sisa nomor untuk tipe ini {$remaining} garasi."]);
            }

            return collect(range($lastNumber + 1, $lastNumber + $quantity))
                ->map(fn (int $number): Garage => Garage::create($attributes + ['code' => sprintf('%s%03d', $prefix, $number)]));
        }, 3);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Garage $garage, array $data): Garage
    {
        return DB::transaction(function () use ($garage, $data): Garage {
            $garage = Garage::query()->lockForUpdate()->findOrFail($garage->id);
            $garage->fill([
                'daily_rate' => $data['daily_rate'],
                'electricity_rate_kwh' => $garage->typeEnum() === GarageType::Ev
                    ? ($data['electricity_rate_kwh'] ?? $garage->electricity_rate_kwh)
                    : null,
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);

            if ($garage->isDirty(['daily_rate', 'electricity_rate_kwh']) && $garage->isOccupiedToday()) {
                throw ValidationException::withMessages([
                    'daily_rate' => 'Tarif tidak bisa diubah saat garasi sedang dipakai.',
                ]);
            }

            $garage->save();

            return $garage->refresh();
        });
    }

    /**
     * @return Collection<int, Garage>
     */
    public function availableFor(Vehicle $vehicle, Carbon $startDate, Carbon $endDate): Collection
    {
        return Garage::query()
            ->whereIn('type', array_column($vehicle->compatibleGarageTypes(), 'value'))
            ->where('is_active', true)
            ->get()
            ->filter(fn (Garage $garage): bool => $garage->isAvailableFor(
                $startDate->toDateString(),
                $endDate->toDateString(),
            ))
            ->values();
    }
}
