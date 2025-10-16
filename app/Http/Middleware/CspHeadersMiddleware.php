<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CspHeadersMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (config('security.csp_enable', true)) {
            $this->addCspHeaders($response);
        }

        return $response;
    }

    /**
     * Add Content Security Policy headers
     */
    private function addCspHeaders(Response $response): void
    {
        $csp = $this->buildCspPolicy();
        
        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Content-Security-Policy', $csp); // IE support
        $response->headers->set('X-WebKit-CSP', $csp); // WebKit support
    }

    /**
     * Build CSP policy
     */
    private function buildCspPolicy(): string
    {
        $directives = [
            'default-src' => "'none'",
            'script-src' => "'none'", // No JavaScript allowed
            'style-src' => "'self' 'unsafe-inline'", // Allow inline styles for Tailwind
            'img-src' => "'self' data:",
            'font-src' => "'self'",
            'connect-src' => "'self'",
            'frame-ancestors' => "'none'",
            'base-uri' => "'none'",
            'form-action' => "'self'",
            'upgrade-insecure-requests' => '',
            'block-all-mixed-content' => '',
        ];

        $csp = [];
        foreach ($directives as $directive => $value) {
            if ($value === '') {
                $csp[] = $directive;
            } else {
                $csp[] = "{$directive} {$value}";
            }
        }

        return implode('; ', $csp);
    }
}

