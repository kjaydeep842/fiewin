<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\CrashGameService;
use App\Services\JetGameService;

$user = User::first();

echo "=== TESTING CRASH STATE SERVICE ===\n";
$crashService = app(CrashGameService::class);
$crashState = $crashService->getSynchronizedState($user);
echo json_encode($crashState, JSON_PRETTY_PRINT) . "\n\n";

echo "=== TESTING JET STATE SERVICE ===\n";
$jetService = app(JetGameService::class);
$jetState = $jetService->getSynchronizedState($user);
echo json_encode($jetState, JSON_PRETTY_PRINT) . "\n";
