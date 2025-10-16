<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'username' => 'testuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'pin' => '1234',
            'pin_confirmation' => '1234',
            'country' => 'US',
            'terms' => true,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['username' => 'testuser']);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/pin/verify');
        $this->assertAuthenticated();
    }

    public function test_pin_verification_works(): void
    {
        $user = User::factory()->create([
            'pin' => '1234',
        ]);

        $this->actingAs($user);

        $response = $this->post('/pin/verify', [
            'pin' => '1234',
        ]);

        $response->assertRedirect('/dashboard');
    }
}

