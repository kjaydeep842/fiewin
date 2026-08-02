<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JetSetting;
use App\Models\CrashSetting;

echo "=== UPDATING JET & CRASH SETTINGS ===\n";

$jetSettings = JetSetting::getSettings();
$jetSettings->update([
    'min_bet' => 10.00,
    'max_bet' => 500000.00,
]);

$crashSettings = CrashSetting::getSettings();
$crashSettings->update([
    'min_bet' => 10.00,
    'max_bet' => 500000.00,
]);

echo "Jet Settings: min_bet = ₹{$jetSettings->fresh()->min_bet}, max_bet = ₹{$jetSettings->fresh()->max_bet}\n";
echo "Crash Settings: min_bet = ₹{$crashSettings->fresh()->min_bet}, max_bet = ₹{$crashSettings->fresh()->max_bet}\n";
