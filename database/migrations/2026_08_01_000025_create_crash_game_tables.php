<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crash_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('round_seconds')->default(45);
            $table->integer('betting_seconds')->default(30);
            $table->decimal('animation_speed', 5, 2)->default(1.20);
            $table->decimal('min_bet', 12, 2)->default(10.00);
            $table->decimal('max_bet', 12, 2)->default(50000.00);
            $table->decimal('rtp_percentage', 5, 2)->default(95.00);
            $table->decimal('house_edge', 5, 2)->default(5.00);
            $table->boolean('is_active')->default(true);
            $table->decimal('manual_override_multiplier', 8, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('crash_results', function (Blueprint $table) {
            $table->id();
            $table->string('round_id')->unique();
            $table->decimal('crash_multiplier', 8, 2);
            $table->string('provably_fair_hash')->nullable();
            $table->string('seed')->nullable();
            $table->timestamp('settled_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crash_results');
        Schema::dropIfExists('crash_settings');
    }
};
