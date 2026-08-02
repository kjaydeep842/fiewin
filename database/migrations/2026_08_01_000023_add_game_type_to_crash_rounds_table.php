<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crash_rounds', function (Blueprint $table) {
            if (!Schema::hasColumn('crash_rounds', 'game_type')) {
                $table->string('game_type')->default('rocket')->index()->after('round_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('crash_rounds', function (Blueprint $table) {
            if (Schema::hasColumn('crash_rounds', 'game_type')) {
                $table->dropColumn('game_type');
            }
        });
    }
};
