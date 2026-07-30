<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->string('period_number')->index();
            $table->json('result_data')->nullable(); // color, number, crash_multiplier, mine_locations, wheel_sector
            $table->string('provably_fair_hash')->nullable();
            $table->string('seed')->nullable();
            $table->enum('status', ['pending', 'settled', 'cancelled'])->default('pending');
            $table->boolean('manual_override')->default(false);
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_results');
    }
};
