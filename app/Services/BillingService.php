<?php

namespace App\Services;

use App\Contracts\BillingCalculator;
use Carbon\Carbon;
use InvalidArgumentException;

/** Hitung durasi, biaya sewa, dan biaya isi baterai. */
class BillingService implements BillingCalculator
{
    // ---------------------------------------------------------------------------
    // §5.1  Periode sewa
    // ---------------------------------------------------------------------------

    /** Hitung hari pertama sebagai hari sewa. */
    public function dailyEndDate(Carbon $startDate, int $days): Carbon
    {
        return $startDate->copy()->addDays($days - 1);
    }

    /** Akhiri sewa sehari sebelum batas bulan berikutnya. */
    public function monthlyEndDate(Carbon $startDate): Carbon
    {
        return $this->addCalendarMonth($startDate, 1)->subDay();
    }

    /**
     * Hitung tanggal akhir perpanjangan bulanan ke-n.
     * Anchor = start_date asli; batas = addCalendarMonth(anchor, n+1) - 1 hari.
     * Ini mencegah drift pada akhir bulan (PRD §5.1).
     */
    public function extensionEndDate(Carbon $startAnchor, int $extensionNumber): Carbon
    {
        return $this->addCalendarMonth($startAnchor, $extensionNumber + 1)->subDay();
    }

    /** Pakai hari terakhir jika tanggal itu tidak ada di bulan tujuan. */
    public function addCalendarMonth(Carbon $date, int $months): Carbon
    {
        $result = $date->copy()->addMonthsNoOverflow($months);

        return $result;
    }

    /** Batasi sewa harian sampai 30 hari. */
    public function maxDailyDays(?Carbon $startDate = null): int
    {
        return 30;
    }

    public function assertDailyDuration(Carbon $startDate, int $days): void
    {
        $maxDays = $this->maxDailyDays($startDate);

        if ($days < 1 || $days > $maxDays) {
            throw new InvalidArgumentException(
                "Sewa harian maksimal {$maxDays} hari untuk tanggal mulai {$startDate->toDateString()}.",
            );
        }
    }

    public function assertMonthlyDuration(int $months): void
    {
        if ($months !== 1) {
            throw new InvalidArgumentException('Sewa bulanan hanya boleh satu bulan per transaksi.');
        }
    }

    // ---------------------------------------------------------------------------
    // §5.2  Harga dan energi
    // ---------------------------------------------------------------------------

    /**
     * Hitung biaya sewa harian.
     * rent_fee = days × daily_rate
     */
    public function calcDailyRentFee(int $days, float $dailyRate): float
    {
        return $days * $dailyRate;
    }

    /**
     * Hitung biaya sewa bulanan.
     * rent_fee = 25 × daily_rate  (PRD: bulanan = 25 × tarif harian)
     */
    public function calcMonthlyRentFee(float $dailyRate): float
    {
        return 25 * $dailyRate;
    }

    /**
     * Hitung energi yang perlu diisi untuk EV.
     * topup_kwh = capacity_kwh × (1 - remaining_pct/100)
     */
    public function calcTopupKwh(float $capacityKwh, float $remainingPct): float
    {
        if ($remainingPct < 0 || $remainingPct > 100) {
            throw new InvalidArgumentException('Sisa baterai harus berada di antara 0 dan 100 persen.');
        }

        return round($capacityKwh * (1 - $remainingPct / 100), 4);
    }

    /**
     * Hitung biaya listrik pengisian EV.
     * electricity_fee = topup_kwh × rate_per_kwh
     */
    public function calcElectricityFee(float $topupKwh, float $ratePerKwh): float
    {
        return round($topupKwh * $ratePerKwh, 2);
    }

    /**
     * Hitung total tagihan sewa (termasuk listrik EV jika ada).
     */
    public function calcTotal(float $rentFee, float $electricityFee = 0): float
    {
        return round($rentFee + $electricityFee, 2);
    }

    // ---------------------------------------------------------------------------
    // Helper: hitung hari antar dua tanggal (inklusif)
    // ---------------------------------------------------------------------------

    public function daysBetween(Carbon $start, Carbon $end): int
    {
        return (int) $start->diffInDays($end) + 1;
    }

    // ---------------------------------------------------------------------------
    // §5.4  Anti-double-booking
    // ---------------------------------------------------------------------------

    /**
     * Cek apakah dua rentang tanggal overlap.
     * Tanggal akhir dianggap terpakai (inklusif).
     */
    public function hasOverlap(
        Carbon $aStart, Carbon $aEnd,
        Carbon $bStart, Carbon $bEnd
    ): bool {
        return $aStart->lte($bEnd) && $aEnd->gte($bStart);
    }

    // ---------------------------------------------------------------------------
    // Validasi NIK (PRD §3.1)
    // ---------------------------------------------------------------------------

    public function isValidNik(string $nik): bool
    {
        return preg_match('/^\d{16}$/', $nik) === 1;
    }

    // ---------------------------------------------------------------------------
    // Kalkulasi lengkap untuk satu transaksi baru
    // ---------------------------------------------------------------------------

    /**
     * Hitung semua komponen biaya untuk sewa baru.
     *
     * @return array{
     *   end_date: Carbon,
     *   rent_fee: float,
     *   electricity_fee: float,
     *   total: float,
     *   estimated_topup_kwh: float|null,
     * }
     */
    public function calculate(
        string $rentalType,
        Carbon $startDate,
        int $days,
        float $dailyRate,
        bool $isEV = false,
        float $capacityKwh = 0,
        float $remainingPct = 0,
        float $electricityRateKwh = 0,
        int $months = 1,
    ): array {
        if ($rentalType === 'harian') {
            $this->assertDailyDuration($startDate, $days);
            $endDate = $this->dailyEndDate($startDate, $days);
            $rentFee = $this->calcDailyRentFee($days, $dailyRate);
        } else {
            $this->assertMonthlyDuration($months);
            $endDate = $this->monthlyEndDate($startDate);
            $rentFee = $this->calcMonthlyRentFee($dailyRate);
        }

        $topupKwh = null;
        $electricityFee = 0;

        if ($isEV && $capacityKwh > 0) {
            $topupKwh = $this->calcTopupKwh($capacityKwh, $remainingPct);
            $electricityFee = $this->calcElectricityFee($topupKwh, $electricityRateKwh);
        }

        return [
            'end_date' => $endDate,
            'rent_fee' => $rentFee,
            'electricity_fee' => $electricityFee,
            'total' => $this->calcTotal($rentFee, $electricityFee),
            'estimated_topup_kwh' => $topupKwh,
        ];
    }
}
