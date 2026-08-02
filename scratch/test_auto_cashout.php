<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\JetGameService;
use App\Services\CrashGameService;
use App\Models\JetRound;
use App\Models\CrashRound;
use App\Services\JetRoundService;
use App\Services\CrashRoundService;

echo "=== TESTING SERVER-SIDE AUTO CASHOUT ENGINE ===\n";

$user = User::first();
if (!$user) {
    echo "No user found!\n";
    exit(1);
}

$jetGameService = app(JetGameService::class);
$jetRoundService = app(JetRoundService::class);

$crashGameService = app(CrashGameService::class);
$crashRoundService = app(CrashRoundService::class);

// 1. Place Jet Bet with Auto Cashout = 1.50x
$jetRound = $jetRoundService->createNewRound();
$betRes = $jetGameService->placeBet($user, 100.00, 1.50);
echo "Jet Bet Placed with Auto Cashout 1.50x: " . json_encode($betRes) . "\n";

// Transition to FLYING and simulate auto cashout trigger at 1.55x
$jetRound->update(['status' => 'FLYING', 'started_at' => now(), 'crash_multiplier' => 5.00]);
$jetRoundService->processAutoCashouts($jetRound, 1.55);

$checkBet = \App\Models\JetBet::find($betRes['bet']['id']);
echo "Jet Bet Status After Auto Cashout Trigger: " . $checkBet->status . " (Mult: " . $checkBet->cashout_multiplier . "x, Profit: ₹" . $checkBet->profit . ")\n";
if ($checkBet->status === 'cashed_out' && (float)$checkBet->cashout_multiplier === 1.50) {
    echo "✅ SUCCESS: Jet Server-Side Auto Cashout Executed Successfully!\n";
}

// 2. Place Crash Bet with Auto Cashout = 2.00x
$crashRound = $crashRoundService->createNewRound();
$betRes = $crashGameService->placeBet($user, 100.00, 2.00);
echo "\nCrash Bet Placed with Auto Cashout 2.00x: " . json_encode($betRes) . "\n";

// Transition to FLYING and simulate auto cashout trigger at 2.10x
$crashRound->update(['status' => 'FLYING', 'started_at' => now(), 'crash_multiplier' => 5.00]);
$crashRoundService->processAutoCashouts($crashRound, 2.10);

$checkBet = \App\Models\CrashBet::find($betRes['bet']['id']);
echo "Crash Bet Status After Auto Cashout Trigger: " . $checkBet->status . " (Mult: " . $checkBet->cashout_multiplier . "x, Profit: ₹" . $checkBet->profit . ")\n";
if ($checkBet->status === 'cashed_out' && (float)$checkBet->cashout_multiplier === 2.00) {
    echo "✅ SUCCESS: Crash Server-Side Auto Cashout Executed Successfully!\n";
}

echo "=== TEST COMPLETED ===\n";
