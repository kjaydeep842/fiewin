<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jet_bets', function (Blueprint $table) {
            if (!Schema::hasColumn('jet_bets', 'auto_cashout')) {
                $table->decimal('auto_cashout', 8, 2)->nullable()->after('bet_amount');
            }
        });

        Schema::table('crash_bets', function (Blueprint $table) {
            if (!Schema::hasColumn('crash_bets', 'auto_cashout')) {
                $table->decimal('auto_cashout', 8, 2)->nullable()->after('bet_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jet_bets', function (Blueprint $table) {
            if (Schema::hasColumn('jet_bets', 'auto_cashout')) {
                $table->dropColumn('auto_cashout');
            }
        });

        Schema::table('crash_bets', function (Blueprint $table) {
            if (Schema::hasColumn('crash_bets', 'auto_cashout')) {
                $table->dropColumn('auto_cashout');
            }
        });
    }
};
