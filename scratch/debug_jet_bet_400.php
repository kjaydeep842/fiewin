<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JetRound;
use App\Models\JetSetting;
use App\Models\User;
use App\Services\JetGameService;
use App\Services\JetRoundService;
use App\Services\WalletService;

$round = JetRound::orderBy('id', 'desc')->first();
$settings = JetSetting::getSettings();
$user = User::first();

echo "=== JET BET DIAGNOSTIC ===\n";
echo "Round Status    : {$round->status}\n";
echo "Round ID        : {$round->round_id}\n";
echo "Round started_at: {$round->started_at}\n";

$nowTs = time();
$startedTs = $round->started_at ? $round->started_at->timestamp : $nowTs;
$elapsed = max(0, $nowTs - $startedTs);
echo "Elapsed (secs)  : {$elapsed}\n";
echo "Min Bet         : ₹{$settings->min_bet}\n";
echo "Max Bet         : ₹{$settings->max_bet}\n";

// Simulate what placeBet checks
$amount = 100.0;

if ($round->status !== 'BETTING_OPEN') {
    echo "\n❌ FAIL: Betting is closed — status is '{$round->status}', not BETTING_OPEN\n";
} else {
    echo "\n✅ PASS: Status is BETTING_OPEN\n";
}

if ($amount < $settings->min_bet || $amount > $settings->max_bet) {
    echo "❌ FAIL: Amount ₹{$amount} out of range [{$settings->min_bet}-{$settings->max_bet}]\n";
} else {
    echo "✅ PASS: Amount ₹{$amount} is within range\n";
}

if ($user) {
    $existing = \App\Models\JetBet::where('jet_round_id', $round->id)->where('user_id', $user->id)->first();
    if ($existing) {
        echo "❌ FAIL: User {$user->id} already has bet in this round (bet #{$existing->id}, status: {$existing->status})\n";
    } else {
        echo "✅ PASS: No existing bet for user {$user->id} in this round\n";
    }

    $wallet = $user->wallet;
    if (!$wallet) {
        echo "❌ FAIL: User has no wallet!\n";
    } elseif ($wallet->main_balance < $amount) {
        echo "❌ FAIL: Insufficient balance — has ₹{$wallet->main_balance}, needs ₹{$amount}\n";
    } else {
        echo "✅ PASS: Wallet balance ₹{$wallet->main_balance} sufficient\n";
    }
}
