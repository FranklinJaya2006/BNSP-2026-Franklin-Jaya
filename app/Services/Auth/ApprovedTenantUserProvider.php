<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

/** Cek verifikasi saat login atau pulihkan sesi penyewa. */
class ApprovedTenantUserProvider extends EloquentUserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        $user = parent::retrieveById($identifier);

        return $user !== null && $this->isApproved($user) ? $user : null;
    }

    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials): ?Authenticatable
    {
        $user = parent::retrieveByCredentials($credentials);

        return $user !== null && $this->isApproved($user) ? $user : null;
    }

    public function retrieveByToken($identifier, #[\SensitiveParameter] $token): ?Authenticatable
    {
        $user = parent::retrieveByToken($identifier, $token);

        return $user !== null && $this->isApproved($user) ? $user : null;
    }

    private function isApproved(Authenticatable $user): bool
    {
        return ! $user instanceof User || $user->canLogin();
    }
}
