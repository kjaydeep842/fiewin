<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JetRound;
use App\Services\JetRoundService;
use Carbon\Carbon;

// Reset all non-settled rounds to CRASHED with ended_at in the past
JetRound::whereIn('status', ['BETTING_OPEN', 'FLYING'])
    ->update(['status' => 'CRASHED', 'ended_at' => Carbon::now()->subMinutes(5)]);

echo "All stuck rounds reset.\n";

// Now create a fresh BETTING_OPEN round
$rs = app(JetRoundService::class);
$round = $rs->getOrSyncActiveRound();

$nowTs = time();
$startedTs = $round->started_at ? $round->started_at->timestamp : $nowTs;
$elapsed = max(0, $nowTs - $startedTs);
$remaining = max(0, JetRoundService::COUNTDOWN_SECONDS - $elapsed);

echo "=== FRESH ROUND ===\n";
echo "Status     : {$round->status}\n";
echo "Round ID   : {$round->round_id}\n";
echo "Elapsed    : {$elapsed}s\n";
echo "Remaining  : {$remaining}s to bet\n";
echo "Window     : " . JetRoundService::COUNTDOWN_SECONDS . "s\n";

if ($round->status === 'BETTING_OPEN') {
    echo "\n✅ SUCCESS: Fresh BETTING_OPEN round with {$remaining}s betting window!\n";
} else {
    echo "\n⚠️ Status is {$round->status}\n";
}
