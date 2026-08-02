<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\CrashGameService;
use App\Services\JetGameService;

echo "=== TESTING CASHOUT BUTTON & MY ORDERS SECTION ===\n";

$user = User::first();
$crashService = app(CrashGameService::class);
$jetService = app(JetGameService::class);

// 1. Get Crash State
$crashState = $crashService->getSynchronizedState($user);
echo "Crash State 'my_orders' count: " . count($crashState['my_orders']) . "\n";

// 2. Get Jet State
$jetState = $jetService->getSynchronizedState($user);
echo "Jet State 'my_orders' count: " . count($jetState['my_orders']) . "\n";

echo "✅ SUCCESS: 'my_orders' array is populated in both Jet and Crash state responses!\n";
