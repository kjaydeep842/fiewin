<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PaymentMethod;

// Deactivate fake gateway extra methods
PaymentMethod::whereIn('code', ['razorpay', 'phonepe'])->update(['is_active' => false]);

// Ensure UPI Fast QR Deposit and Manual Bank Deposit are active
PaymentMethod::updateOrCreate(
    ['code' => 'upi_qr'],
    ['name' => 'UPI Fast QR Deposit', 'bonus_percentage' => 10.00, 'is_active' => true]
);

PaymentMethod::updateOrCreate(
    ['code' => 'manual_bank'],
    ['name' => 'Manual Bank Deposit', 'bonus_percentage' => 8.00, 'is_active' => true]
);

echo "Payment methods updated successfully!\n";
$activeMethods = PaymentMethod::where('is_active', true)->get();
foreach ($activeMethods as $m) {
    echo "- Active Method: {$m->name} (Code: {$m->code}, Bonus: {$m->bonus_percentage}%)\n";
}
