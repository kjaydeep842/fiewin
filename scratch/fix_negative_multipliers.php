<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FIXING HISTORICAL NEGATIVE MULTIPLIERS IN DATABASE ===\n";

DB::statement("UPDATE jet_bets SET cashout_multiplier = ABS(cashout_multiplier) WHERE cashout_multiplier < 0");
DB::statement("UPDATE jet_bets SET profit = ABS(profit) WHERE status = 'cashed_out' AND profit < 0");

DB::statement("UPDATE crash_bets SET cashout_multiplier = ABS(cashout_multiplier) WHERE cashout_multiplier < 0");
DB::statement("UPDATE crash_bets SET profit = ABS(profit) WHERE status = 'cashed_out' AND profit < 0");

echo "✅ SUCCESS: All historical negative multipliers and profits fixed cleanly!\n";
