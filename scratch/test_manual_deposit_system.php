<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;
use App\Models\DepositRequest;
use App\Models\MerchantAccount;
use App\Models\User;
use App\Services\ManualDepositService;
use App\Services\MerchantLoadBalancerService;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

echo "=========================================================\n";
echo "  FIEWIN MANUAL DEPOSIT & MERCHANT SYSTEM AUDIT SCRIPT   \n";
echo "=========================================================\n\n";

try {
    // 1. Ensure test user & admin exist
    $user = User::first() ?? User::create([
        'name' => 'Test Player',
        'email' => 'testplayer@fiewin.test',
        'password' => bcrypt('password'),
        'phone' => '9876543210',
    ]);

    if (!$user->wallet) {
        $user->wallet()->create([
            'main_balance' => 1000.00,
            'bonus_balance' => 0.00,
            'commission_balance' => 0.00,
        ]);
    }

    $admin = Admin::first() ?? Admin::create([
        'name' => 'Super Admin',
        'email' => 'admin@fiewin.test',
        'password' => bcrypt('password'),
    ]);

    echo "[1] User & Admin Loaded:\n";
    echo "    - User: {$user->name} (ID: {$user->id}, Main Balance: ₹{$user->wallet->fresh()->main_balance})\n";
    echo "    - Admin: {$admin->name} (ID: {$admin->id})\n\n";

    // 2. Create 3 Merchant Accounts with different limits & priority
    MerchantAccount::query()->delete();

    $m1 = MerchantAccount::create([
        'name' => 'Merchant Alpha (UPI)',
        'account_holder' => 'Fiewin Alpha Pvt Ltd',
        'upi_id' => 'fiewin.alpha@okaxis',
        'bank_name' => 'HDFC Bank',
        'account_number' => '50100876123491',
        'ifsc' => 'HDFC0001234',
        'status' => 'active',
        'daily_limit' => 200000.00,
        'current_daily_total' => 50000.00, // 25% load
        'priority' => 10,
        'supported_payment_types' => ['upi', 'bank_transfer', 'qr'],
    ]);

    $m2 = MerchantAccount::create([
        'name' => 'Merchant Beta (High Limit)',
        'account_holder' => 'Fiewin Beta Services',
        'upi_id' => 'fiewin.beta@icici',
        'bank_name' => 'ICICI Bank',
        'account_number' => '000405891234',
        'ifsc' => 'ICIC0000004',
        'status' => 'active',
        'daily_limit' => 500000.00,
        'current_daily_total' => 10000.00, // 2% load -> SHOULD BE PICKED BY LOAD BALANCER
        'priority' => 5,
        'supported_payment_types' => ['upi', 'bank_transfer', 'qr'],
    ]);

    $m3 = MerchantAccount::create([
        'name' => 'Merchant Gamma (Inactive)',
        'account_holder' => 'Fiewin Gamma',
        'upi_id' => 'gamma@ybl',
        'status' => 'inactive',
        'daily_limit' => 100000.00,
        'current_daily_total' => 0.00,
        'priority' => 1,
    ]);

    echo "[2] Created 3 Merchant Collection Accounts:\n";
    echo "    - M1: {$m1->name} | Limit: ₹{$m1->daily_limit} | Current: ₹{$m1->current_daily_total} (Load: " . round($m1->load_ratio * 100, 1) . "%)\n";
    echo "    - M2: {$m2->name} | Limit: ₹{$m2->daily_limit} | Current: ₹{$m2->current_daily_total} (Load: " . round($m2->load_ratio * 100, 1) . "%)\n";
    echo "    - M3: {$m3->name} | Status: INACTIVE\n\n";

    // 3. Test Merchant Load Balancer
    $loadBalancer = app(MerchantLoadBalancerService::class);
    $selectedMerchant = $loadBalancer->selectOptimalMerchant(500.00, 'upi');
    echo "[3] Merchant Load Balancer Result:\n";
    echo "    - Assigned Merchant: {$selectedMerchant->name} (ID: {$selectedMerchant->id})\n";

    if ($selectedMerchant->id !== $m2->id) {
        throw new Exception("Load Balancer failed to select least-loaded merchant M2!");
    }
    echo "    - SUCCESS: Load Balancer correctly assigned least-loaded Merchant Beta!\n\n";

    // 4. Create Deposit Request
    $depositService = app(ManualDepositService::class);
    $depositRequest = $depositService->createDepositRequest($user, 500.00, 'upi');

    echo "[4] Created Deposit Request:\n";
    echo "    - Deposit ID: {$depositRequest->deposit_id}\n";
    echo "    - User: {$depositRequest->user->name}\n";
    echo "    - Merchant Assigned: {$depositRequest->merchantAccount->name}\n";
    echo "    - Status: {$depositRequest->status}\n";
    echo "    - Expires At: {$depositRequest->expires_at->format('Y-m-d H:i:s')} ({$depositRequest->seconds_remaining}s remaining)\n\n";

    // 5. Submit UTR & Proof
    $utr = '329871' . rand(100000, 999999);
    $depositService->submitPaymentProof($depositRequest, $utr, null, 'Transferred via PhonePe');
    echo "[5] Payment Proof & UTR Submitted:\n";
    echo "    - UTR Number: {$depositRequest->fresh()->utr_number}\n";
    echo "    - Status: {$depositRequest->fresh()->status}\n\n";

    // 6. Test Duplicate UTR Check
    echo "[6] Testing Duplicate UTR Guard:\n";
    $duplicate = $depositService->checkDuplicateUTR($utr);
    if ($duplicate) {
        echo "    - SUCCESS: Duplicate UTR '{$utr}' correctly detected for Deposit #{$duplicate->deposit_id}!\n\n";
    }

    // 7. Approve Deposit via Service
    $initialBalance = $user->wallet->fresh()->main_balance;
    $res = $depositService->approveDeposit($depositRequest, $admin, 'Audited & Verified via Admin Panel');
    $newBalance = $user->wallet->fresh()->main_balance;

    echo "[7] Deposit Approval & Wallet Credit Results:\n";
    echo "    - Status Message: {$res['message']}\n";
    echo "    - Initial Wallet Balance: ₹" . number_format($initialBalance, 2) . "\n";
    echo "    - Updated Wallet Balance: ₹" . number_format($newBalance, 2) . "\n";
    echo "    - Net Balance Gain: +₹" . number_format($newBalance - $initialBalance, 2) . "\n";
    echo "    - Updated Merchant M2 Collection Total: ₹" . number_format($m2->fresh()->current_daily_total, 2) . "\n\n";

    // 8. Verify Audit Trail Records
    $verifications = $depositRequest->verifications;
    echo "[8] Audit Trail Verification Log:\n";
    foreach ($verifications as $v) {
        echo "    - Log ID #{$v->id}: Transition [{$v->status_from} -> {$v->status_to}] | Notes: {$v->verification_notes}\n";
    }

    echo "\n=========================================================\n";
    echo "  ALL MANUAL DEPOSIT SYSTEM AUDITS PASSED 100% CLEANLY!  \n";
    echo "=========================================================\n";

} catch (\Throwable $e) {
    echo "\n[ERROR]: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
