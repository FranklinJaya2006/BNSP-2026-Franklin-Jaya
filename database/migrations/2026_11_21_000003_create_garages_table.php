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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garages');
    }
};
