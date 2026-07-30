<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            GameCategorySeeder::class,
            GameSeeder::class,
            PaymentMethodSeeder::class,
            PromotionSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
