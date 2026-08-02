<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\CrashRound;
use App\Models\CrashBet;
use App\Models\JetRound;
use App\Models\JetBet;
use App\Services\CrashGameService;
use App\Services\JetGameService;
use Carbon\Carbon;

$user = User::first();
echo "Testing Cashout Flow for User: {$user->name} (ID: {$user->id})\n";

// 1. Create a simulated active FLYING round for Crash
$crashRound = CrashRound::create([
    'round_id' => 'CRASH_TEST_' . time(),
    'crash_multiplier' => 10.00,
    'started_at' => Carbon::now(),
    'status' => 'FLYING',
]);

$crashBet = CrashBet::create([
    'crash_round_id' => $crashRound->id,
    'round_id' => $crashRound->round_id,
    'user_id' => $user->id,
    'bet_amount' => 100,
    'status' => 'flying',
]);

echo "Created Active Crash Bet ID: {$crashBet->id} on Round: {$crashRound->round_id}\n";

$crashService = app(CrashGameService::class);
try {
    $resCrash = $crashService->processCashout($user, $crashBet->id, 1.85);
    echo "[CRASH CASHOUT SUCCESS] Payload: " . json_encode($resCrash) . "\n";
} catch (\Throwable $e) {
    echo "[CRASH CASHOUT EXCEPTION] " . $e->getMessage() . "\n";
}

// 2. Create a simulated active FLYING round for Jet
$jetRound = JetRound::create([
    'round_id' => 'JET_TEST_' . time(),
    'crash_multiplier' => 10.00,
    'started_at' => Carbon::now(),
    'status' => 'FLYING',
]);

$jetBet = JetBet::create([
    'jet_round_id' => $jetRound->id,
    'round_id' => $jetRound->round_id,
    'user_id' => $user->id,
    'bet_amount' => 100,
    'status' => 'flying',
]);

echo "Created Active Jet Bet ID: {$jetBet->id} on Round: {$jetRound->round_id}\n";

$jetService = app(JetGameService::class);
try {
    $resJet = $jetService->processCashout($user, $jetBet->id, 1.85);
    echo "[JET CASHOUT SUCCESS] Payload: " . json_encode($resJet) . "\n";
} catch (\Throwable $e) {
    echo "[JET CASHOUT EXCEPTION] " . $e->getMessage() . "\n";
}
