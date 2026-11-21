<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
    }
};
