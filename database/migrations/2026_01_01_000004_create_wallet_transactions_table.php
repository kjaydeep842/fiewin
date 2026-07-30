<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');
            $table->decimal('amount', 16, 4);
            $table->enum('balance_type', ['main', 'bonus', 'commission'])->default('main');
            $table->enum('transaction_type', ['deposit', 'withdrawal', 'bet', 'win', 'referral_bonus', 'daily_checkin', 'commission_transfer', 'admin_adjustment']);
            $table->string('reference_id')->nullable()->index();
            $table->string('description')->nullable();
            $table->decimal('balance_after', 16, 4)->default(0.0000);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
