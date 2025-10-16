<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;

class PinService
{
    /**
     * Hash PIN with Argon2id
     */
    public function hashPin(string $pin): string
    {
        return Hash::make($pin, [
            'memory' => 65536, // 64 MB
            'time' => 4,       // 4 iterations
            'threads' => 3,    // 3 threads
        ]);
    }

    /**
     * Verify PIN
     */
    public function verifyPin(User $user, string $pin): bool
    {
        return Hash::check($pin, $user->pin_hash);
    }

    /**
     * Check if PIN is locked
     */
    public function isPinLocked(User $user): bool
    {
        return $user->pin_locked_until && $user->pin_locked_until->isFuture();
    }

    /**
     * Get remaining lockout time in seconds
     */
    public function getLockoutTimeRemaining(User $user): int
    {
        if (!$this->isPinLocked($user)) {
            return 0;
        }

        return max(0, $user->pin_locked_until->diffInSeconds(now()));
    }

    /**
     * Increment PIN attempts and lock if necessary
     */
    public function incrementAttempts(User $user): void
    {
        $user->pin_attempts++;
        
        $maxAttempts = config('auth.pin_max_attempts', 5);
        $lockoutMinutes = config('auth.pin_lockout_minutes', 15);
        
        if ($user->pin_attempts >= $maxAttempts) {
            $user->pin_locked_until = now()->addMinutes($lockoutMinutes);
        }
        
        $user->save();
    }

    /**
     * Reset PIN attempts
     */
    public function resetAttempts(User $user): void
    {
        $user->pin_attempts = 0;
        $user->pin_locked_until = null;
        $user->save();
    }

    /**
     * Check rate limit for PIN attempts
     */
    public function checkRateLimit(User $user): bool
    {
        $key = "pin_attempts:{$user->id}";
        $maxAttempts = config('auth.pin_max_attempts', 5);
        $decayMinutes = config('auth.pin_lockout_minutes', 15);
        
        return RateLimiter::attempt($key, $maxAttempts, function () {}, $decayMinutes * 60);
    }

    /**
     * Check rate limit for PIN attempts by IP
     */
    public function checkIpRateLimit(string $ip): bool
    {
        $key = "pin_attempts_ip:" . hash('sha256', $ip);
        $maxAttempts = config('auth.pin_max_attempts_ip', 20);
        $decayMinutes = config('auth.pin_lockout_minutes', 15);
        
        return RateLimiter::attempt($key, $maxAttempts, function () {}, $decayMinutes * 60);
    }

    /**
     * Validate PIN format
     */
    public function validatePinFormat(string $pin): bool
    {
        $minLength = config('auth.pin_min_length', 4);
        $maxLength = config('auth.pin_max_length', 8);
        
        return preg_match("/^\d{{$minLength},{$maxLength}}$/", $pin);
    }

    /**
     * Generate random PIN for testing
     */
    public function generateRandomPin(int $length = 6): string
    {
        $pin = '';
        for ($i = 0; $i < $length; $i++) {
            $pin .= random_int(0, 9);
        }
        return $pin;
    }

    /**
     * Check if PIN is too common
     */
    public function isCommonPin(string $pin): bool
    {
        $commonPins = [
            '0000', '1111', '2222', '3333', '4444', '5555', '6666', '7777', '8888', '9999',
            '1234', '2345', '3456', '4567', '5678', '6789', '7890',
            '0123', '1230', '2301', '3012',
            '11111', '22222', '33333', '44444', '55555', '66666', '77777', '88888', '99999',
            '12345', '23456', '34567', '45678', '56789', '67890',
            '111111', '222222', '333333', '444444', '555555', '666666', '777777', '888888', '999999',
            '123456', '234567', '345678', '456789', '567890',
        ];
        
        return in_array($pin, $commonPins);
    }

    /**
     * Get PIN policy requirements
     */
    public function getPinPolicy(): array
    {
        return [
            'min_length' => config('auth.pin_min_length', 4),
            'max_length' => config('auth.pin_max_length', 8),
            'max_attempts' => config('auth.pin_max_attempts', 5),
            'lockout_minutes' => config('auth.pin_lockout_minutes', 15),
            'requires_digits_only' => true,
            'prevents_common_pins' => true,
        ];
    }

    /**
     * Check if user can attempt PIN
     */
    public function canAttemptPin(User $user, string $ip): array
    {
        $result = [
            'can_attempt' => true,
            'reason' => null,
            'lockout_remaining' => 0,
        ];

        // Check if PIN is locked
        if ($this->isPinLocked($user)) {
            $result['can_attempt'] = false;
            $result['reason'] = 'pin_locked';
            $result['lockout_remaining'] = $this->getLockoutTimeRemaining($user);
            return $result;
        }

        // Check rate limit for user
        if (!$this->checkRateLimit($user)) {
            $result['can_attempt'] = false;
            $result['reason'] = 'user_rate_limited';
            $result['lockout_remaining'] = $this->getLockoutTimeRemaining($user);
            return $result;
        }

        // Check rate limit for IP
        if (!$this->checkIpRateLimit($ip)) {
            $result['can_attempt'] = false;
            $result['reason'] = 'ip_rate_limited';
            $result['lockout_remaining'] = 60; // 1 minute for IP rate limit
            return $result;
        }

        return $result;
    }
}

