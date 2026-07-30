<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('transaction_id')->unique();
            $table->string('payment_method'); // razorpay, phonepe, cashfree, upi_qr, manual
            $table->decimal('amount', 14, 2);
            $table->decimal('bonus_amount', 14, 2)->default(0.00);
            $table->string('utr_number')->nullable();
            $table->string('proof_image')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->json('gateway_response')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
