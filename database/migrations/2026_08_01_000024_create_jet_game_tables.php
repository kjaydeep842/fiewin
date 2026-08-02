<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jet_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('round_seconds')->default(60);
            $table->integer('betting_seconds')->default(45);
            $table->decimal('animation_speed', 5, 2)->default(1.10);
            $table->decimal('min_bet', 12, 2)->default(10.00);
            $table->decimal('max_bet', 12, 2)->default(50000.00);
            $table->decimal('rtp_percentage', 5, 2)->default(96.00);
            $table->decimal('house_edge', 5, 2)->default(4.00);
            $table->boolean('is_active')->default(true);
            $table->decimal('manual_override_multiplier', 8, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('jet_rounds', function (Blueprint $table) {
            $table->id();
            $table->string('round_id')->unique();
            $table->decimal('crash_multiplier', 8, 2);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('status')->default('BETTING_OPEN'); // BETTING_OPEN, FLYING, CRASHED
            $table->boolean('manual_override')->default(false);
            $table->timestamps();
        });

        Schema::create('jet_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jet_round_id')->constrained('jet_rounds')->onDelete('cascade');
            $table->string('round_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('bet_amount', 12, 2);
            $table->decimal('cashout_multiplier', 8, 2)->nullable();
            $table->decimal('profit', 12, 2)->default(0.00);
            $table->string('status')->default('flying'); // flying, cashed_out, lost
            $table->timestamps();
        });

        Schema::create('jet_results', function (Blueprint $table) {
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
        Schema::dropIfExists('jet_results');
        Schema::dropIfExists('jet_bets');
        Schema::dropIfExists('jet_rounds');
        Schema::dropIfExists('jet_settings');
    }
};
