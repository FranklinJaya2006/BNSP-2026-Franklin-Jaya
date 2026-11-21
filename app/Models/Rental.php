<?php

namespace App\Models;

use App\Enums\RentalStatus;
use App\Enums\RentalType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rental extends Model
{
    protected $fillable = [
        'user_id', 'vehicle_id', 'garage_id', 'rental_type',
        'start_date', 'end_date', 'daily_rate_snapshot',
        'rent_fee', 'electricity_fee', 'total', 'status',
        'battery_capacity_kwh', 'remaining_pct', 'estimated_topup_kwh',
        'unit_kwh_rate_snapshot', 'start_date_anchor',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'start_date_anchor' => 'date',
            'daily_rate_snapshot' => 'decimal:2',
            'rent_fee' => 'decimal:2',
            'electricity_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'battery_capacity_kwh' => 'decimal:2',
            'remaining_pct' => 'decimal:2',
            'estimated_topup_kwh' => 'decimal:2',
            'unit_kwh_rate_snapshot' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function garage(): BelongsTo
    {
        return $this->belongsTo(Garage::class);
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(RentalExtension::class);
    }

    public function paymentProofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
    }

    public function rentalTypeEnum(): RentalType
    {
        return RentalType::from($this->rental_type);
    }

    public function statusEnum(): RentalStatus
    {
        return RentalStatus::from($this->status);
    }

    public function isActive(): bool
    {
        return $this->statusEnum()->isActive();
    }

    public function startAnchor(): Carbon
    {
        return Carbon::parse($this->start_date_anchor ?? $this->start_date);
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForGarage(Builder $query, int $garageId): Builder
    {
        return $query->where('garage_id', $garageId);
    }

    public function scopeOverlapping(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query
            ->where('start_date', '<=', $endDate->toDateString())
            ->where('end_date', '>=', $startDate->toDateString());
    }
}
