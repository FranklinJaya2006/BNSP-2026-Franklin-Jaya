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
        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->nullable()->constrained('rentals')->cascadeOnDelete();
            // Null berarti bukti untuk rental awal; diisi untuk bukti perpanjangan
            $table->foreignId('extension_id')->nullable()->constrained('rental_extensions')->cascadeOnDelete();
            $table->string('object_key')->unique(); // path private di storage
            $table->string('mime_type', 50);
            $table->unsignedBigInteger('file_size'); // bytes
            $table->timestamp('uploaded_at');
            $table->timestamps();
        });

        $this->installIntegrityRules();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
    }

    /** Pasang aturan untuk tabel ini. */
    public function installIntegrityRules(): void
    {
        $statements = match (DB::getDriverName()) {
            'sqlite' => [
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS payment_proofs_exactly_one_insert
                    BEFORE INSERT ON payment_proofs
                    FOR EACH ROW
                    WHEN (NEW.rental_id IS NULL AND NEW.extension_id IS NULL)
                      OR (NEW.rental_id IS NOT NULL AND NEW.extension_id IS NOT NULL)
                    BEGIN
                        SELECT RAISE(ABORT, 'Payment proof must reference exactly one rental or extension.');
                    END
                SQL,
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS payment_proofs_exactly_one_update
                    BEFORE UPDATE ON payment_proofs
                    FOR EACH ROW
                    WHEN (NEW.rental_id IS NULL AND NEW.extension_id IS NULL)
                      OR (NEW.rental_id IS NOT NULL AND NEW.extension_id IS NOT NULL)
                    BEGIN
                        SELECT RAISE(ABORT, 'Payment proof must reference exactly one rental or extension.');
                    END
                SQL,
            ],
            'mysql' => [
                <<<'SQL'
                    CREATE TRIGGER IF NOT EXISTS payment_proofs_exactly_one_insert
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
                    CREATE TRIGGER IF NOT EXISTS payment_proofs_exactly_one_update
                    BEFORE UPDATE ON payment_proofs
                    FOR EACH ROW
                    BEGIN
                        IF (NEW.rental_id IS NULL AND NEW.extension_id IS NULL)
                           OR (NEW.rental_id IS NOT NULL AND NEW.extension_id IS NOT NULL) THEN
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Payment proof must reference exactly one rental or extension.';
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
