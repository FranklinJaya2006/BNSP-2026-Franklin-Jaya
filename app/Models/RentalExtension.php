<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalExtension extends Model
{
    protected $fillable = [
        'rental_id', 'previous_end', 'new_end', 'rent_fee', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'previous_end' => 'date',
            'new_end' => 'date',
            'rent_fee' => 'decimal:2',
        ];
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentProofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class, 'extension_id');
    }
}
