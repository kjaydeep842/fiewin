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
        // 1. Merchant Accounts
        Schema::create('merchant_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('account_holder')->nullable();
            $table->string('upi_id')->nullable();
            $table->string('qr_image')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->decimal('daily_limit', 15, 2)->default(200000.00);
            $table->decimal('current_daily_total', 15, 2)->default(0.00);
            $table->integer('priority')->default(1);
            $table->json('supported_payment_types')->nullable(); // e.g. ["upi", "bank_transfer", "qr"]
            $table->string('region')->default('IN');
            $table->string('currency')->default('INR');
            $table->timestamps();
        });

        // 2. Deposit Requests
        Schema::create('deposit_requests', function (Blueprint $table) {
            $table->id();
            $table->string('deposit_id')->unique(); // e.g. DEP202608020001
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('merchant_account_id')->nullable()->constrained('merchant_accounts')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->default('upi'); // upi, bank_transfer, qr
            $table->string('utr_number')->nullable()->index();
            $table->enum('status', ['pending', 'verified', 'approved', 'rejected', 'expired', 'cancelled'])->default('pending');
            $table->text('user_remarks')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->foreignId('rejected_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // 3. Deposit Proofs
        Schema::create('deposit_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deposit_request_id')->constrained('deposit_requests')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->string('original_name')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });

        // 4. Deposit Verifications Audit
        Schema::create('deposit_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deposit_request_id')->constrained('deposit_requests')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->string('status_from');
            $table->string('status_to');
            $table->text('verification_notes')->nullable();
            $table->timestamp('verified_at');
            $table->timestamps();
        });

        // 5. Merchant Assignment Logs
        Schema::create('merchant_assignment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deposit_request_id')->constrained('deposit_requests')->onDelete('cascade');
            $table->foreignId('merchant_account_id')->constrained('merchant_accounts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('assignment_reason')->nullable();
            $table->timestamp('assigned_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_assignment_logs');
        Schema::dropIfExists('deposit_verifications');
        Schema::dropIfExists('deposit_proofs');
        Schema::dropIfExists('deposit_requests');
        Schema::dropIfExists('merchant_accounts');
    }
};
