<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tor Hidden Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Tor hidden service deployment.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Onion Host Configuration
    |--------------------------------------------------------------------------
    |
    | The onion host for your hidden service.
    |
    */

    'onion_host' => env('TOR_ONION_HOST', ''),
    'onion_port' => env('TOR_ONION_PORT', 80),

    /*
    |--------------------------------------------------------------------------
    | Tor-Specific Security Settings
    |--------------------------------------------------------------------------
    |
    | Security settings optimized for Tor hidden services.
    |
    */

    'security' => [
        'disable_ssl' => true,
        'disable_hsts' => true,
        'disable_secure_cookies' => true,
        'allow_http_only' => true,
        'tor_only_mode' => env('TOR_ONLY_MODE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tor Browser Detection
    |--------------------------------------------------------------------------
    |
    | Settings for detecting and handling Tor browser requests.
    |
    */

    'browser_detection' => [
        'enabled' => env('TOR_BROWSER_DETECTION', true),
        'user_agents' => [
            'Mozilla/5.0 (Windows NT 10.0; rv:102.0) Gecko/20100101 Firefox/102.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:102.0) Gecko/20100101 Firefox/102.0',
            'Mozilla/5.0 (X11; Linux x86_64; rv:102.0) Gecko/20100101 Firefox/102.0',
        ],
        'onion_domains' => [
            '.onion',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Optimizations for Tor
    |--------------------------------------------------------------------------
    |
    | Performance settings optimized for Tor network characteristics.
    |
    */

    'performance' => [
        'reduce_timeouts' => true,
        'optimize_for_latency' => true,
        'minimize_requests' => true,
        'cache_aggressively' => true,
        'compress_responses' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Monero Integration for Tor
    |--------------------------------------------------------------------------
    |
    | Monero-specific settings for Tor deployment.
    |
    */

    'monero' => [
        'rpc_timeout' => 60, // Increased for Tor latency
        'retry_attempts' => 5, // More retries for Tor
        'retry_delay' => 2, // Longer delay between retries
        'use_tor_proxy' => env('MONERO_USE_TOR_PROXY', false),
        'tor_proxy_host' => env('MONERO_TOR_PROXY_HOST', '127.0.0.1'),
        'tor_proxy_port' => env('MONERO_TOR_PROXY_PORT', 9050),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging for Tor
    |--------------------------------------------------------------------------
    |
    | Logging settings optimized for Tor hidden services.
    |
    */

    'logging' => [
        'log_tor_requests' => env('LOG_TOR_REQUESTS', true),
        'log_ip_hashes' => env('LOG_IP_HASHES', true),
        'anonymize_logs' => env('ANONYMIZE_LOGS', true),
        'log_retention_days' => env('TOR_LOG_RETENTION_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting for Tor
    |--------------------------------------------------------------------------
    |
    | Rate limiting settings adjusted for Tor network characteristics.
    |
    */

    'rate_limiting' => [
        'tor_multiplier' => 2.0, // Allow 2x more requests for Tor users
        'onion_exemptions' => true,
        'ip_hash_rate_limiting' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy for Tor
    |--------------------------------------------------------------------------
    |
    | CSP settings optimized for Tor hidden services.
    |
    */

    'csp' => [
        'allow_onion_connections' => true,
        'strict_origin_policy' => true,
        'disable_unsafe_eval' => true,
        'disable_unsafe_inline' => false, // Allow for Tailwind CSS
    ],
];
