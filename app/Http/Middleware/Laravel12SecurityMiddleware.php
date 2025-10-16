<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class Laravel12SecurityMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add Laravel 12 enhanced security headers
        $this->addEnhancedSecurityHeaders($response, $request);

        // Add performance headers
        $this->addPerformanceHeaders($response);

        // Add Laravel 12 specific headers
        $this->addLaravel12Headers($response);

        return $response;
    }

    /**
     * Add enhanced security headers for Laravel 12
     */
    private function addEnhancedSecurityHeaders(Response $response, Request $request): void
    {
        if (!config('laravel12.security_headers', true)) {
            return;
        }

        // Cross-Origin Embedder Policy
        $response->headers->set('Cross-Origin-Embedder-Policy', config('laravel12.security_headers.cross_origin_embedder_policy', 'require-corp'));

        // Cross-Origin Opener Policy
        $response->headers->set('Cross-Origin-Opener-Policy', config('laravel12.security_headers.cross_origin_opener_policy', 'same-origin'));

        // Cross-Origin Resource Policy
        $response->headers->set('Cross-Origin-Resource-Policy', config('laravel12.security_headers.cross_origin_resource_policy', 'same-origin'));

        // Enhanced Permissions Policy
        $response->headers->set('Permissions-Policy', config('laravel12.security_headers.permissions_policy', 'geolocation=(), microphone=(), camera=()'));

        // Enhanced HSTS (disabled for Tor/onion sites)
        // Note: HSTS is not applicable for Tor hidden services
    }

    /**
     * Add performance headers
     */
    private function addPerformanceHeaders(Response $response): void
    {
        if (!config('laravel12.performance', true)) {
            return;
        }

        // Cache control for static assets
        if ($this->isStaticAsset($response)) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        }

        // Compression headers
        if (config('laravel12.advanced_caching.compression', true)) {
            $response->headers->set('Vary', 'Accept-Encoding');
        }
    }

    /**
     * Add Laravel 12 specific headers
     */
    private function addLaravel12Headers(Response $response): void
    {
        // Laravel 12 version header
        $response->headers->set('X-Laravel-Version', '12.0');

        // Enhanced security features
        $response->headers->set('X-Content-Security-Policy-Report-Only', 'default-src \'self\'; report-uri /csp-report');
        
        // Performance monitoring
        if (config('laravel12.monero_enhancements.performance_monitoring', true)) {
            $response->headers->set('X-Performance-Monitoring', 'enabled');
        }
    }

    /**
     * Check if response is for static asset
     */
    private function isStaticAsset(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        
        return str_contains($contentType, 'text/css') ||
               str_contains($contentType, 'application/javascript') ||
               str_contains($contentType, 'image/') ||
               str_contains($contentType, 'font/');
    }
}
