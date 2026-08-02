<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\JetGameService;
use App\Services\CrashGameService;

$user = User::first();
echo "Testing Place Bet for User: {$user->name} (ID: {$user->id})\n";

try {
    $jetService = app(JetGameService::class);
    $resJet = $jetService->placeBet($user, 100);
    echo "[JET BET SUCCESS] Bet ID: {$resJet['bet']['id']}, Round: {$resJet['bet']['round_id']}\n";
} catch (\Throwable $e) {
    echo "[JET BET ERROR] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

try {
    $crashService = app(CrashGameService::class);
    $resCrash = $crashService->placeBet($user, 100);
    echo "[CRASH BET SUCCESS] Bet ID: {$resCrash['bet']['id']}, Round: {$resCrash['bet']['round_id']}\n";
} catch (\Throwable $e) {
    echo "[CRASH BET ERROR] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
