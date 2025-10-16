<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CsrfProtectionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip CSRF for read-only requests and API routes
        if ($this->shouldSkipCsrf($request)) {
            return $next($request);
        }

        // Verify CSRF token for state-changing requests
        if ($this->requiresCsrfToken($request)) {
            if (!$this->tokensMatch($request)) {
                Log::warning('CSRF token mismatch', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ]);

                return response()->json([
                    'error' => 'CSRF token mismatch. Please refresh the page and try again.'
                ], 419);
            }
        }

        return $next($request);
    }

    /**
     * Determine if the request should skip CSRF verification
     */
    private function shouldSkipCsrf(Request $request): bool
    {
        // Skip for read-only methods
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return true;
        }

        // Skip for API routes (if they exist)
        if ($request->is('api/*')) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the request requires CSRF token
     */
    private function requiresCsrfToken(Request $request): bool
    {
        // All state-changing requests require CSRF token
        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']);
    }

    /**
     * Verify that the CSRF tokens match
     */
    private function tokensMatch(Request $request): bool
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
        
        if (!$token) {
            return false;
        }

        return hash_equals(
            (string) $request->session()->token(),
            (string) $token
        );
    }
}
