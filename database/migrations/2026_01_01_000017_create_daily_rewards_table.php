<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('day_number'); // 1 to 7
            $table->decimal('reward_amount', 10, 2);
            $table->date('claimed_date');
            $table->timestamps();

            $table->unique(['user_id', 'claimed_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_rewards');
    }
};
