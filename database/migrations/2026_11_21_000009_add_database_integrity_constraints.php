<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $triggers = [
        'garages_validate_insert',
        'garages_validate_update',
        'vehicles_require_plate_insert',
        'vehicles_require_plate_update',
        'vehicles_require_battery_insert',
        'vehicles_require_battery_update',
        'rentals_motorcycle_garage_insert',
        'rentals_motorcycle_garage_update',
        'rentals_type_compat_insert',
        'rentals_type_compat_update',
        'rentals_no_overlap_insert',
        'rentals_no_overlap_update',
        'payment_proofs_exactly_one_insert',
        'payment_proofs_exactly_one_update',
    ];

    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `vehicles` MODIFY `type` ENUM('sepeda', 'sepeda_motor', 'mobil_bensin', 'mobil_listrik') NOT NULL");
            DB::statement('ALTER TABLE `payment_proofs` MODIFY `rental_id` BIGINT UNSIGNED NULL');
        }

        $this->dropTriggers();

        if (DB::getDriverName() === 'sqlite') {
            $this->createSqliteTriggers();

            return;
        }

        if (DB::getDriverName() === 'mysql') {
            $this->createMySqlTriggers();
        }
    }

    public function down(): void
    {
        $this->dropTriggers();
    }

    private function dropTriggers(): void
    {
        foreach ($this->triggers as $trigger) {
            DB::unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }
    }

    private function createSqliteTriggers(): void
    {
        $statements = [
            <<<'SQL'
                CREATE TRIGGER garages_validate_insert
                BEFORE INSERT ON garages
                FOR EACH ROW
                WHEN NEW.code NOT GLOB '[SCE][0-9][0-9][0-9]'
                  OR NEW.code GLOB '*000'
                  OR SUBSTR(NEW.code, 1, 1) <> CASE NEW.type
                        WHEN 'sepeda' THEN 'S'
                        WHEN 'mobil' THEN 'C'
                        WHEN 'ev' THEN 'E'
                        ELSE '?'
                     END
                BEGIN
                    SELECT RAISE(ABORT, 'Garage code must match its type and use 001-999.');
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER garages_validate_update
                BEFORE UPDATE ON garages
                FOR EACH ROW
                WHEN NEW.code NOT GLOB '[SCE][0-9][0-9][0-9]'
                  OR NEW.code GLOB '*000'
                  OR SUBSTR(NEW.code, 1, 1) <> CASE NEW.type
                        WHEN 'sepeda' THEN 'S'
                        WHEN 'mobil' THEN 'C'
                        WHEN 'ev' THEN 'E'
                        ELSE '?'
                     END
                BEGIN
                    SELECT RAISE(ABORT, 'Garage code must match its type and use 001-999.');
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER vehicles_require_plate_insert
                BEFORE INSERT ON vehicles
                FOR EACH ROW
                WHEN NEW.type = 'sepeda_motor'
                  AND (NEW.plate IS NULL OR TRIM(NEW.plate) = '')
                BEGIN
                    SELECT RAISE(ABORT, 'Motorcycles require a license plate.');
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER vehicles_require_plate_update
                BEFORE UPDATE ON vehicles
                FOR EACH ROW
                WHEN NEW.type = 'sepeda_motor'
                  AND (NEW.plate IS NULL OR TRIM(NEW.plate) = '')
                BEGIN
                    SELECT RAISE(ABORT, 'Motorcycles require a license plate.');
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER vehicles_require_battery_insert
                BEFORE INSERT ON vehicles
                FOR EACH ROW
                WHEN NEW.type = 'mobil_listrik'
                  AND (NEW.battery_capacity_kwh IS NULL OR NEW.battery_capacity_kwh <= 0)
                BEGIN
                    SELECT RAISE(ABORT, 'Electric vehicles require battery capacity.');
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER vehicles_require_battery_update
                BEFORE UPDATE ON vehicles
                FOR EACH ROW
                WHEN NEW.type = 'mobil_listrik'
                  AND (NEW.battery_capacity_kwh IS NULL OR NEW.battery_capacity_kwh <= 0)
                BEGIN
                    SELECT RAISE(ABORT, 'Electric vehicles require battery capacity.');
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER rentals_motorcycle_garage_insert
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
                CREATE TRIGGER rentals_motorcycle_garage_update
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
                CREATE TRIGGER rentals_type_compat_insert
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
                          OR (v.type = 'mobil_listrik' AND g.type <> 'ev')
                      )
                )
                BEGIN
                    SELECT RAISE(ABORT, 'Vehicle and garage types are incompatible.');
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER rentals_type_compat_update
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
                          OR (v.type = 'mobil_listrik' AND g.type <> 'ev')
                      )
                )
                BEGIN
                    SELECT RAISE(ABORT, 'Vehicle and garage types are incompatible.');
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER rentals_no_overlap_insert
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
                CREATE TRIGGER rentals_no_overlap_update
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
            <<<'SQL'
                CREATE TRIGGER payment_proofs_exactly_one_insert
                BEFORE INSERT ON payment_proofs
                FOR EACH ROW
                WHEN (NEW.rental_id IS NULL AND NEW.extension_id IS NULL)
                  OR (NEW.rental_id IS NOT NULL AND NEW.extension_id IS NOT NULL)
                BEGIN
                    SELECT RAISE(ABORT, 'Payment proof must reference exactly one rental or extension.');
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER payment_proofs_exactly_one_update
                BEFORE UPDATE ON payment_proofs
                FOR EACH ROW
                WHEN (NEW.rental_id IS NULL AND NEW.extension_id IS NULL)
                  OR (NEW.rental_id IS NOT NULL AND NEW.extension_id IS NOT NULL)
                BEGIN
                    SELECT RAISE(ABORT, 'Payment proof must reference exactly one rental or extension.');
                END
            SQL,
        ];

        foreach ($statements as $statement) {
            DB::unprepared($statement);
        }
    }

    private function createMySqlTriggers(): void
    {
        $statements = [
            <<<'SQL'
                CREATE TRIGGER garages_validate_insert
                BEFORE INSERT ON garages
                FOR EACH ROW
                BEGIN
                    IF NOT (NEW.code COLLATE utf8mb4_bin REGEXP '^[SCE][0-9]{3}$')
                       OR SUBSTRING(NEW.code, 2) = '000'
                       OR SUBSTR(NEW.code, 1, 1) <> CASE NEW.type
                            WHEN 'sepeda' THEN 'S'
                            WHEN 'mobil' THEN 'C'
                            WHEN 'ev' THEN 'E'
                            ELSE '?'
                          END THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Garage code must match its type and use 001-999.';
                    END IF;
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER garages_validate_update
                BEFORE UPDATE ON garages
                FOR EACH ROW
                BEGIN
                    IF NOT (NEW.code COLLATE utf8mb4_bin REGEXP '^[SCE][0-9]{3}$')
                       OR SUBSTRING(NEW.code, 2) = '000'
                       OR SUBSTR(NEW.code, 1, 1) <> CASE NEW.type
                            WHEN 'sepeda' THEN 'S'
                            WHEN 'mobil' THEN 'C'
                            WHEN 'ev' THEN 'E'
                            ELSE '?'
                          END THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Garage code must match its type and use 001-999.';
                    END IF;
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER vehicles_require_plate_insert
                BEFORE INSERT ON vehicles
                FOR EACH ROW
                BEGIN
                    IF NEW.type = 'sepeda_motor' AND (NEW.plate IS NULL OR TRIM(NEW.plate) = '') THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Motorcycles require a license plate.';
                    END IF;
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER vehicles_require_plate_update
                BEFORE UPDATE ON vehicles
                FOR EACH ROW
                BEGIN
                    IF NEW.type = 'sepeda_motor' AND (NEW.plate IS NULL OR TRIM(NEW.plate) = '') THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Motorcycles require a license plate.';
                    END IF;
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER vehicles_require_battery_insert
                BEFORE INSERT ON vehicles
                FOR EACH ROW
                BEGIN
                    IF NEW.type = 'mobil_listrik' AND (NEW.battery_capacity_kwh IS NULL OR NEW.battery_capacity_kwh <= 0) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Electric vehicles require battery capacity.';
                    END IF;
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER vehicles_require_battery_update
                BEFORE UPDATE ON vehicles
                FOR EACH ROW
                BEGIN
                    IF NEW.type = 'mobil_listrik' AND (NEW.battery_capacity_kwh IS NULL OR NEW.battery_capacity_kwh <= 0) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Electric vehicles require battery capacity.';
                    END IF;
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER rentals_motorcycle_garage_insert
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
                CREATE TRIGGER rentals_motorcycle_garage_update
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
                CREATE TRIGGER rentals_type_compat_insert
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
                              OR (v.type = 'mobil_listrik' AND g.type <> 'ev')
                          )
                    ) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vehicle and garage types are incompatible.';
                    END IF;
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER rentals_type_compat_update
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
                              OR (v.type = 'mobil_listrik' AND g.type <> 'ev')
                          )
                    ) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vehicle and garage types are incompatible.';
                    END IF;
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER rentals_no_overlap_insert
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
                CREATE TRIGGER rentals_no_overlap_update
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
            <<<'SQL'
                CREATE TRIGGER payment_proofs_exactly_one_insert
                BEFORE INSERT ON payment_proofs
                FOR EACH ROW
                BEGIN
                    IF (NEW.rental_id IS NULL AND NEW.extension_id IS NULL)
                       OR (NEW.rental_id IS NOT NULL AND NEW.extension_id IS NOT NULL) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Payment proof must reference exactly one rental or extension.';
                    END IF;
                END
            SQL,
            <<<'SQL'
                CREATE TRIGGER payment_proofs_exactly_one_update
                BEFORE UPDATE ON payment_proofs
                FOR EACH ROW
                BEGIN
                    IF (NEW.rental_id IS NULL AND NEW.extension_id IS NULL)
                       OR (NEW.rental_id IS NOT NULL AND NEW.extension_id IS NOT NULL) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Payment proof must reference exactly one rental or extension.';
                    END IF;
                END
            SQL,
        ];

        foreach ($statements as $statement) {
            DB::unprepared($statement);
        }
    }
};
