<?php

namespace App\Enums;

enum GarageType: string
{
    case Sepeda = 'sepeda';
    case Mobil = 'mobil';
    case Ev = 'ev';

    public function label(): string
    {
        return match ($this) {
            self::Sepeda => 'Sepeda',
            self::Mobil => 'Mobil Bensin',
            self::Ev => 'Mobil Listrik (EV)',
        };
    }

    public function codePrefix(): string
    {
        return match ($this) {
            self::Sepeda => 'S',
            self::Mobil => 'C',
            self::Ev => 'E',
        };
    }

    public static function fromCodePrefix(string $prefix): ?self
    {
        return match (strtoupper($prefix)) {
            'S' => self::Sepeda,
            'C' => self::Mobil,
            'E' => self::Ev,
            default => null,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
