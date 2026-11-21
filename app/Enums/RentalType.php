<?php

namespace App\Enums;

enum RentalType: string
{
    case Harian = 'harian';
    case Bulanan = 'bulanan';

    public function label(): string
    {
        return match ($this) {
            self::Harian => 'Harian',
            self::Bulanan => 'Bulanan',
        };
    }

    public function isMonthly(): bool
    {
        return $this === self::Bulanan;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
