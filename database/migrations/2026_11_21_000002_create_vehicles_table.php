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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['sepeda', 'sepeda_motor', 'mobil_bensin', 'mobil_listrik']);
            $table->string('plate', 20)->nullable(); // null untuk sepeda
            $table->string('make', 100)->nullable();
            $table->string('model', 100)->nullable();
            // Hanya untuk mobil_listrik
            $table->decimal('battery_capacity_kwh', 8, 2)->nullable();
            $table->timestamps();
        });

        $this->installIntegrityRules();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }

    /** Pasang aturan untuk tabel ini. */
    public function installIntegrityRules(): void
    {
        $statements = match (DB::getDriverName()) {
            'sqlite' => [
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS vehicles_require_plate_insert
                    BEFORE INSERT ON vehicles
                    FOR EACH ROW
                    WHEN NEW.type = 'sepeda_motor'
                      AND (NEW.plate IS NULL OR TRIM(NEW.plate) = '')
                    BEGIN
                        SELECT RAISE(ABORT, 'Motorcycles require a license plate.');
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS vehicles_require_plate_update
                    BEFORE UPDATE ON vehicles
                    FOR EACH ROW
                    WHEN NEW.type = 'sepeda_motor'
                      AND (NEW.plate IS NULL OR TRIM(NEW.plate) = '')
                    BEGIN
                        SELECT RAISE(ABORT, 'Motorcycles require a license plate.');
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS vehicles_require_battery_insert
                    BEFORE INSERT ON vehicles
                    FOR EACH ROW
                    WHEN NEW.type = 'mobil_listrik'
                      AND (NEW.battery_capacity_kwh IS NULL OR NEW.battery_capacity_kwh <= 0)
                    BEGIN
                        SELECT RAISE(ABORT, 'Electric vehicles require battery capacity.');
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS vehicles_require_battery_update
                    BEFORE UPDATE ON vehicles
                    FOR EACH ROW
                    WHEN NEW.type = 'mobil_listrik'
                      AND (NEW.battery_capacity_kwh IS NULL OR NEW.battery_capacity_kwh <= 0)
                    BEGIN
                        SELECT RAISE(ABORT, 'Electric vehicles require battery capacity.');
                    END
                SQL,
            ],
            'mysql' => [
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS vehicles_require_plate_insert
                    BEFORE INSERT ON vehicles
                    FOR EACH ROW
                    BEGIN
                        IF NEW.type = 'sepeda_motor' AND (NEW.plate IS NULL OR TRIM(NEW.plate) = '') THEN
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Motorcycles require a license plate.';
                        END IF;
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS vehicles_require_plate_update
                    BEFORE UPDATE ON vehicles
                    FOR EACH ROW
                    BEGIN
                        IF NEW.type = 'sepeda_motor' AND (NEW.plate IS NULL OR TRIM(NEW.plate) = '') THEN
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Motorcycles require a license plate.';
                        END IF;
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS vehicles_require_battery_insert
                    BEFORE INSERT ON vehicles
                    FOR EACH ROW
                    BEGIN
                        IF NEW.type = 'mobil_listrik' AND (NEW.battery_capacity_kwh IS NULL OR NEW.battery_capacity_kwh <= 0) THEN
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Electric vehicles require battery capacity.';
                        END IF;
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS vehicles_require_battery_update
                    BEFORE UPDATE ON vehicles
                    FOR EACH ROW
                    BEGIN
                        IF NEW.type = 'mobil_listrik' AND (NEW.battery_capacity_kwh IS NULL OR NEW.battery_capacity_kwh <= 0) THEN
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Electric vehicles require battery capacity.';
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
