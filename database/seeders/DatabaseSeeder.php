<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PaymentMethodSeeder::class,
            FeeSeeder::class,
            UserSeeder::class,
            OfferSeeder::class,
            TradeSeeder::class,
        ]);
    }
}

