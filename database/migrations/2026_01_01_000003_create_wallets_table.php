<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->decimal('main_balance', 16, 4)->default(0.0000);
            $table->decimal('bonus_balance', 16, 4)->default(0.0000);
            $table->decimal('commission_balance', 16, 4)->default(0.0000);
            $table->decimal('total_deposited', 16, 4)->default(0.0000);
            $table->decimal('total_withdrawn', 16, 4)->default(0.0000);
            $table->decimal('total_winnings', 16, 4)->default(0.0000);
            $table->enum('status', ['active', 'frozen'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
