<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Services\PinService;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    private PinService $pinService;

    public function __construct(PinService $pinService)
    {
        $this->pinService = $pinService;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo users
        $users = [
            [
                'username' => 'alice',
                'password' => 'password123',
                'pin' => '1234',
                'country' => 'US',
                'is_tor_only' => false,
            ],
            [
                'username' => 'bob',
                'password' => 'password123',
                'pin' => '5678',
                'country' => 'CA',
                'is_tor_only' => false,
            ],
            [
                'username' => 'charlie',
                'password' => 'password123',
                'pin' => '9999',
                'country' => 'GB',
                'is_tor_only' => true,
            ],
        ];

        foreach ($users as $userData) {
            $pin = $userData['pin'];
            unset($userData['pin']);

            $user = User::create([
                ...$userData,
                'password' => $userData['password'],
                'pin_hash' => $this->pinService->hashPin($pin),
            ]);

            $this->command->info("Created user: {$user->username} (PIN: {$pin})");
        }
    }
}

