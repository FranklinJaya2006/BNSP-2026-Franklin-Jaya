<?php

namespace App\Policies;

use App\Models\Rental;
use App\Models\User;

class RentalPolicy
{
    public function view(User $user, Rental $rental): bool
    {
        return $user->isAdmin() || $rental->user_id === $user->id;
    }

    public function extend(User $user, Rental $rental): bool
    {
        return $user->isAdmin() && $rental->isActive();
    }
}
