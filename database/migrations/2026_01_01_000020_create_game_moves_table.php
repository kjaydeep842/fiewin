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
        Schema::create('game_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_bet_id')->constrained('game_bets')->onDelete('cascade');
            $table->integer('tile_index');
            $table->boolean('is_mine')->default(false);
            $table->decimal('multiplier', 8, 2)->default(1.00);
            $table->decimal('profit', 12, 2)->default(0.00);
            $table->timestamp('clicked_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_moves');
    }
};
