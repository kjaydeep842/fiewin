<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->foreignId('game_result_id')->nullable()->constrained('game_results')->onDelete('set null');
            $table->string('period_number')->index();
            $table->decimal('bet_amount', 14, 2);
            $table->string('bet_type'); // color (green/red/violet), number (0-9), multiplier, cards, sector
            $table->decimal('win_amount', 14, 2)->default(0.00);
            $table->decimal('multiplier', 10, 2)->default(1.00);
            $table->decimal('cashout_multiplier', 10, 2)->nullable();
            $table->enum('status', ['pending', 'won', 'lost', 'cancelled'])->default('pending');
            $table->json('bet_details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_bets');
    }
};
