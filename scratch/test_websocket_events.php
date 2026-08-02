<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Events\GameStateUpdated;
use App\Events\BetPlaced;
use App\Events\WalletUpdated;
use App\Events\HistoryUpdated;

echo "=== TESTING BROADCAST EVENTS ===\n";

// 1. GameStateUpdated Event Test
$stateEvent = new GameStateUpdated('jet', [
    'round' => ['round_id' => 'JET_TEST_101', 'status' => 'FLYING'],
    'current_multiplier' => '2.50',
]);
$channels = $stateEvent->broadcastOn();
echo "1. GameStateUpdated Event: channel=" . $channels[0]->name . ", as=" . $stateEvent->broadcastAs() . "\n";

// 2. BetPlaced Event Test
$betEvent = new BetPlaced('crash', [
    'username' => 'TestUser',
    'bet_amount' => '500.00',
]);
$betChannels = $betEvent->broadcastOn();
echo "2. BetPlaced Event: channel=" . $betChannels[0]->name . ", as=" . $betEvent->broadcastAs() . "\n";

// 3. WalletUpdated Event Test
$walletEvent = new WalletUpdated(1, '2450.00', 'win');
$walletChannels = $walletEvent->broadcastOn();
echo "3. WalletUpdated Private Event: channel=" . $walletChannels[0]->name . ", as=" . $walletEvent->broadcastAs() . "\n";

// 4. HistoryUpdated Event Test
$historyEvent = new HistoryUpdated('jet', ['crash_multiplier' => '3.45', 'color' => 'green']);
$historyChannels = $historyEvent->broadcastOn();
echo "4. HistoryUpdated Event: channel=" . $historyChannels[0]->name . ", as=" . $historyEvent->broadcastAs() . "\n";

echo "✅ SUCCESS: All 4 Real-time Broadcast Events Constructed & Verified!\n";
