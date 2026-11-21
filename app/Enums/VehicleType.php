<?php

namespace App\Enums;

enum VehicleType: string
{
    case Sepeda = 'sepeda';
    case SepedaMotor = 'sepeda_motor';
    case MobilBensin = 'mobil_bensin';
    case MobilListrik = 'mobil_listrik';

    public function label(): string
    {
        return match ($this) {
            self::Sepeda => 'Sepeda',
            self::SepedaMotor => 'Sepeda Motor',
            self::MobilBensin => 'Mobil Bensin',
            self::MobilListrik => 'Mobil Listrik (EV)',
        };
    }

    public function requiresPlate(): bool
    {
        return $this !== self::Sepeda;
    }

    public function isElectric(): bool
    {
        return $this === self::MobilListrik;
    }

    public function garageType(): GarageType
    {
        return match ($this) {
            self::Sepeda, self::SepedaMotor => GarageType::Sepeda,
            self::MobilBensin => GarageType::Mobil,
            self::MobilListrik => GarageType::Ev,
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
