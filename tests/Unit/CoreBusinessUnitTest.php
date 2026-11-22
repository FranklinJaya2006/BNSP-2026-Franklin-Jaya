<?php

namespace Tests\Unit;

use App\Services\BillingService;
use Carbon\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Unit test inti aturan bisnis (UT-01 s.d. UT-07).
 */
class CoreBusinessUnitTest extends TestCase
{
    private BillingService $billing;

    protected function setUp(): void
    {
        $this->billing = new BillingService;
    }

    public function test_ut_01_sewa_bulanan_mulai_1_februari_2027_berakhir_28_februari(): void
    {
        $end = $this->billing->monthlyEndDate(Carbon::parse('2027-02-01'));

        $this->assertSame('2027-02-28', $end->toDateString());
    }

    public function test_ut_02_sewa_harian_maksimal_30_hari_meski_mulai_februari(): void
    {
        $start = Carbon::parse('2027-02-01');

        $this->assertSame(30, $this->billing->maxDailyDays($start));
        $this->billing->assertDailyDuration($start, 30);
        $this->assertSame('2027-03-02', $this->billing->dailyEndDate($start, 30)->toDateString());

        try {
            $this->billing->assertDailyDuration($start, 31);
            $this->fail('Durasi 31 hari harus ditolak.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('maksimal 30', $exception->getMessage());
        }
    }

    public function test_ut_03_sewa_harian_30_hari_pada_bulan_31_hari_diterima(): void
    {
        $start = Carbon::parse('2027-01-01');

        $this->assertSame(30, $this->billing->maxDailyDays($start));
        $this->billing->assertDailyDuration($start, 30);

        $this->assertSame('2027-01-30', $this->billing->dailyEndDate($start, 30)->toDateString());
    }

    public function test_ut_04_sewa_bulanan_lebih_dari_satu_bulan_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sewa bulanan hanya boleh satu bulan per transaksi.');

        $this->billing->calculate(
            rentalType: 'bulanan',
            startDate: Carbon::parse('2027-02-01'),
            days: 1,
            dailyRate: 50000,
            months: 2,
        );
    }

    public function test_ut_05_harga_bulanan_adalah_harga_harian_dikali_25(): void
    {
        $dailyRate = 50000;

        $this->assertEquals(1250000.0, $this->billing->calcMonthlyRentFee($dailyRate));
    }

    public function test_ut_06_perhitungan_biaya_pengisian_mobil_listrik(): void
    {
        $topupKwh = $this->billing->calcTopupKwh(60, 25);
        $electricityFee = $this->billing->calcElectricityFee($topupKwh, 2500);
        $total = $this->billing->calcTotal(1250000, $electricityFee);

        $this->assertEquals(45.0, $topupKwh);
        $this->assertEquals(112500.0, $electricityFee);
        $this->assertEquals(1362500.0, $total);
    }

    public function test_ut_07_sisa_baterai_melebihi_kapasitas_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sisa baterai harus berada di antara 0 dan 100 persen.');

        $this->billing->calcTopupKwh(60, 101);
    }
}
