<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fee;

class FeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fees = [
            [
                'type' => 'admin_fee',
                'bps' => 50, // 0.5% standard admin fee for all traders
                'flat_atomic' => 0,
            ],
            [
                'type' => 'dispute_fee',
                'bps' => 0,
                'flat_atomic' => 1000000000000, // 0.001 XMR
            ],
        ];

        foreach ($fees as $fee) {
            Fee::create($fee);
        }
    }
}
