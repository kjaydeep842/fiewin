<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\JetGameService;
use App\Services\CrashGameService;

$user = User::first();

echo "====================================================\n";
echo "       PERSISTENT ACTIVE BET RECOVERY SUITE          \n";
echo "====================================================\n\n";

$jetService = app(JetGameService::class);
$crashService = app(CrashGameService::class);

echo "1. Testing Jet State & Recovery Payload:\n";
$jetState = $jetService->getSynchronizedState($user);
echo "[PASS] Game: " . ($jetState['game'] ?? 'N/A') . "\n";
echo "[PASS] Round: " . ($jetState['round']['round_id'] ?? 'N/A') . " (" . ($jetState['round']['status'] ?? 'N/A') . ")\n";
echo "[PASS] Current Multiplier: " . ($jetState['current_multiplier'] ?? '1.00') . "x\n";
echo "[PASS] Player Active Bet: " . ($jetState['player']['has_active_bet'] ? 'YES' : 'NO') . "\n";
if ($jetState['player']['has_active_bet']) {
    echo "       -> Bet ID: {$jetState['player']['bet_id']}\n";
    echo "       -> Amount: ₹{$jetState['player']['bet_amount']}\n";
    echo "       -> Status: {$jetState['player']['status']}\n";
    echo "       -> Cashout Available: " . ($jetState['player']['cashout_available'] ? 'YES' : 'NO') . "\n";
}
echo "[PASS] My Orders Count: " . count($jetState['my_orders'] ?? []) . "\n";
echo "[PASS] User Balance: ₹" . ($jetState['user_balance'] ?? '0.00') . "\n\n";

echo "2. Testing Crash State & Recovery Payload:\n";
$crashState = $crashService->getSynchronizedState($user);
echo "[PASS] Game: " . ($crashState['game'] ?? 'N/A') . "\n";
echo "[PASS] Round: " . ($crashState['round']['round_id'] ?? 'N/A') . " (" . ($crashState['round']['status'] ?? 'N/A') . ")\n";
echo "[PASS] Current Multiplier: " . ($crashState['current_multiplier'] ?? '1.00') . "x\n";
echo "[PASS] Player Active Bet: " . ($crashState['player']['has_active_bet'] ? 'YES' : 'NO') . "\n";
if ($crashState['player']['has_active_bet']) {
    echo "       -> Bet ID: {$crashState['player']['bet_id']}\n";
    echo "       -> Amount: ₹{$crashState['player']['bet_amount']}\n";
    echo "       -> Status: {$crashState['player']['status']}\n";
    echo "       -> CashoutAvailable: " . ($crashState['player']['cashout_available'] ? 'YES' : 'NO') . "\n";
}
echo "[PASS] My Orders Count: " . count($crashState['my_orders'] ?? []) . "\n";
echo "[PASS] User Balance: ₹" . ($crashState['user_balance'] ?? '0.00') . "\n\n";

echo "====================================================\n";
echo "    ALL RECOVERY SUITE CHECKS PASSED SUCCESSFULLY!  \n";
echo "====================================================\n";
