<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Offer;
use App\Models\User;
use App\Models\PaymentMethod;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $paymentMethods = PaymentMethod::all();

        if ($users->isEmpty() || $paymentMethods->isEmpty()) {
            $this->command->warn('No users or payment methods found. Skipping offer seeding.');
            return;
        }

        $offers = [
            [
                'user_id' => $users->first()->id,
                'side' => 'sell',
                'price_mode' => 'fixed',
                'fixed_price' => 150.00,
                'currency' => 'USD',
                'min_xmr_atomic' => 1000000000000, // 0.001 XMR
                'max_xmr_atomic' => 10000000000000, // 0.01 XMR
                'payment_method_id' => $paymentMethods->where('name', 'Bank Transfer')->first()->id,
                'country' => 'US',
                'online_or_inperson' => 'online',
                'terms_md' => 'Bank transfer only. Must be from a US bank account.',
            ],
            [
                'user_id' => $users->skip(1)->first()->id,
                'side' => 'buy',
                'price_mode' => 'floating',
                'margin_bps' => 50, // 0.5% above market
                'currency' => 'EUR',
                'min_xmr_atomic' => 5000000000000, // 0.005 XMR
                'max_xmr_atomic' => 50000000000000, // 0.05 XMR
                'payment_method_id' => $paymentMethods->where('name', 'PayPal')->first()->id,
                'country' => 'CA',
                'online_or_inperson' => 'online',
                'terms_md' => 'PayPal friends and family only. No business payments.',
            ],
            [
                'user_id' => $users->last()->id,
                'side' => 'sell',
                'price_mode' => 'fixed',
                'fixed_price' => 120.00,
                'currency' => 'GBP',
                'min_xmr_atomic' => 2000000000000, // 0.002 XMR
                'max_xmr_atomic' => 20000000000000, // 0.02 XMR
                'payment_method_id' => $paymentMethods->where('name', 'Cash in Person')->first()->id,
                'country' => 'GB',
                'online_or_inperson' => 'inperson',
                'terms_md' => 'Cash only. Meet in central London. Bring ID.',
            ],
        ];

        foreach ($offers as $offer) {
            Offer::create($offer);
        }

        $this->command->info('Created ' . count($offers) . ' demo offers');
    }
}

