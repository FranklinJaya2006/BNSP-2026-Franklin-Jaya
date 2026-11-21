<?php

namespace Tests\Unit;

use App\Services\BillingService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * Unit test untuk BillingService.
 * Mencakup 5 kasus kalender utama PRD §5.3 termasuk Februari & tahun kabisat.
 */
class BillingServiceTest extends TestCase
{
    private BillingService $billing;

    protected function setUp(): void
    {
        $this->billing = new BillingService();
    }

    // =========================================================================
    // §5.3 Kasus kalender dan tanggal akhir
    // =========================================================================

    /**
     * Kasus 1: Sewa harian biasa — 7 hari mulai 10 Mar
     * end_date = 10 Mar + 6 hari = 16 Mar (inklusif kedua ujung)
     */
    public function test_daily_7_days_normal_month(): void
    {
        $start  = Carbon::parse('2026-03-10');
        $end    = $this->billing->dailyEndDate($start, 7);

        $this->assertEquals('2026-03-16', $end->toDateString());
    }

    /**
     * Kasus 2: Sewa harian melewati akhir bulan — 10 hari mulai 25 Jan
     * end_date = 25 Jan + 9 hari = 3 Feb
     */
    public function test_daily_spans_month_boundary(): void
    {
        $start = Carbon::parse('2026-01-25');
        $end   = $this->billing->dailyEndDate($start, 10);

        $this->assertEquals('2026-02-03', $end->toDateString());
    }

    /**
     * Kasus 3: Sewa bulanan mulai 1 Februari tahun biasa (28 hari)
     * end_date = 28 Feb (addCalendarMonth(1 Feb, 1) = 1 Mar, -1 hari = 28 Feb)
     */
    public function test_monthly_start_february_normal_year(): void
    {
        $start = Carbon::parse('2025-02-01'); // 2025 bukan kabisat
        $end   = $this->billing->monthlyEndDate($start);

        $this->assertEquals('2025-02-28', $end->toDateString());
    }

    /**
     * Kasus 4: Sewa bulanan mulai 1 Februari tahun KABISAT (29 hari)
     * end_date = 29 Feb 2024
     */
    public function test_monthly_start_february_leap_year(): void
    {
        $start = Carbon::parse('2024-02-01'); // 2024 adalah tahun kabisat
        $end   = $this->billing->monthlyEndDate($start);

        $this->assertEquals('2024-02-29', $end->toDateString());
    }

    /**
     * Kasus 5: Clamp akhir bulan — mulai 31 Jan, bulanan
     * addCalendarMonth(31 Jan, 1) dengan noOverflow = 28 Feb (normal) atau 29 Feb (kabisat)
     * end_date = 28 Feb - 1 hari = 27 Feb (bukan meluber ke Mar!)
     */
    public function test_monthly_start_31_jan_no_overflow(): void
    {
        $start = Carbon::parse('2025-01-31'); // bukan kabisat
        $end   = $this->billing->monthlyEndDate($start);

        // addMonthsNoOverflow(1) dari 31 Jan → 28 Feb; -1 hari → 27 Feb
        $this->assertEquals('2025-02-27', $end->toDateString());
        // Memastikan TIDAK meluber ke Maret
        $this->assertNotEquals('2025-03-02', $end->toDateString());
    }

    /**
     * Kasus 6: Clamp akhir bulan — mulai 31 Jan tahun kabisat
     * addMonthsNoOverflow(1) dari 31 Jan 2024 → 29 Feb 2024; -1 hari → 28 Feb
     */
    public function test_monthly_start_31_jan_leap_year_no_overflow(): void
    {
        $start = Carbon::parse('2024-01-31'); // 2024 kabisat
        $end   = $this->billing->monthlyEndDate($start);

        $this->assertEquals('2024-02-28', $end->toDateString());
    }

    // =========================================================================
    // §5.1 Perpanjangan bulanan — drift prevention via anchor
    // =========================================================================

    /**
     * Perpanjangan ke-1 dan ke-2 dari anchor 31 Jan harus konsisten.
     * ext1: addCalendarMonth(31 Jan, 2) - 1 = 27 Feb atau 28 Feb kabisat
     * Tidak boleh drift ke tanggal berbeda karena akumulasi.
     */
    public function test_extension_anchor_prevents_drift(): void
    {
        $anchor = Carbon::parse('2024-01-31');

        $ext1End = $this->billing->extensionEndDate($anchor, 1); // anchor+2bln - 1hr
        $ext2End = $this->billing->extensionEndDate($anchor, 2); // anchor+3bln - 1hr

        // Ext1: addMonthsNoOverflow(2) dari 31 Jan 2024 = 31 Mar 2024, -1 hari = 30 Mar
        $this->assertEquals('2024-03-30', $ext1End->toDateString());

        // Ext2: addMonthsNoOverflow(3) dari 31 Jan 2024 = 30 Apr 2024, -1 hari = 29 Apr
        $this->assertEquals('2024-04-29', $ext2End->toDateString());
    }

    // =========================================================================
    // §5.2 Kalkulasi biaya
    // =========================================================================

    public function test_daily_rent_fee(): void
    {
        $fee = $this->billing->calcDailyRentFee(7, 50000);
        $this->assertEquals(350000, $fee);
    }

    public function test_monthly_rent_fee_is_25x_daily(): void
    {
        $fee = $this->billing->calcMonthlyRentFee(50000);
        $this->assertEquals(1250000, $fee);
    }

    public function test_ev_topup_kwh(): void
    {
        // 60 kWh, baterai 25% → isi 45 kWh
        $topup = $this->billing->calcTopupKwh(60, 25);
        $this->assertEquals(45.0, $topup);
    }

    public function test_ev_electricity_fee(): void
    {
        // 45 kWh × Rp2.500 = Rp112.500
        $fee = $this->billing->calcElectricityFee(45, 2500);
        $this->assertEquals(112500, $fee);
    }

    /**
     * Contoh lengkap dari PRD §5.2:
     * Garasi E, daily_rate=50.000, electricity_rate=2.500/kWh
     * EV 60kWh, baterai 25%
     * Bulanan = 1.250.000, listrik = 112.500, total = 1.362.500
     */
    public function test_full_ev_monthly_calculation_matches_prd_example(): void
    {
        $result = $this->billing->calculate(
            rentalType:         'bulanan',
            startDate:          Carbon::parse('2026-01-01'),
            days:               1,
            dailyRate:          50000,
            isEV:               true,
            capacityKwh:        60,
            remainingPct:       25,
            electricityRateKwh: 2500,
        );

        $this->assertEquals(1250000, $result['rent_fee']);
        $this->assertEquals(45.0,    $result['estimated_topup_kwh']);
        $this->assertEquals(112500,  $result['electricity_fee']);
        $this->assertEquals(1362500, $result['total']);
    }

    // =========================================================================
    // §5.4 Anti-double-booking
    // =========================================================================

    public function test_overlap_detection(): void
    {
        $this->assertTrue($this->billing->hasOverlap(
            Carbon::parse('2026-01-01'), Carbon::parse('2026-01-10'),
            Carbon::parse('2026-01-05'), Carbon::parse('2026-01-15'),
        ));
    }

    public function test_no_overlap_adjacent_dates(): void
    {
        // Tanggal akhir = 10 Jan; sewa berikut mulai 11 Jan → tidak overlap
        $this->assertFalse($this->billing->hasOverlap(
            Carbon::parse('2026-01-01'), Carbon::parse('2026-01-10'),
            Carbon::parse('2026-01-11'), Carbon::parse('2026-01-20'),
        ));
    }

    public function test_end_date_is_occupied(): void
    {
        // Sewa berakhir 10 Jan; sewa baru mulai 10 Jan → OVERLAP (end inklusif)
        $this->assertTrue($this->billing->hasOverlap(
            Carbon::parse('2026-01-01'), Carbon::parse('2026-01-10'),
            Carbon::parse('2026-01-10'), Carbon::parse('2026-01-20'),
        ));
    }
}
