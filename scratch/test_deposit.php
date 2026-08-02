<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\ManualDepositService;

try {
    $user = User::first();
    echo "Testing Deposit Request for User: {$user->email} (ID: {$user->id})\n";

    $depositService = app(ManualDepositService::class);

    // Test UPI Manual Deposit
    $req1 = $depositService->createDepositRequest($user, 500.00, 'upi');
    echo "UPI Deposit Created: ID={$req1->deposit_id}, Merchant={$req1->merchantAccount->name}, UPI={$req1->merchantAccount->upi_id}\n";

    // Test Bank Manual Deposit
    $req2 = $depositService->createDepositRequest($user, 1000.00, 'bank_transfer');
    echo "Bank Deposit Created: ID={$req2->deposit_id}, Merchant={$req2->merchantAccount->name}, Bank={$req2->merchantAccount->bank_name}\n";

} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
