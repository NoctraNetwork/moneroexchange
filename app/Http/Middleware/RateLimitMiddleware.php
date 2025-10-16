<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'general'): Response
    {
        $key = $this->getRateLimitKey($request, $type);
        $maxAttempts = $this->getMaxAttempts($type);
        $decayMinutes = $this->getDecayMinutes($type);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'type' => $type,
                'key' => $key,
                'seconds_remaining' => $seconds,
            ]);

            return response()->json([
                'error' => 'Too many attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.',
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return $next($request);
    }

    /**
     * Get rate limit key for the request
     */
    private function getRateLimitKey(Request $request, string $type): string
    {
        $ip = $request->ip();
        $user = $request->user();
        
        // Use user ID if authenticated, otherwise IP
        $identifier = $user ? "user:{$user->id}" : "ip:{$ip}";
        
        return "rate_limit:{$type}:{$identifier}";
    }

    /**
     * Get max attempts for the rate limit type
     */
    private function getMaxAttempts(string $type): int
    {
        return match($type) {
            'login' => 5,
            'register' => 3,
            'pin' => 5,
            'api' => 100,
            'admin' => 20,
            'trade' => 10,
            'offer' => 5,
            'message' => 30,
            'withdrawal' => 3,
            default => 60
        };
    }

    /**
     * Get decay minutes for the rate limit type
     */
    private function getDecayMinutes(string $type): int
    {
        return match($type) {
            'login' => 15,
            'register' => 60,
            'pin' => 15,
            'api' => 1,
            'admin' => 5,
            'trade' => 5,
            'offer' => 10,
            'message' => 1,
            'withdrawal' => 30,
            default => 1
        };
    }
}
