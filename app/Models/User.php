<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nik',
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'approved_by',
        'approved_at',
        'rejection_notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'approved_at' => 'datetime',
        ];
    }

    // --- JWT interface ---

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
            'status' => $this->status,
        ];
    }

    // --- Helpers ---

    public function roleEnum(): UserRole
    {
        return UserRole::tryFrom((string) $this->role) ?? UserRole::Penyewa;
    }

    public function statusEnum(): UserStatus
    {
        return UserStatus::tryFrom((string) $this->status) ?? UserStatus::Pending;
    }

    public function loginStatus(): UserStatus
    {
        return $this->statusEnum();
    }

    public function canLogin(): bool
    {
        return $this->loginStatus()->canLogin();
    }

    public function isAdmin(): bool
    {
        return $this->roleEnum()->isAdmin();
    }

    public function isApproved(): bool
    {
        return $this->statusEnum()->canLogin();
    }

    public function maskedNik(): string
    {
        return substr($this->nik, 0, 4).'************';
    }

    // --- Scopes ---

    public function scopeTenants(Builder $query): Builder
    {
        return $query->where('role', UserRole::Penyewa->value);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Approved->value);
    }

    // --- Relations ---

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
