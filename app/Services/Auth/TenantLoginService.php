<?php

namespace App\Services\Auth;

use App\DTOs\LoginAttemptResult;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TenantLoginService
{
    /** Cek kata sandi dulu, baru cek status verifikasi. */
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
