<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameCategory;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $fastCat = GameCategory::where('slug', 'fast-games')->first();
        $crashCat = GameCategory::where('slug', 'crash-jet')->first();
        $colorCat = GameCategory::where('slug', 'color-prediction')->first();
        $casinoCat = GameCategory::where('slug', 'casino-dice')->first();

        $games = [
            [
                'name' => 'Fast Parity',
                'slug' => 'fast-parity',
                'code' => 'fast_parity',
                'category_id' => $fastCat?->id,
                'image' => '/images/games/fast_parity.png',
                'icon' => 'bi-lightning',
                'min_entry_fee' => 10.00,
                'max_entry_fee' => 50000.00,
                'win_multiplier' => 2.00,
                'rtp_percentage' => 96.00,
                'is_active' => true,
                'rules' => 'Predict Red, Green, Violet or Number 0-9. Period rotates every 30 seconds.',
                'instruction' => 'Choose your bet color or number before countdown ends.',
                'config' => ['timer_seconds' => 30],
            ],
            [
                'name' => 'Mines',
                'slug' => 'mines',
                'code' => 'mines',
                'category_id' => $fastCat?->id,
                'image' => '/images/games/mines.png',
                'icon' => 'bi-pin-angle',
                'min_entry_fee' => 20.00,
                'max_entry_fee' => 100000.00,
                'win_multiplier' => 1.20,
                'rtp_percentage' => 97.00,
                'is_active' => true,
                'rules' => 'Uncover tiles on a 5x5 grid without hitting bombs to increase your payout multiplier.',
                'instruction' => 'Cash out at any time to claim your accumulated winnings.',
                'config' => ['grid_size' => 25, 'default_mines' => 3],
            ],
            [
                'name' => 'Crash',
                'slug' => 'crash',
                'code' => 'crash',
                'category_id' => $crashCat?->id,
                'image' => '/images/games/crash.png',
                'icon' => 'bi-graph-up-arrow',
                'min_entry_fee' => 10.00,
                'max_entry_fee' => 100000.00,
                'win_multiplier' => 1.01,
                'rtp_percentage' => 95.00,
                'is_active' => true,
                'rules' => 'Watch the rocket multiplier climb e.g. 1.5x, 3x, 50x. Cash out before it crashes!',
                'instruction' => 'Set auto-cashout or click Cash Out manually during flight.',
                'config' => ['speed' => 1.1],
            ],
            [
                'name' => 'JetX Flight',
                'slug' => 'jet',
                'code' => 'jet',
                'category_id' => $crashCat?->id,
                'image' => '/images/games/jet.png',
                'icon' => 'bi-rocket',
                'min_entry_fee' => 50.00,
                'max_entry_fee' => 100000.00,
                'win_multiplier' => 1.01,
                'rtp_percentage' => 96.00,
                'is_active' => true,
                'rules' => 'High-altitude Jet rocket game with dynamic multiplier curve.',
                'instruction' => 'Eject passengers before atmospheric explosion.',
                'config' => ['speed' => 1.2],
            ],
            [
                'name' => 'Parity (3-Min)',
                'slug' => 'parity',
                'code' => 'parity',
                'category_id' => $colorCat?->id,
                'image' => '/images/games/parity.png',
                'icon' => 'bi-clock-history',
                'min_entry_fee' => 10.00,
                'max_entry_fee' => 50000.00,
                'win_multiplier' => 2.00,
                'rtp_percentage' => 96.50,
                'is_active' => true,
                'rules' => 'Classic 3-minute period color prediction room with higher entry limits.',
                'instruction' => 'Place your bets on Green, Violet, Red, or Number.',
                'config' => ['timer_seconds' => 180],
            ],
            [
                'name' => 'Spin Wheel',
                'slug' => 'spin-wheel',
                'code' => 'spin_wheel',
                'category_id' => $casinoCat?->id,
                'image' => '/images/games/spin_wheel.png',
                'icon' => 'bi-arrow-repeat',
                'min_entry_fee' => 10.00,
                'max_entry_fee' => 20000.00,
                'win_multiplier' => 10.00,
                'rtp_percentage' => 94.00,
                'is_active' => true,
                'rules' => 'Spin the wheel to hit lucky multipliers e.g. 2x, 5x, 10x, 50x.',
                'instruction' => 'Select stake amount and press Spin.',
                'config' => ['sectors' => [2, 3, 5, 10, 50]],
            ],
            [
                'name' => 'Dice Roll',
                'slug' => 'dice',
                'code' => 'dice',
                'category_id' => $casinoCat?->id,
                'image' => '/images/games/dice.png',
                'icon' => 'bi-dice-5',
                'min_entry_fee' => 10.00,
                'max_entry_fee' => 50000.00,
                'win_multiplier' => 1.98,
                'rtp_percentage' => 98.00,
                'is_active' => true,
                'rules' => 'Roll over or roll under target number.',
                'instruction' => 'Adjust slider target to configure payout odds.',
                'config' => ['min_target' => 5, 'max_target' => 95],
            ],
        ];

        foreach ($games as $g) {
            Game::firstOrCreate(['code' => $g['code']], $g);
        }
    }
}
