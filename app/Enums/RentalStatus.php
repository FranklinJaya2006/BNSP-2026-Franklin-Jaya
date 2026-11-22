<?php

namespace App\Enums;

enum RentalStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Extended = 'extended';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Completed => 'Selesai',
            self::Extended => 'Diperpanjang',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active || $this === self::Extended;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
