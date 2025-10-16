<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminProtectionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            Log::warning('Unauthenticated admin access attempt', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('login')->with('error', 'Authentication required');
        }

        // Check if user is admin
        if (!$user->is_admin) {
            Log::warning('Non-admin user attempted admin access', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            abort(403, 'Admin privileges required');
        }

        // Check if admin account is active
        if (!$user->isActive()) {
            Log::warning('Inactive admin account access attempt', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip' => $request->ip(),
            ]);

            return redirect()->route('login')->with('error', 'Account is inactive');
        }

        // Check for suspicious admin activity
        if ($this->isSuspiciousActivity($request, $user)) {
            Log::critical('Suspicious admin activity detected', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
            ]);

            // Log out the user and require re-authentication
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Suspicious activity detected. Please log in again.');
        }

        // Log admin access
        $this->logAdminAccess($request, $user);

        return $next($request);
    }

    /**
     * Check for suspicious admin activity
     */
    private function isSuspiciousActivity(Request $request, $user): bool
    {
        // Check for rapid successive requests (potential automated attack)
        $key = "admin_requests:{$user->id}:" . $request->ip();
        $requests = cache()->get($key, 0);
        
        if ($requests > 50) { // More than 50 requests in 1 minute
            return true;
        }

        // Increment request counter
        cache()->put($key, $requests + 1, 60);

        // Check for unusual user agent
        $userAgent = $request->userAgent();
        if (empty($userAgent) || strlen($userAgent) < 10) {
            return true;
        }

        // Check for known bot patterns
        $botPatterns = [
            'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 'python', 'php'
        ];

        foreach ($botPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log admin access
     */
    private function logAdminAccess(Request $request, $user): void
    {
        try {
            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action' => 'admin_access',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'data' => $request->except(['password', 'pin', '_token']),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log admin access', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }
    }
}
