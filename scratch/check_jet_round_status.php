<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JetRound;
use App\Models\JetSetting;

$round = JetRound::orderBy('id', 'desc')->first();
if (!$round) {
    echo "No round found.\n";
    exit;
}

$settings = JetSetting::getSettings();
$nowTs = time();
$startedTs = $round->started_at ? $round->started_at->timestamp : $nowTs;
$elapsedSeconds = max(0, $nowTs - $startedTs);

echo "=== CURRENT JET ROUND STATUS ===\n";
echo "Round ID   : {$round->round_id}\n";
echo "Status     : {$round->status}\n";
echo "Started At : {$round->started_at}\n";
echo "Elapsed    : {$elapsedSeconds} seconds\n";
echo "Crash Mult : {$round->crash_multiplier}x\n";
echo "Min Bet    : ₹{$settings->min_bet}\n";
echo "Max Bet    : ₹{$settings->max_bet}\n";
echo "Betting window (COUNTDOWN_SECONDS): 5\n";

if ($round->status === 'BETTING_OPEN') {
    $remaining = max(0, 5 - $elapsedSeconds);
    echo "Betting OPEN — {$remaining}s remaining before flight\n";
} elseif ($round->status === 'FLYING') {
    $currentMult = 1.00 + ($elapsedSeconds * 0.35);
    echo "FLYING — Current multiplier: " . round($currentMult, 2) . "x\n";
    echo "Betting is CLOSED while flying.\n";
} else {
    echo "Status: {$round->status} — Waiting for next round.\n";
}
