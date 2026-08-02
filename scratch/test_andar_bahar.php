<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\AndarBaharGameService;
use App\Services\AndarBaharSettlementService;
use App\Http\Controllers\Player\AndarBaharController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$user = User::first();
Auth::login($user);

echo "Testing Andar Bahar for User: {$user->name} (ID: {$user->id})\n";

$gameService = app(AndarBaharGameService::class);
$currentRound = $gameService->getCurrentRound();
echo "Current Period: {$currentRound->period_number}, Open Card: {$currentRound->open_card}\n";

$controller = app(AndarBaharController::class);

// 1. Get Game State
$reqState = Request::create('/games/andar-bahar/state', 'GET');
$reqState->setUserResolver(fn() => $user);

try {
    $resState = $controller->getGameState($reqState);
    echo "[STATE RESPONSE] Status: " . $resState->getStatusCode() . " Payload: " . $resState->getContent() . "\n";
} catch (\Throwable $e) {
    echo "[STATE ERROR] " . $e->getMessage() . "\n";
}

// 2. Place Bet on ANDAR
$reqBet = Request::create('/games/andar-bahar/bet', 'POST', [
    'bet_option' => 'andar',
    'amount' => 100
]);
$reqBet->setUserResolver(fn() => $user);

try {
    $resBet = $controller->placeBet($reqBet);
    echo "[BET RESPONSE] Status: " . $resBet->getStatusCode() . " Payload: " . $resBet->getContent() . "\n";
} catch (\Throwable $e) {
    echo "[BET ERROR] " . $e->getMessage() . "\n";
}
