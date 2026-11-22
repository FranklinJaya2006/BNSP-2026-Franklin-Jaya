<?php

namespace App\DTOs;

use App\Enums\UserStatus;
use App\Models\User;

final readonly class LoginAttemptResult
{
    private function __construct(
        public bool $success,
        public ?User $user,
        public ?UserStatus $status,
    ) {}

    public static function success(User $user): self
    {
        return new self(true, $user, null);
    }

    public static function invalidCredentials(): self
    {
        return new self(false, null, null);
    }

    public static function blocked(User $user, UserStatus $status): self
    {
        return new self(false, $user, $status);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isBlocked(): bool
    {
        return ! $this->success && $this->user !== null && $this->status !== null;
    }
}
