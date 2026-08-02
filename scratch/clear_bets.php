<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Clearing all bets from database...\n";

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::table('jet_bets')->truncate();
    DB::table('crash_bets')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    echo "[SUCCESS] jet_bets and crash_bets tables cleared successfully!\n";
} catch (\Throwable $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
