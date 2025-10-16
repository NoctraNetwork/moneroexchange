<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $this->addSecurityHeaders($response);

        return $response;
    }

    /**
     * Add security headers
     */
    private function addSecurityHeaders(Response $response): void
    {
        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // X-Content-Type-Options
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-Frame-Options
        $response->headers->set('X-Frame-Options', 'DENY');

        // X-XSS-Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Permissions Policy
        $this->addPermissionsPolicy($response);

        // HSTS (if enabled and HTTPS)
        if (config('security.hsts_enable', true) && $request->secure()) {
            $this->addHstsHeader($response);
        }

        // Remove server information
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');
    }

    /**
     * Add Permissions Policy header
     */
    private function addPermissionsPolicy(Response $response): void
    {
        $permissions = [
            'accelerometer' => '()',
            'ambient-light-sensor' => '()',
            'autoplay' => '()',
            'battery' => '()',
            'camera' => '()',
            'cross-origin-isolated' => '()',
            'display-capture' => '()',
            'document-domain' => '()',
            'encrypted-media' => '()',
            'execution-while-not-rendered' => '()',
            'execution-while-out-of-viewport' => '()',
            'fullscreen' => '()',
            'geolocation' => '()',
            'gyroscope' => '()',
            'keyboard-map' => '()',
            'magnetometer' => '()',
            'microphone' => '()',
            'midi' => '()',
            'navigation-override' => '()',
            'payment' => '()',
            'picture-in-picture' => '()',
            'publickey-credentials-get' => '()',
            'screen-wake-lock' => '()',
            'sync-xhr' => '()',
            'usb' => '()',
            'web-share' => '()',
            'xr-spatial-tracking' => '()',
        ];

        $policy = [];
        foreach ($permissions as $feature => $allowlist) {
            $policy[] = "{$feature}={$allowlist}";
        }

        $response->headers->set('Permissions-Policy', implode(', ', $policy));
    }

    /**
     * Add HSTS header
     */
    private function addHstsHeader(Response $response): void
    {
        $maxAge = config('security.hsts_max_age', 31536000); // 1 year
        $includeSubDomains = config('security.hsts_include_subdomains', true);
        $preload = config('security.hsts_preload', false);

        $hsts = "max-age={$maxAge}";
        
        if ($includeSubDomains) {
            $hsts .= '; includeSubDomains';
        }
        
        if ($preload) {
            $hsts .= '; preload';
        }

        $response->headers->set('Strict-Transport-Security', $hsts);
    }
}

