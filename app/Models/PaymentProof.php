<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentProof extends Model
{
    protected $fillable = [
        'rental_id', 'extension_id', 'object_key', 'mime_type', 'file_size', 'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
        ];
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function extension(): BelongsTo
    {
        return $this->belongsTo(RentalExtension::class, 'extension_id');
    }
}
