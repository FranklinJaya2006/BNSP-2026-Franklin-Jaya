<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Penyewa = 'penyewa';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Penyewa => 'Penyewa',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
