<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('transaction_id')->unique();
            $table->decimal('amount', 14, 2);
            $table->decimal('fee', 14, 2)->default(0.00);
            $table->decimal('net_amount', 14, 2);
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->onDelete('set null');
            $table->string('upi_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('admin_note')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
