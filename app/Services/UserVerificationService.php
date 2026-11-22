<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;

/** Simpan keputusan admin di akun penyewa. */
class UserVerificationService
{
    public function approve(User $tenant, ?User $reviewer = null): User
    {
        $tenant->update([
            'status' => UserStatus::Approved->value,
            'approved_by' => $reviewer?->id,
            'approved_at' => now(),
            'rejection_notes' => null,
        ]);

        return $tenant->refresh();
    }

    public function reject(User $tenant, ?User $reviewer = null, ?string $notes = null): User
    {
        $tenant->update([
            'status' => UserStatus::Rejected->value,
            'approved_by' => $reviewer?->id,
            'approved_at' => now(),
            'rejection_notes' => $notes,
        ]);

        return $tenant->refresh();
    }
}
