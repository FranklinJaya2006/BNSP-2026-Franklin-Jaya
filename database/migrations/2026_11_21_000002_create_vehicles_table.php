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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
