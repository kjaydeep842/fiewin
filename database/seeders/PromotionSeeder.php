<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        Promotion::firstOrCreate(
            ['code' => 'WELCOME100'],
            [
                'title' => '100% First Deposit Match Bonus',
                'banner' => '/images/promotions/banner_welcome.png',
                'description' => 'Get up to Rs 1,000 extra on your first deposit!',
                'bonus_type' => 'percentage',
                'bonus_amount' => 100.00,
                'min_deposit' => 200.00,
                'is_active' => true,
            ]
        );

        Promotion::firstOrCreate(
            ['code' => 'DAILYLUCKY'],
            [
                'title' => 'Daily Lucky Spin Reward',
                'banner' => '/images/promotions/banner_lucky.png',
                'description' => 'Log in daily and get a free spin to win up to Rs 500 cash reward.',
                'bonus_type' => 'fixed',
                'bonus_amount' => 50.00,
                'min_deposit' => 0.00,
                'is_active' => true,
            ]
        );

        Coupon::firstOrCreate(
            ['code' => 'GAMEHUB50'],
            [
                'amount' => 50.00,
                'min_deposit' => 0.00,
                'usage_limit' => 500,
                'times_used' => 12,
                'is_active' => true,
            ]
        );
    }
}
