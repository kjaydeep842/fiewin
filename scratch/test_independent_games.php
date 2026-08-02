<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\JetGameService;
use App\Services\CrashGameService;

echo "=== TESTING INDEPENDENT JET & CRASH GAMES ===\n";

$user = User::first();
if (!$user) {
    echo "No user found in database!\n";
    exit(1);
}

$jetService = app(JetGameService::class);
$crashService = app(CrashGameService::class);

// 1. Get Jet State
$jetState = $jetService->getSynchronizedState($user);
echo "Jet Game State:\n";
echo "  - Round ID: " . $jetState['round']['round_id'] . "\n";
echo "  - Status: " . $jetState['round']['status'] . "\n";
echo "  - Multiplier: " . $jetState['current_multiplier'] . "\n";
echo "  - History count: " . count($jetState['history']) . "\n";

// 2. Get Crash State
$crashState = $crashService->getSynchronizedState($user);
echo "\nCrash Rocket Game State:\n";
echo "  - Round ID: " . $crashState['round']['round_id'] . "\n";
echo "  - Status: " . $crashState['round']['status'] . "\n";
echo "  - Multiplier: " . $crashState['current_multiplier'] . "\n";
echo "  - History count: " . count($crashState['history']) . "\n";

// Verify Independence
if (strpos($jetState['round']['round_id'], 'JET_') === 0 && strpos($crashState['round']['round_id'], 'CRASH_') === 0) {
    echo "\n✅ SUCCESS: Jet Round ID starts with JET_ and Crash Round ID starts with CRASH_!\n";
} else {
    echo "\n❌ FAILURE: Round ID prefixes invalid!\n";
}

if ($jetState['round']['round_id'] !== $crashState['round']['round_id']) {
    echo "✅ SUCCESS: Jet Round and Crash Round are completely separate instances!\n";
} else {
    echo "❌ FAILURE: Jet and Crash are sharing the same round!\n";
}

echo "=== TEST COMPLETED SUCCESSFULLY ===\n";
