<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crash_rounds', function (Blueprint $table) {
            $table->id();
            $table->string('round_id')->unique();
            $table->decimal('crash_multiplier', 8, 2);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('status')->default('BETTING_OPEN'); // BETTING_OPEN, BETTING_CLOSED, PLANE_TAKEOFF, FLYING, CRASHED, NEXT_ROUND_COUNTDOWN
            $table->timestamps();
        });

        Schema::create('crash_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crash_round_id')->constrained('crash_rounds')->onDelete('cascade');
            $table->string('round_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('bet_amount', 12, 2);
            $table->decimal('cashout_multiplier', 8, 2)->nullable();
            $table->decimal('profit', 12, 2)->default(0.00);
            $table->string('status')->default('flying'); // flying, cashed_out, lost, cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crash_bets');
        Schema::dropIfExists('crash_rounds');
    }
};
