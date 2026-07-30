<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // razorpay, phonepe, cashfree, upi_qr, manual_bank
            $table->string('qr_image')->nullable();
            $table->string('upi_id')->nullable();
            $table->decimal('min_amount', 12, 2)->default(100.00);
            $table->decimal('max_amount', 12, 2)->default(50000.00);
            $table->decimal('bonus_percentage', 5, 2)->default(0.00);
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
