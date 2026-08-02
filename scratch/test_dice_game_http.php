<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Game;
use App\Http\Controllers\Player\GameController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$user = User::first();
Auth::login($user);

echo "Testing Premium Dice Game for User: {$user->name} (ID: {$user->id})\n";

$game = Game::where('code', 'dice')->firstOrFail();
echo "Game Found: {$game->name} (ID: {$game->id}), Active: {$game->is_active}\n";

$controller = app(GameController::class);

// 1. Test Bet Placement for OVER bet
$reqBet = Request::create('/games/bet', 'POST', [
    'game_id' => $game->id,
    'period_number' => 'DICE_' . time(),
    'bet_amount' => 100,
    'bet_type' => 'over'
]);
$reqBet->headers->set('Accept', 'application/json');
$reqBet->setUserResolver(fn() => $user);

try {
    $resBet = $controller->placeBet($reqBet);
    echo "[BET RESPONSE] Status: " . $resBet->getStatusCode() . " Payload: " . $resBet->getContent() . "\n";
    
    $betData = json_decode($resBet->getContent(), true);
    if (!empty($betData['bet']['id'])) {
        $betId = $betData['bet']['id'];
        
        // 2. Test Settle Dice
        $reqSettle = Request::create('/games/dice/settle', 'POST', [
            'bet_id' => $betId
        ]);
        $reqSettle->headers->set('Accept', 'application/json');
        $reqSettle->setUserResolver(fn() => $user);
        
        $resSettle = $controller->settleDice($reqSettle);
        echo "[SETTLE RESPONSE] Status: " . $resSettle->getStatusCode() . " Payload: " . $resSettle->getContent() . "\n";
    }
} catch (\Throwable $e) {
    echo "[DICE ERROR] " . $e->getMessage() . "\n";
}
