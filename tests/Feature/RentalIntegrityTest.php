<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RentalIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private int $user;

    private int $motorcycle;

    private int $garage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = DB::table('users')->insertGetId([
            'nik' => '0123456789012345', 'name' => 'Penyewa', 'phone' => '081234567890',
            'email' => 'rental-integrity@example.test', 'password' => 'unused',
        ]);
        $this->motorcycle = DB::table('vehicles')->insertGetId([
            'user_id' => $this->user, 'type' => 'sepeda_motor', 'plate' => 'L 1234 AB',
        ]);
        $this->garage = DB::table('garages')->insertGetId([
            'code' => 'S001', 'type' => 'sepeda', 'daily_rate' => 10000,
        ]);
    }

    public function test_motorcycle_requires_a_plate(): void
    {
        $this->expectExceptionMessage('Motorcycles require a license plate.');

        DB::table('vehicles')->where('id', $this->motorcycle)->update(['plate' => ' ']);
    }

    public function test_motorcycle_cannot_rent_a_car_garage(): void
    {
        $this->garage = DB::table('garages')->insertGetId([
            'code' => 'C001', 'type' => 'mobil', 'daily_rate' => 10000,
        ]);

        $this->expectExceptionMessage('Motorcycles require a bicycle garage.');

        $this->createRental('2026-11-21', '2026-11-21');
    }

    public function test_rentals_cannot_share_the_final_day(): void
    {
        $this->createRental('2026-11-21', '2026-11-22');

        $this->expectExceptionMessage('Garage is already rented during this period.');

        $this->createRental('2026-11-22', '2026-11-23');
    }

    public function test_extending_a_rental_cannot_overlap_the_next_rental(): void
    {
        $firstRental = $this->createRental('2026-11-21', '2026-11-21');
        $this->createRental('2026-11-23', '2026-11-23');

        $rental = DB::table('rentals')->where('id', $firstRental);
        $this->assertSame(1, $rental->update(['end_date' => '2026-11-22']));

        $this->expectExceptionMessage('Garage is already rented during this period.');

        $rental->update(['end_date' => '2026-11-23']);
    }

    public function test_proof_can_reference_a_rental_or_an_extension_but_not_both(): void
    {
        $firstRental = $this->createRental('2026-11-21', '2026-11-21');
        $secondRental = $this->createRental('2026-11-23', '2026-11-24');
        $extension = DB::table('rental_extensions')->insertGetId([
            'rental_id' => $secondRental, 'previous_end' => '2026-11-23',
            'new_end' => '2026-11-24', 'rent_fee' => 10000, 'created_by' => DB::table('admins')->insertGetId([
                'name' => 'Admin', 'email' => 'admin-integrity@example.test', 'password' => 'unused',
            ]),
        ]);
        $proof = ['object_key' => 'initial.png', 'mime_type' => 'image/png', 'file_size' => 12, 'uploaded_at' => now()];

        DB::table('payment_proofs')->insert($proof + ['rental_id' => $firstRental, 'extension_id' => null]);
        $proof['object_key'] = 'extension.png';
        DB::table('payment_proofs')->insert($proof + ['extension_id' => $extension]);
        $this->assertSame(2, DB::table('payment_proofs')->where('rental_id', $firstRental)->orWhere('extension_id', $extension)->count());

        $this->expectExceptionMessage('Payment proof must reference exactly one rental or extension.');

        DB::table('payment_proofs')->where('object_key', 'extension.png')->update(['rental_id' => $firstRental]);
    }

    public function test_proof_requires_a_reference(): void
    {
        $this->expectExceptionMessage('Payment proof must reference exactly one rental or extension.');

        DB::table('payment_proofs')->insert([
            'object_key' => 'missing.png', 'mime_type' => 'image/png', 'file_size' => 12, 'uploaded_at' => now(),
        ]);
    }

    private function createRental(string $start, string $end): int
    {
        return DB::table('rentals')->insertGetId([
            'user_id' => $this->user, 'vehicle_id' => $this->motorcycle, 'garage_id' => $this->garage,
            'rental_type' => 'harian', 'start_date' => $start, 'end_date' => $end,
            'daily_rate_snapshot' => 10000, 'rent_fee' => 10000, 'total' => 10000,
        ]);
    }
}
