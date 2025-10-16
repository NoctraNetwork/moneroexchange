<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel 12 Enhanced Security Features
    |--------------------------------------------------------------------------
    |
    | Configuration for Laravel 12 specific security enhancements.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Enhanced Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Laravel 12 introduces enhanced rate limiting with better performance
    | and more granular control.
    |
    */

    'enhanced_rate_limiting' => [
        'enabled' => env('ENHANCED_RATE_LIMITING', true),
        'cache_driver' => env('RATE_LIMIT_CACHE_DRIVER', 'redis'),
        'default_ttl' => env('RATE_LIMIT_DEFAULT_TTL', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Advanced Caching
    |--------------------------------------------------------------------------
    |
    | Laravel 12 introduces advanced caching features with better performance.
    |
    */

    'advanced_caching' => [
        'enabled' => env('ADVANCED_CACHING', true),
        'compression' => env('CACHE_COMPRESSION', true),
        'serialization' => env('CACHE_SERIALIZATION', 'json'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Enhanced Session Security
    |--------------------------------------------------------------------------
    |
    | Laravel 12 introduces enhanced session security features.
    |
    */

    'enhanced_session_security' => [
        'enabled' => env('ENHANCED_SESSION_SECURITY', true),
        'encrypt_session_data' => env('ENCRYPT_SESSION_DATA', true),
        'session_fingerprinting' => env('SESSION_FINGERPRINTING', true),
        'session_regeneration_interval' => env('SESSION_REGENERATION_INTERVAL', 300), // 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Advanced Input Validation
    |--------------------------------------------------------------------------
    |
    | Laravel 12 introduces advanced input validation features.
    |
    */

    'advanced_validation' => [
        'enabled' => env('ADVANCED_VALIDATION', true),
        'sanitize_inputs' => env('SANITIZE_INPUTS', true),
        'validate_file_uploads' => env('VALIDATE_FILE_UPLOADS', true),
        'max_upload_size' => env('MAX_UPLOAD_SIZE', 10240), // 10MB in KB
    ],

    /*
    |--------------------------------------------------------------------------
    | Enhanced Logging
    |--------------------------------------------------------------------------
    |
    | Laravel 12 introduces enhanced logging with better performance.
    |
    */

    'enhanced_logging' => [
        'enabled' => env('ENHANCED_LOGGING', true),
        'structured_logging' => env('STRUCTURED_LOGGING', true),
        'log_compression' => env('LOG_COMPRESSION', true),
        'log_retention_days' => env('LOG_RETENTION_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Optimizations
    |--------------------------------------------------------------------------
    |
    | Laravel 12 specific performance optimizations.
    |
    */

    'performance' => [
        'opcache_enabled' => env('OPCACHE_ENABLED', true),
        'query_optimization' => env('QUERY_OPTIMIZATION', true),
        'eager_loading' => env('EAGER_LOADING', true),
        'connection_pooling' => env('CONNECTION_POOLING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers (Laravel 12 Enhanced)
    |--------------------------------------------------------------------------
    |
    | Enhanced security headers for Laravel 12.
    |
    */

    'security_headers' => [
        'cross_origin_embedder_policy' => env('COEP', 'require-corp'),
        'cross_origin_opener_policy' => env('COOP', 'same-origin'),
        'cross_origin_resource_policy' => env('CORP', 'same-origin'),
        'permissions_policy' => env('PERMISSIONS_POLICY', 'geolocation=(), microphone=(), camera=()'),
        'strict_transport_security' => env('HSTS', 'max-age=31536000; includeSubDomains; preload'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monero Integration Enhancements
    |--------------------------------------------------------------------------
    |
    | Laravel 12 specific enhancements for Monero integration.
    |
    */

    'monero_enhancements' => [
        'async_processing' => env('MONERO_ASYNC_PROCESSING', true),
        'transaction_batching' => env('MONERO_TRANSACTION_BATCHING', true),
        'enhanced_validation' => env('MONERO_ENHANCED_VALIDATION', true),
        'performance_monitoring' => env('MONERO_PERFORMANCE_MONITORING', true),
    ],
];
