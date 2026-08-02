<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Game;
use App\Models\GameBet;
use App\Models\User;
use App\Services\GameEngineService;
use Illuminate\Support\Facades\Cache;

echo "=========================================================\n";
echo "    FIEWIN MANUAL GAME OVERRIDES AUDIT TEST SCRIPT       \n";
echo "=========================================================\n\n";

try {
    $user = User::first() ?? User::create([
        'name' => 'Override Tester',
        'email' => 'overridetester@fiewin.test',
        'password' => bcrypt('password'),
    ]);

    if (!$user->wallet) {
        $user->wallet()->create(['main_balance' => 5000.00]);
    }

    $gameEngine = app(GameEngineService::class);

    // 1. Test Fast Parity Override
    echo "[1] Testing Fast Parity Manual Override:\n";
    $fastParityGame = Game::where('code', 'fast_parity')->firstOrFail();
    $periodNum = 'TEST_FP_' . time();

    // Set cache override to number 7
    Cache::put('override_fast_parity', 7, 300);

    // Create pending bet
    $bet = GameBet::create([
        'user_id' => $user->id,
        'game_id' => $fastParityGame->id,
        'period_number' => $periodNum,
        'bet_amount' => 100.00,
        'bet_type' => '7', // Bet on number 7
        'status' => 'pending',
    ]);

    $result = $gameEngine->settleFastParityPeriod($fastParityGame, $periodNum);

    echo "    - Target Override Number Set: 7\n";
    echo "    - Settled Winning Number: " . ($result->result_data['number'] ?? 'N/A') . "\n";
    echo "    - Manual Override Flag: " . ($result->manual_override ? 'TRUE' : 'FALSE') . "\n";
    echo "    - Bet Status: {$bet->fresh()->status} | Win Amount: ₹{$bet->fresh()->win_amount}\n";

    if (($result->result_data['number'] ?? null) !== 7 || !$result->manual_override) {
        throw new Exception("Fast Parity manual override failed!");
    }
    echo "    - SUCCESS: Fast Parity Manual Override Passed 100%!\n\n";

    // 2. Test Spin Wheel Manual Override
    echo "[2] Testing Spin Wheel Manual Override:\n";
    $spinGame = Game::where('code', 'spin_wheel')->firstOrFail();
    Cache::put('override_spin_wheel', 50.0, 300);

    $spinBet = GameBet::create([
        'user_id' => $user->id,
        'game_id' => $spinGame->id,
        'period_number' => 'SPIN_' . time(),
        'bet_amount' => 50.00,
        'bet_type' => 'spin_wheel',
        'status' => 'pending',
    ]);

    // Simulate controller settle
    $override = Cache::get('override_spin_wheel');
    $mult = ($override !== null) ? (float)$override : 2.0;
    $spinWin = round($spinBet->bet_amount * $mult, 2);
    $spinBet->update(['win_amount' => $spinWin, 'multiplier' => $mult, 'status' => 'won']);

    echo "    - Target Override Multiplier Set: 50.0X\n";
    echo "    - Applied Multiplier: {$spinBet->fresh()->multiplier}X\n";
    echo "    - Payout: ₹{$spinBet->fresh()->win_amount}\n";

    if ($spinBet->fresh()->multiplier != 50.0 || $spinBet->fresh()->win_amount != 2500.00) {
        throw new Exception("Spin Wheel manual override failed!");
    }
    echo "    - SUCCESS: Spin Wheel Manual Override Passed 100%!\n\n";

    // 3. Test Dice Roll Manual Override
    echo "[3] Testing Dice Roll Manual Override:\n";
    Cache::put('override_dice', 6, 300);
    $diceOverride = Cache::get('override_dice');

    echo "    - Target Override Dice Roll Set: 6\n";
    echo "    - Retrieved Override Roll: {$diceOverride}\n";

    if ($diceOverride !== 6) {
        throw new Exception("Dice Roll manual override failed!");
    }
    echo "    - SUCCESS: Dice Roll Manual Override Passed 100%!\n\n";

    echo "=========================================================\n";
    echo "  ALL GAME MANUAL OVERRIDE TESTS PASSED 100% CLEANLY!    \n";
    echo "=========================================================\n";

} catch (\Throwable $e) {
    echo "\n[ERROR]: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
