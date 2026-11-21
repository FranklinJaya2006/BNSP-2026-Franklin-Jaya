<?php

namespace App\Models;

use App\Enums\VerificationAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVerification extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'action',
        'notes',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => VerificationAction::class,
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
