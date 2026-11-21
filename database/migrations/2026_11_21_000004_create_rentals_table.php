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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
