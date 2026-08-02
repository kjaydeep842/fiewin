<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\JetRoundService;

$rs = app(JetRoundService::class);
$round = $rs->getOrSyncActiveRound();

$nowTs = time();
$startedTs = $round->started_at ? $round->started_at->timestamp : $nowTs;
$elapsed = max(0, $nowTs - $startedTs);
$remaining = max(0, JetRoundService::COUNTDOWN_SECONDS - $elapsed);

echo "=== NEW ROUND VERIFICATION ===\n";
echo "Round Status       : {$round->status}\n";
echo "Round ID           : {$round->round_id}\n";
echo "Betting Window     : " . JetRoundService::COUNTDOWN_SECONDS . " seconds\n";
echo "Elapsed            : {$elapsed} seconds\n";
echo "Remaining (Bet)    : {$remaining} seconds\n";
echo "Crash Multiplier   : {$round->crash_multiplier}x\n";

if ($round->status === 'BETTING_OPEN' && $remaining > 5) {
    echo "\n✅ SUCCESS: Player has {$remaining}s to place a bet!\n";
} else {
    echo "\n⚠️  Betting window: {$remaining}s\n";
}
