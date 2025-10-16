<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'XMR (Monero)',
                'requires_reference' => true,
                'cash_possible' => false,
                'metadata_schema_json' => json_encode([
                    'type' => 'object',
                    'properties' => [
                        'wallet_address' => ['type' => 'string', 'required' => true],
                        'payment_id' => ['type' => 'string', 'required' => false],
                    ],
                ]),
            ],
            [
                'name' => 'BTC (Bitcoin)',
                'requires_reference' => true,
                'cash_possible' => false,
                'metadata_schema_json' => json_encode([
                    'type' => 'object',
                    'properties' => [
                        'wallet_address' => ['type' => 'string', 'required' => true],
                    ],
                ]),
            ],
            [
                'name' => 'Bank Transfer',
                'requires_reference' => true,
                'cash_possible' => false,
                'metadata_schema_json' => json_encode([
                    'type' => 'object',
                    'properties' => [
                        'bank_name' => ['type' => 'string', 'required' => true],
                        'account_holder' => ['type' => 'string', 'required' => true],
                        'account_number' => ['type' => 'string', 'required' => true],
                        'routing_number' => ['type' => 'string', 'required' => true],
                    ],
                ]),
            ],
            [
                'name' => 'PayPal',
                'requires_reference' => true,
                'cash_possible' => false,
                'metadata_schema_json' => json_encode([
                    'type' => 'object',
                    'properties' => [
                        'email' => ['type' => 'string', 'format' => 'email', 'required' => true],
                    ],
                ]),
            ],
            [
                'name' => 'CashApp',
                'requires_reference' => true,
                'cash_possible' => false,
                'metadata_schema_json' => json_encode([
                    'type' => 'object',
                    'properties' => [
                        'cashapp_username' => ['type' => 'string', 'required' => true],
                    ],
                ]),
            ],
            [
                'name' => 'Cash (In-Person)',
                'requires_reference' => false,
                'cash_possible' => true,
                'metadata_schema_json' => json_encode([
                    'type' => 'object',
                    'properties' => [
                        'meeting_location' => ['type' => 'string', 'required' => true],
                        'meeting_time' => ['type' => 'string', 'required' => true],
                    ],
                ]),
            ],
            [
                'name' => 'Cash (Postal)',
                'requires_reference' => true,
                'cash_possible' => true,
                'metadata_schema_json' => json_encode([
                    'type' => 'object',
                    'properties' => [
                        'mailing_address' => ['type' => 'string', 'required' => true],
                        'delivery_method' => ['type' => 'string', 'required' => true],
                    ],
                ]),
            ],
            [
                'name' => 'USDT (Tether)',
                'requires_reference' => true,
                'cash_possible' => false,
                'metadata_schema_json' => json_encode([
                    'type' => 'object',
                    'properties' => [
                        'wallet_address' => ['type' => 'string', 'required' => true],
                        'network' => ['type' => 'string', 'required' => true, 'enum' => ['ERC-20', 'TRC-20', 'BEP-20']],
                    ],
                ]),
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }
    }
}
