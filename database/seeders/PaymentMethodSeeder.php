<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Razorpay Gateway',
                'code' => 'razorpay',
                'min_amount' => 100.00,
                'max_amount' => 100000.00,
                'bonus_percentage' => 5.00,
                'instructions' => 'Instant auto credit via Netbanking / UPI / Cards.',
                'is_active' => false,
            ],
            [
                'name' => 'PhonePe Direct Gateway',
                'code' => 'phonepe',
                'min_amount' => 100.00,
                'max_amount' => 50000.00,
                'bonus_percentage' => 5.00,
                'instructions' => 'Pay via PhonePe app / UPI intent.',
                'is_active' => false,
            ],
            [
                'name' => 'UPI Fast QR Deposit',
                'code' => 'upi_qr',
                'qr_image' => '/images/qr/upi_sample_qr.png',
                'upi_id' => 'rivexa.pay@upi',
                'min_amount' => 100.00,
                'max_amount' => 50000.00,
                'bonus_percentage' => 10.00,
                'instructions' => 'Scan QR code, enter UTR / Ref Number after payment.',
                'is_active' => true,
            ],
            [
                'name' => 'Manual Bank Deposit',
                'code' => 'manual_bank',
                'min_amount' => 500.00,
                'max_amount' => 200000.00,
                'bonus_percentage' => 8.00,
                'instructions' => 'Transfer to HDFC Bank A/C: 50200012345678, IFSC: HDFC0001234.',
                'is_active' => true,
            ],
        ];

        foreach ($methods as $m) {
            PaymentMethod::firstOrCreate(['code' => $m['code']], $m);
        }
    }
}
