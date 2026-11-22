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
        Schema::create('garages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // e.g. S001, M001, E001
            $table->enum('type', ['sepeda', 'mobil', 'ev']);
            $table->decimal('daily_rate', 12, 2);
            // Hanya untuk garasi tipe EV
            $table->decimal('electricity_rate_kwh', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $this->installIntegrityRules();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garages');
    }

    /** Pasang aturan untuk tabel ini. */
    public function installIntegrityRules(): void
    {
        $statements = match (DB::getDriverName()) {
            'sqlite' => [
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS garages_validate_insert
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
                    CREATE TRIGGER IF NOT EXISTS garages_validate_update
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
            ],
            'mysql' => [
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS garages_validate_insert
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
                    CREATE TRIGGER IF NOT EXISTS garages_validate_update
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
            ],
            default => [],
        };

        foreach ($statements as $statement) {
            DB::unprepared($statement);
        }
    }
};
