<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->foreignId('garage_id')->constrained('garages')->restrictOnDelete();
            $table->enum('rental_type', ['harian', 'bulanan']);
            $table->date('start_date');
            $table->date('end_date');
            // Snapshot harga agar histori tidak berubah setelah tarif admin diubah
            $table->decimal('daily_rate_snapshot', 12, 2);
            $table->decimal('rent_fee', 14, 2);
            $table->decimal('electricity_fee', 14, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->enum('status', ['active', 'completed', 'extended'])->default('active');
            // Inline charging detail (hanya diisi untuk kendaraan EV)
            $table->decimal('battery_capacity_kwh', 8, 2)->nullable();
            $table->decimal('remaining_pct', 5, 2)->nullable(); // 0.00 – 100.00
            $table->decimal('estimated_topup_kwh', 8, 2)->nullable();
            $table->decimal('unit_kwh_rate_snapshot', 10, 2)->nullable();
            // Anchor tanggal awal untuk kalkulasi perpanjangan bulanan tanpa drift
            $table->date('start_date_anchor')->nullable();
            $table->timestamps();

            // Index untuk anti-double-booking: garage + rentang tanggal + status
            $table->index(['garage_id', 'start_date', 'end_date', 'status'], 'idx_garage_date_status');
        });

        $this->installIntegrityRules();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }

    /** Pasang aturan untuk tabel ini. */
    public function installIntegrityRules(): void
    {
        $statements = match (DB::getDriverName()) {
            'sqlite' => [
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS rentals_motorcycle_garage_insert
                    BEFORE INSERT ON rentals
                    FOR EACH ROW
                    WHEN EXISTS (
                        SELECT 1
                        FROM vehicles v
                        JOIN garages g ON g.id = NEW.garage_id
                        WHERE v.id = NEW.vehicle_id
                          AND v.type = 'sepeda_motor'
                          AND g.type <> 'sepeda'
                    )
                    BEGIN
                        SELECT RAISE(ABORT, 'Motorcycles require a bicycle garage.');
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS rentals_motorcycle_garage_update
                    BEFORE UPDATE ON rentals
                    FOR EACH ROW
                    WHEN EXISTS (
                        SELECT 1
                        FROM vehicles v
                        JOIN garages g ON g.id = NEW.garage_id
                        WHERE v.id = NEW.vehicle_id
                          AND v.type = 'sepeda_motor'
                          AND g.type <> 'sepeda'
                    )
                    BEGIN
                        SELECT RAISE(ABORT, 'Motorcycles require a bicycle garage.');
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS rentals_type_compat_insert
                    BEFORE INSERT ON rentals
                    FOR EACH ROW
                    WHEN EXISTS (
                        SELECT 1
                        FROM vehicles v
                        JOIN garages g ON g.id = NEW.garage_id
                        WHERE v.id = NEW.vehicle_id
                          AND v.type <> 'sepeda_motor'
                          AND (
                              (v.type IN ('sepeda', 'sepeda_motor') AND g.type <> 'sepeda')
                              OR (v.type = 'mobil_bensin' AND g.type <> 'mobil')
                              OR (v.type = 'mobil_listrik' AND g.type NOT IN ('mobil', 'ev'))
                          )
                    )
                    BEGIN
                        SELECT RAISE(ABORT, 'Vehicle and garage types are incompatible.');
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS rentals_type_compat_update
                    BEFORE UPDATE ON rentals
                    FOR EACH ROW
                    WHEN EXISTS (
                        SELECT 1
                        FROM vehicles v
                        JOIN garages g ON g.id = NEW.garage_id
                        WHERE v.id = NEW.vehicle_id
                          AND v.type <> 'sepeda_motor'
                          AND (
                              (v.type IN ('sepeda', 'sepeda_motor') AND g.type <> 'sepeda')
                              OR (v.type = 'mobil_bensin' AND g.type <> 'mobil')
                              OR (v.type = 'mobil_listrik' AND g.type NOT IN ('mobil', 'ev'))
                          )
                    )
                    BEGIN
                        SELECT RAISE(ABORT, 'Vehicle and garage types are incompatible.');
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS rentals_no_overlap_insert
                    BEFORE INSERT ON rentals
                    FOR EACH ROW
                    WHEN EXISTS (
                        SELECT 1
                        FROM rentals r
                        WHERE r.garage_id = NEW.garage_id
                          AND r.status IN ('active', 'extended')
                          AND r.start_date <= NEW.end_date
                          AND r.end_date >= NEW.start_date
                    )
                    BEGIN
                        SELECT RAISE(ABORT, 'Garage is already rented during this period.');
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS rentals_no_overlap_update
                    BEFORE UPDATE ON rentals
                    FOR EACH ROW
                    WHEN EXISTS (
                        SELECT 1
                        FROM rentals r
                        WHERE r.garage_id = NEW.garage_id
                          AND r.id <> NEW.id
                          AND r.status IN ('active', 'extended')
                          AND r.start_date <= NEW.end_date
                          AND r.end_date >= NEW.start_date
                    )
                    BEGIN
                        SELECT RAISE(ABORT, 'Garage is already rented during this period.');
                    END
                SQL,
            ],
            'mysql' => [
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS rentals_motorcycle_garage_insert
                    BEFORE INSERT ON rentals
                    FOR EACH ROW
                    BEGIN
                        IF EXISTS (
                            SELECT 1
                            FROM vehicles v
                            JOIN garages g ON g.id = NEW.garage_id
                            WHERE v.id = NEW.vehicle_id
                              AND v.type = 'sepeda_motor'
                              AND g.type <> 'sepeda'
                        ) THEN
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Motorcycles require a bicycle garage.';
                        END IF;
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS rentals_motorcycle_garage_update
                    BEFORE UPDATE ON rentals
                    FOR EACH ROW
                    BEGIN
                        IF EXISTS (
                            SELECT 1
                            FROM vehicles v
                            JOIN garages g ON g.id = NEW.garage_id
                            WHERE v.id = NEW.vehicle_id
                              AND v.type = 'sepeda_motor'
                              AND g.type <> 'sepeda'
                        ) THEN
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Motorcycles require a bicycle garage.';
                        END IF;
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS rentals_type_compat_insert
                    BEFORE INSERT ON rentals
                    FOR EACH ROW
                    BEGIN
                        IF EXISTS (
                            SELECT 1
                            FROM vehicles v
                            JOIN garages g ON g.id = NEW.garage_id
                            WHERE v.id = NEW.vehicle_id
                              AND v.type <> 'sepeda_motor'
                              AND (
                                  (v.type IN ('sepeda', 'sepeda_motor') AND g.type <> 'sepeda')
                                  OR (v.type = 'mobil_bensin' AND g.type <> 'mobil')
                                  OR (v.type = 'mobil_listrik' AND g.type NOT IN ('mobil', 'ev'))
                              )
                        ) THEN
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vehicle and garage types are incompatible.';
                        END IF;
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS rentals_type_compat_update
                    BEFORE UPDATE ON rentals
                    FOR EACH ROW
                    BEGIN
                        IF EXISTS (
                            SELECT 1
                            FROM vehicles v
                            JOIN garages g ON g.id = NEW.garage_id
                            WHERE v.id = NEW.vehicle_id
                              AND v.type <> 'sepeda_motor'
                              AND (
                                  (v.type IN ('sepeda', 'sepeda_motor') AND g.type <> 'sepeda')
                                  OR (v.type = 'mobil_bensin' AND g.type <> 'mobil')
                                  OR (v.type = 'mobil_listrik' AND g.type NOT IN ('mobil', 'ev'))
                              )
                        ) THEN
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vehicle and garage types are incompatible.';
                        END IF;
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS rentals_no_overlap_insert
                    BEFORE INSERT ON rentals
                    FOR EACH ROW
                    BEGIN
                        IF EXISTS (
                            SELECT 1
                            FROM rentals r
                            WHERE r.garage_id = NEW.garage_id
                              AND r.status IN ('active', 'extended')
                              AND r.start_date <= NEW.end_date
                              AND r.end_date >= NEW.start_date
                        ) THEN
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Garage is already rented during this period.';
                        END IF;
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS rentals_no_overlap_update
                    BEFORE UPDATE ON rentals
                    FOR EACH ROW
                    BEGIN
                        IF EXISTS (
                            SELECT 1
                            FROM rentals r
                            WHERE r.garage_id = NEW.garage_id
                              AND r.id <> NEW.id
                              AND r.status IN ('active', 'extended')
                              AND r.start_date <= NEW.end_date
                              AND r.end_date >= NEW.start_date
                        ) THEN
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Garage is already rented during this period.';
                        END IF;
                    END
                SQL,
            ],
            default => [],
        };

        foreach ($statements as $statement) {
            DB::unprepared($statement);
        }
    }
};
