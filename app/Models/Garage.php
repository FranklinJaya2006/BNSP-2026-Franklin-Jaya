<?php

namespace App\Models;

use App\Enums\GarageType;
use App\Enums\RentalStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Garage extends Model
{
    protected $fillable = [
        'code', 'type', 'daily_rate', 'electricity_rate_kwh', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'daily_rate' => 'decimal:2',
            'electricity_rate_kwh' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function typeEnum(): GarageType
    {
        return GarageType::from($this->type);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, GarageType $type): Builder
    {
        return $query->where('type', $type->value);
    }

    public function isOccupiedToday(): bool
    {
        $today = Carbon::today();

        return $this->rentals()
            ->whereIn('status', [RentalStatus::Active->value, RentalStatus::Extended->value])
            ->overlapping($today, $today)
            ->exists();
    }

    /**
     * Cek apakah garasi available untuk rentang tanggal tertentu.
     */
    public function isAvailableFor(string $startDate, string $endDate, ?int $excludeRentalId = null): bool
    {
        $query = $this->rentals()
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate);

        if ($excludeRentalId) {
            $query->where('id', '!=', $excludeRentalId);
        }

        return $query->doesntExist();
    }
}
