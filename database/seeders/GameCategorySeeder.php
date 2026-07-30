<?php

namespace Database\Seeders;

use App\Models\GameCategory;
use Illuminate\Database\Seeder;

class GameCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Fast Games', 'slug' => 'fast-games', 'icon' => 'bi-lightning-charge-fill', 'sort_order' => 1],
            ['name' => 'Crash & Jet', 'slug' => 'crash-jet', 'icon' => 'bi-rocket-takeoff-fill', 'sort_order' => 2],
            ['name' => 'Color Prediction', 'slug' => 'color-prediction', 'icon' => 'bi-palette-fill', 'sort_order' => 3],
            ['name' => 'Casino & Dice', 'slug' => 'casino-dice', 'icon' => 'bi-dice-6-fill', 'sort_order' => 4],
            ['name' => 'Lottery & Cards', 'slug' => 'lottery-cards', 'icon' => 'bi-suit-spade-fill', 'sort_order' => 5],
        ];

        foreach ($categories as $cat) {
            GameCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
