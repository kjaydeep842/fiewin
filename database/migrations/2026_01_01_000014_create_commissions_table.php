<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Benefit user
            $table->foreignId('source_user_id')->constrained('users')->onDelete('cascade'); // Bettor user
            $table->foreignId('bet_id')->nullable()->constrained('game_bets')->onDelete('cascade');
            $table->integer('level'); // 1, 2, 3
            $table->decimal('amount', 14, 4);
            $table->decimal('rate_percentage', 5, 2);
            $table->enum('status', ['pending', 'credited'])->default('credited');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
