<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SecureSessionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Configure secure session settings
        $this->configureSecureSession($request);

        // Add session security headers
        $this->addSessionSecurityHeaders($response, $request);

        return $response;
    }

    /**
     * Configure secure session settings
     */
    private function configureSecureSession(Request $request): void
    {
        // Set secure session configuration
        Config::set('session.secure', $request->secure());
        Config::set('session.http_only', true);
        Config::set('session.same_site', 'strict');
        
        // Set cookie lifetime (2 hours for security)
        Config::set('session.lifetime', 120);
        
        // Enable session regeneration on login
        Config::set('session.regenerate_on_login', true);
    }

    /**
     * Add session security headers
     */
    private function addSessionSecurityHeaders(Response $response, Request $request): void
    {
        // Set secure cookie attributes
        $response->headers->set('Set-Cookie', $this->buildSecureCookieHeader($request));
        
        // Add cache control for sensitive pages
        if ($this->isSensitivePage($request)) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }
    }

    /**
     * Build secure cookie header
     */
    private function buildSecureCookieHeader(Request $request): string
    {
        $secure = $request->secure() ? '; Secure' : '';
        $httpOnly = '; HttpOnly';
        $sameSite = '; SameSite=Strict';
        
        return "laravel_session=*{$secure}{$httpOnly}{$sameSite}";
    }

    /**
     * Check if the current page is sensitive
     */
    private function isSensitivePage(Request $request): bool
    {
        $sensitivePaths = [
            '/admin',
            '/dashboard',
            '/settings',
            '/trades',
            '/offers/create',
            '/withdrawals',
        ];

        foreach ($sensitivePaths as $path) {
            if (str_starts_with($request->path(), ltrim($path, '/'))) {
                return true;
            }
        }

        return false;
    }
}
