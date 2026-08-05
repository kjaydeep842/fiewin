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
        Schema::table('bank_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_accounts', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('is_primary');
            }
            if (!Schema::hasColumn('bank_accounts', 'admin_notes')) {
                $table->string('admin_notes')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('bank_accounts', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('bank_accounts', 'admin_notes')) {
                $table->dropColumn('admin_notes');
            }
        });
    }
};
