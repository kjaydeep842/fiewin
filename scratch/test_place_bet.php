<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Game;
use App\Http\Controllers\Player\GameController;
use Illuminate\Http\Request;

try {
    $user = User::first();
    auth()->login($user);

    echo "Logged in user: {$user->email} (ID: {$user->id})\n";
    echo "Wallet balance: ₹" . ($user->wallet ? $user->wallet->main_balance : '0') . "\n";

    $game = Game::where('code', 'fast_parity')->firstOrFail();

    // Test 1: Amount exceeding balance
    $req1 = Request::create('/games/bet', 'POST', [
        'game_id' => $game->id,
        'period_number' => '202608029999',
        'bet_amount' => 99999999,
        'bet_type' => 'red',
    ]);
    $req1->headers->set('Accept', 'application/json');
    $req1->headers->set('X-Requested-With', 'XMLHttpRequest');

    $controller = app(GameController::class);
    $res1 = $controller->placeBet($req1);

    echo "Test 1 (Over balance) Status: " . $res1->getStatusCode() . "\n";
    echo "Test 1 Content: " . $res1->getContent() . "\n\n";

    // Test 2: Valid amount
    $req2 = Request::create('/games/bet', 'POST', [
        'game_id' => $game->id,
        'period_number' => '202608029999',
        'bet_amount' => 10,
        'bet_type' => 'red',
    ]);
    $req2->headers->set('Accept', 'application/json');
    $req2->headers->set('X-Requested-With', 'XMLHttpRequest');

    $res2 = $controller->placeBet($req2);

    echo "Test 2 (Valid) Status: " . $res2->getStatusCode() . "\n";
    echo "Test 2 Content: " . $res2->getContent() . "\n";

} catch (\Throwable $e) {
    echo "EXCEPTION THROWN: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
