<?php

namespace App\Enums;

enum UserStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu verifikasi',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }

    public function canLogin(): bool
    {
        return $this === self::Approved;
    }

    public function loginBlockedMessage(?string $rejectionNotes = null): ?string
    {
        return match ($this) {
            self::Pending => 'Akun Anda masih menunggu verifikasi admin. Silakan coba lagi setelah disetujui.',
            self::Rejected => 'Akun Anda ditolak.'
                .($rejectionNotes ? ' Alasan: '.$rejectionNotes : ' Hubungi admin untuk informasi lebih lanjut.'),
            self::Approved => null,
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
