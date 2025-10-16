<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class UserProtectionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('login')->with('error', 'Authentication required');
        }

        // Check if user account is active
        if (!$user->isActive()) {
            Log::warning('Inactive user account access attempt', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Your account is inactive. Please contact support.');
        }

        // Check if user is suspended
        if ($user->isSuspended()) {
            Log::warning('Suspended user account access attempt', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Your account has been suspended. Please contact support.');
        }

        // Check for suspicious user activity
        if ($this->isSuspiciousUserActivity($request, $user)) {
            Log::warning('Suspicious user activity detected', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
            ]);

            // For suspicious activity, just log it but don't block (could be false positive)
            // In production, you might want to implement additional verification
        }

        // Check if PIN verification is required for sensitive operations
        if ($this->requiresPinVerification($request) && !$this->isPinVerified($request)) {
            return redirect()->route('pin.verify')->with('error', 'PIN verification required for this action');
        }

        // Log user access for sensitive operations
        if ($this->isSensitiveOperation($request)) {
            $this->logUserAccess($request, $user);
        }

        return $next($request);
    }

    /**
     * Check for suspicious user activity
     */
    private function isSuspiciousUserActivity(Request $request, $user): bool
    {
        // Check for rapid successive requests
        $key = "user_requests:{$user->id}:" . $request->ip();
        $requests = cache()->get($key, 0);
        
        if ($requests > 100) { // More than 100 requests in 1 minute
            return true;
        }

        // Increment request counter
        cache()->put($key, $requests + 1, 60);

        // Check for unusual user agent changes
        $currentUserAgent = $request->userAgent();
        $lastUserAgent = cache()->get("user_agent:{$user->id}");
        
        if ($lastUserAgent && $lastUserAgent !== $currentUserAgent) {
            // User agent changed - could be suspicious
            cache()->put("user_agent:{$user->id}", $currentUserAgent, 3600);
            return true;
        }

        if (!$lastUserAgent) {
            cache()->put("user_agent:{$user->id}", $currentUserAgent, 3600);
        }

        return false;
    }

    /**
     * Check if the request requires PIN verification
     */
    private function requiresPinVerification(Request $request): bool
    {
        $sensitiveRoutes = [
            'trades.store',
            'trades.release',
            'trades.refund',
            'withdrawals.store',
            'settings.password.change',
            'settings.pin.change',
        ];

        $routeName = $request->route()?->getName();
        
        return $routeName && in_array($routeName, $sensitiveRoutes);
    }

    /**
     * Check if PIN is verified in current session
     */
    private function isPinVerified(Request $request): bool
    {
        return $request->session()->get('pin_verified', false);
    }

    /**
     * Check if the request is a sensitive operation
     */
    private function isSensitiveOperation(Request $request): bool
    {
        $sensitiveRoutes = [
            'trades.store',
            'trades.release',
            'trades.refund',
            'withdrawals.store',
            'offers.store',
            'offers.update',
            'offers.destroy',
        ];

        $routeName = $request->route()?->getName();
        
        return $routeName && in_array($routeName, $sensitiveRoutes);
    }

    /**
     * Log user access for sensitive operations
     */
    private function logUserAccess(Request $request, $user): void
    {
        try {
            \App\Models\UserSecurityLog::create([
                'user_id' => $user->id,
                'ip_hash' => hash('sha256', $request->ip()),
                'ua_hash' => hash('sha256', $request->userAgent() ?? ''),
                'is_tor' => $this->isTorRequest($request),
                'action' => $request->route()?->getName(),
                'url' => $request->fullUrl(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log user access', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Check if request is from Tor
     */
    private function isTorRequest(Request $request): bool
    {
        $userAgent = $request->userAgent();
        $ip = $request->ip();
        
        // Check for Tor browser user agent patterns
        $torUserAgents = [
            'Mozilla/5.0 (Windows NT 10.0; rv:102.0) Gecko/20100101 Firefox/102.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:102.0) Gecko/20100101 Firefox/102.0',
        ];
        
        if (in_array($userAgent, $torUserAgents)) {
            return true;
        }
        
        // Check for .onion domain
        if (str_contains($request->getHost(), '.onion')) {
            return true;
        }
        
        return false;
    }
}
