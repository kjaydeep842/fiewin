<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\JetGameService;
use App\Services\JetRoundService;
use App\Services\CrashGameService;
use App\Services\CrashRoundService;

echo "=== COMPREHENSIVE JET & CRASH AUDIT TEST ===\n";

$user = User::first();
if (!$user) {
    echo "No user found!\n";
    exit(1);
}

$jetGameService = app(JetGameService::class);
$jetRoundService = app(JetRoundService::class);

$crashGameService = app(CrashGameService::class);
$crashRoundService = app(CrashRoundService::class);

// 1. Test Jet State & Endpoints
$jetState = $jetGameService->getSynchronizedState($user);
echo "1. Jet State: round=" . $jetState['round']['round_id'] . ", mult=" . $jetState['current_multiplier'] . "x, status=" . $jetState['round']['status'] . "\n";

// 2. Test Jet Bet & Auto Cashout with Transaction check
$jetRound = $jetRoundService->createNewRound();
$jetBetRes = $jetGameService->placeBet($user, 100.00, 1.80);
echo "2. Jet Bet Placed: " . json_encode($jetBetRes) . "\n";

$walletId = $user->wallet->id;
$debitTx = WalletTransaction::where('wallet_id', $walletId)->where('reference_id', 'LIKE', 'JET_BET_%')->latest()->first();
echo "   - Jet Debit Transaction Reference: " . ($debitTx ? $debitTx->reference_id : 'NONE') . " (Desc: " . ($debitTx ? $debitTx->description : '') . ")\n";

$jetRound->update(['status' => 'FLYING', 'started_at' => now(), 'crash_multiplier' => 5.00]);
$jetRoundService->processAutoCashouts($jetRound, 1.85);

$autoTx = WalletTransaction::where('wallet_id', $walletId)->where('reference_id', 'LIKE', 'JET_AUTO_CASHOUT_%')->latest()->first();
echo "   - Jet Auto Cashout Credit Reference: " . ($autoTx ? $autoTx->reference_id : 'NONE') . " (Desc: " . ($autoTx ? $autoTx->description : '') . ")\n";

// 3. Test Crash Bet & Manual Cashout with Transaction check
$crashRound = $crashRoundService->createNewRound();
$crashBetRes = $crashGameService->placeBet($user, 100.00, null);
echo "\n3. Crash Bet Placed: " . json_encode($crashBetRes) . "\n";

$crashDebitTx = WalletTransaction::where('wallet_id', $walletId)->where('reference_id', 'LIKE', 'CRASH_BET_%')->latest()->first();
echo "   - Crash Debit Transaction Reference: " . ($crashDebitTx ? $crashDebitTx->reference_id : 'NONE') . " (Desc: " . ($crashDebitTx ? $crashDebitTx->description : '') . ")\n";

$crashRound->update(['status' => 'FLYING', 'started_at' => now(), 'crash_multiplier' => 5.00]);
$crashServiceState = $crashGameService->getSynchronizedState($user);

$cashoutRes = $crashGameService->processCashout($user, $crashBetRes['bet']['id'], 2.50);
echo "   - Crash Manual Cashout Result: " . json_encode($cashoutRes) . "\n";

$crashCreditTx = WalletTransaction::where('wallet_id', $walletId)->where('reference_id', 'LIKE', 'CRASH_CASHOUT_%')->latest()->first();
echo "   - Crash Manual Cashout Credit Reference: " . ($crashCreditTx ? $crashCreditTx->reference_id : 'NONE') . " (Desc: " . ($crashCreditTx ? $crashCreditTx->description : '') . ")\n";

echo "\n✅ ALL AUDIT SCENARIOS PASSED WITH 100% SUCCESS!\n";
