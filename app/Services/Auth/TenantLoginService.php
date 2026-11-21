<?php

namespace App\Services\Auth;

use App\DTOs\LoginAttemptResult;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TenantLoginService
{
    /**
     * Validasi kredensial tanpa langsung membuat sesi.
     * Status akun dicek setelah password terbukti benar, sehingga pesan
     * "password salah" tidak dipakai untuk akun yang masih pending/rejected.
     */
    public function validate(string $email, string $password): LoginAttemptResult
    {
        $user = User::query()->where('email', $email)->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            return LoginAttemptResult::invalidCredentials();
        }

        $status = $user->loginStatus();

        if (! $status->canLogin()) {
            return LoginAttemptResult::blocked($user, $status);
        }

        return LoginAttemptResult::success($user);
    }
}
