<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('admins')->restrictOnDelete();
            $table->enum('action', ['approved', 'rejected']);
            $table->text('notes')->nullable();
            $table->timestamp('verified_at');
            $table->timestamps();

            $table->index(['user_id', 'verified_at'], 'idx_user_verification_latest');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_verifications');
    }
};
