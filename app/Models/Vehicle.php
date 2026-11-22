<?php

namespace App\Models;

use App\Enums\GarageType;
use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'user_id', 'type', 'plate', 'make', 'model', 'battery_capacity_kwh',
    ];

    protected function casts(): array
    {
        return [
            'battery_capacity_kwh' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function typeEnum(): VehicleType
    {
        return VehicleType::from($this->type);
    }

    public function garageType(): GarageType
    {
        return $this->typeEnum()->garageType();
    }

    /** @return list<GarageType> */
    public function compatibleGarageTypes(): array
    {
        return $this->isEV() ? [GarageType::Mobil, GarageType::Ev] : [$this->garageType()];
    }

    /** Tipe garasi yang kompatibel dengan kendaraan ini */
    public function compatibleGarageType(): string
    {
        return $this->garageType()->value;
    }

    public function isEV(): bool
    {
        return $this->typeEnum()->isElectric();
    }

    public function requiresPlate(): bool
    {
        return $this->typeEnum()->requiresPlate();
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
