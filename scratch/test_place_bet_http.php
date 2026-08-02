<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Player\JetController;
use App\Http\Controllers\Player\CrashController;
use Illuminate\Http\Request;

$user = User::first();
Auth::login($user);

echo "Logged in as User: {$user->name} (ID: {$user->id}, Balance: ₹{$user->wallet->main_balance})\n";

// 1. Test Jet Bet
$reqJet = Request::create('/games/jet/bet', 'POST', [
    'bet_amount' => 100,
    'auto_cashout' => null
]);
$reqJet->setUserResolver(fn() => $user);

$jetController = app(JetController::class);
try {
    $resJet = $jetController->placeBet($reqJet);
    echo "[JET HTTP BET RESPONSE] Status: " . $resJet->getStatusCode() . " Payload: " . $resJet->getContent() . "\n";
} catch (\Throwable $e) {
    echo "[JET HTTP BET EXCEPTION] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

// 2. Test Crash Bet
$reqCrash = Request::create('/games/crash/bet', 'POST', [
    'bet_amount' => 100,
    'auto_cashout' => null
]);
$reqCrash->setUserResolver(fn() => $user);

$crashController = app(CrashController::class);
try {
    $resCrash = $crashController->placeBet($reqCrash);
    echo "[CRASH HTTP BET RESPONSE] Status: " . $resCrash->getStatusCode() . " Payload: " . $resCrash->getContent() . "\n";
} catch (\Throwable $e) {
    echo "[CRASH HTTP BET EXCEPTION] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
