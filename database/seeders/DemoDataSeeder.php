<?php

namespace Database\Seeders;

use App\Helpers\GameHelper;
use App\Models\BankAccount;
use App\Models\Deposit;
use App\Models\Game;
use App\Models\GameBet;
use App\Models\GameResult;
use App\Models\Referral;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Repositories\UserRepository;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $userRepo = new UserRepository();

        // 1. Create Tier 1 Referrer User
        $topUser = $userRepo->createPlayer([
            'name' => 'Rahul Sharma',
            'email' => 'rahul@example.com',
            'mobile' => '9876543210',
            'password' => bcrypt('password123'),
        ]);

        $topWallet = Wallet::where('user_id', $topUser->id)->first();
        $topWallet->update(['main_balance' => 2500.00, 'commission_balance' => 450.00]);

        // 2. Create Level 1 Referral User
        $subUser1 = $userRepo->createPlayer([
            'name' => 'Vikram Singh',
            'email' => 'vikram@example.com',
            'mobile' => '9876543211',
            'password' => bcrypt('password123'),
            'referred_by' => $topUser->id,
        ]);

        Referral::create(['referrer_id' => $topUser->id, 'referee_id' => $subUser1->id, 'level' => 1, 'total_commission_earned' => 150.00]);

        // 3. Create Level 2 Referral User
        $subUser2 = $userRepo->createPlayer([
            'name' => 'Amit Kumar',
            'email' => 'amit@example.com',
            'mobile' => '9876543212',
            'password' => bcrypt('password123'),
            'referred_by' => $subUser1->id,
        ]);

        Referral::create(['referrer_id' => $topUser->id, 'referee_id' => $subUser2->id, 'level' => 2, 'total_commission_earned' => 50.00]);
        Referral::create(['referrer_id' => $subUser1->id, 'referee_id' => $subUser2->id, 'level' => 1, 'total_commission_earned' => 100.00]);

        // Add Bank Account for Rahul
        BankAccount::create([
            'user_id' => $topUser->id,
            'account_holder' => 'RAHUL SHARMA',
            'bank_name' => 'HDFC BANK',
            'account_number' => '5010023456789',
            'ifsc_code' => 'HDFC0001234',
            'upi_id' => 'rahul@paytm',
            'is_primary' => true,
        ]);

        // Create sample deposits & withdrawals
        Deposit::create([
            'user_id' => $topUser->id,
            'transaction_id' => 'DEP_DEMO_001',
            'payment_method' => 'upi_qr',
            'amount' => 1000.00,
            'bonus_amount' => 100.00,
            'utr_number' => 'UTR987654321',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        Withdrawal::create([
            'user_id' => $topUser->id,
            'transaction_id' => 'WD_DEMO_001',
            'amount' => 500.00,
            'fee' => 5.00,
            'net_amount' => 495.00,
            'upi_id' => 'rahul@paytm',
            'status' => 'approved',
            'processed_at' => now(),
        ]);

        // Create sample Fast Parity results and bets
        $fastParity = Game::where('code', 'fast_parity')->first();
        if ($fastParity) {
            for ($i = 20; $i >= 1; $i--) {
                $basePeriod = (int)GameHelper::generatePeriodNumber('fast_parity', 30);
                $period = (string)($basePeriod - $i);
                $num = rand(0, 9);
                $resDetails = GameHelper::getParityColorResult($num);

                $result = GameResult::create([
                    'game_id' => $fastParity->id,
                    'period_number' => (string)$period,
                    'result_data' => array_merge($resDetails, ['number' => $num]),
                    'status' => 'settled',
                    'settled_at' => now()->subMinutes($i),
                ]);

                // Create a bet for Rahul
                GameBet::create([
                    'user_id' => $topUser->id,
                    'game_id' => $fastParity->id,
                    'game_result_id' => $result->id,
                    'period_number' => (string)$period,
                    'bet_amount' => 100.00,
                    'bet_type' => 'green',
                    'win_amount' => in_array('green', $resDetails['colors']) ? 200.00 : 0.00,
                    'multiplier' => in_array('green', $resDetails['colors']) ? 2.00 : 0.00,
                    'status' => in_array('green', $resDetails['colors']) ? 'won' : 'lost',
                ]);
            }
        }
    }
}
