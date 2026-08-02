<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\AndarBaharRound;
use App\Models\AndarBaharBet;
use App\Services\AndarBaharGameService;
use Carbon\Carbon;

$user = User::first();
echo "Testing Andar Bahar Settlement for User: {$user->name} (ID: {$user->id})\n";

$gameService = app(AndarBaharGameService::class);
$periodNumber = $gameService->generateSequentialPeriodNumber();

$round = AndarBaharRound::create([
    'period_number' => $periodNumber,
    'open_card' => $gameService->getRandomCard(),
    'status' => 'betting',
    'started_at' => Carbon::now()->subSeconds(65),
]);

$betAndar = AndarBaharBet::create([
    'user_id' => $user->id,
    'andar_bahar_round_id' => $round->id,
    'period_number' => $periodNumber,
    'bet_option' => 'andar',
    'bet_amount' => 100,
    'status' => 'pending'
]);

echo "Created test pending bet on period #{$periodNumber} for ANDAR ₹100\n";

$result = $gameService->settleRound($round);
$betAndar->refresh();

echo "[SETTLEMENT RESULT] Winner: " . strtoupper($result->winner) . ", Deal Count: {$result->deal_count}, Winning Card: {$result->winning_card}\n";
echo "[BET STATUS] Status: {$betAndar->status}, Win Amount: ₹{$betAndar->win_amount}\n";
