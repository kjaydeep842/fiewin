<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\JetGameService;

$user = User::first();
$jetService = app(JetGameService::class);

echo "=== INITIAL STATE ===\n";
$state1 = $jetService->getSynchronizedState($user);
echo "Current Round Status: " . $state1['round']['status'] . "\n";
echo "Initial user_bet: " . json_encode($state1['user_bet']) . "\n";

echo "\n=== PLACING BET ===\n";
try {
    $res = $jetService->placeBet($user, 100, 2.00);
    echo "Place Bet Result: " . json_encode($res) . "\n";
} catch (\Throwable $e) {
    echo "Place Bet Exception: " . $e->getMessage() . "\n";
}

echo "\n=== POST-BET STATE ===\n";
$state2 = $jetService->getSynchronizedState($user);
echo "Post-bet user_bet: " . json_encode($state2['user_bet']) . "\n";
