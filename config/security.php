<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    |
    | Enable or disable Content Security Policy headers.
    |
    */

    'csp_enable' => env('CSP_ENABLE', true),

    /*
    |--------------------------------------------------------------------------
    | HTTP Strict Transport Security
    |--------------------------------------------------------------------------
    |
    | Enable or disable HSTS headers.
    |
    */

    'hsts_enable' => env('HSTS_ENABLE', true),
    'hsts_max_age' => env('HSTS_MAX_AGE', 31536000), // 1 year
    'hsts_include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
    'hsts_preload' => env('HSTS_PRELOAD', false),

    /*
    |--------------------------------------------------------------------------
    | Tor Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Tor hidden service support.
    |
    */

    'tor_onion_host' => env('TOR_ONION_HOST', ''),

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    |
    | Security settings for file uploads.
    |
    */

    'upload_max_size' => env('UPLOAD_MAX_SIZE', 10485760), // 10MB
    'exiftool_path' => env('EXIFTOOL_PATH', '/usr/bin/exiftool'),
    'allowed_file_types' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'text/plain',
    ],

    /*
    |--------------------------------------------------------------------------
    | PGP Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for PGP/GnuPG integration.
    |
    */

    'gnupg_path' => env('GPG_PATH', '/usr/bin/gpg'),
    'temp_dir' => env('TEMP_DIR', sys_get_temp_dir()),

    /*
    |--------------------------------------------------------------------------
    | Compliance Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for compliance and AML features.
    |
    */

    'compliance_enabled' => env('COMPLIANCE_ENABLED', true),
    'kyc_required_countries' => explode(',', env('KYC_REQUIRED_COUNTRIES', 'US,CA,EU')),
    'aml_enabled' => env('AML_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache TTL settings for various security-related data.
    |
    */

    'cache_ttl_offers' => env('CACHE_TTL_OFFERS', 300), // 5 minutes
    'cache_ttl_prices' => env('CACHE_TTL_PRICES', 60), // 1 minute
    'cache_ttl_api' => env('CACHE_TTL_API', 300), // 5 minutes

    /*
    |--------------------------------------------------------------------------
    | CSRF Protection
    |--------------------------------------------------------------------------
    |
    | Configuration for CSRF protection.
    |
    */

    'csrf_protection' => env('CSRF_PROTECTION', true),
    'csrf_token_lifetime' => env('CSRF_TOKEN_LIFETIME', 120), // 2 hours

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configuration for rate limiting various operations.
    |
    */

    'rate_limiting' => [
        'login_max_attempts' => env('RATE_LIMIT_LOGIN', 5),
        'login_lockout_minutes' => env('RATE_LIMIT_LOGIN_LOCKOUT', 15),
        'register_max_attempts' => env('RATE_LIMIT_REGISTER', 3),
        'register_lockout_minutes' => env('RATE_LIMIT_REGISTER_LOCKOUT', 60),
        'pin_max_attempts' => env('RATE_LIMIT_PIN', 5),
        'pin_lockout_minutes' => env('RATE_LIMIT_PIN_LOCKOUT', 15),
        'api_max_attempts' => env('RATE_LIMIT_API', 100),
        'api_lockout_minutes' => env('RATE_LIMIT_API_LOCKOUT', 1),
        'admin_max_attempts' => env('RATE_LIMIT_ADMIN', 20),
        'admin_lockout_minutes' => env('RATE_LIMIT_ADMIN_LOCKOUT', 5),
        'trade_max_attempts' => env('RATE_LIMIT_TRADE', 10),
        'trade_lockout_minutes' => env('RATE_LIMIT_TRADE_LOCKOUT', 5),
        'offer_max_attempts' => env('RATE_LIMIT_OFFER', 5),
        'offer_lockout_minutes' => env('RATE_LIMIT_OFFER_LOCKOUT', 10),
        'message_max_attempts' => env('RATE_LIMIT_MESSAGE', 30),
        'message_lockout_minutes' => env('RATE_LIMIT_MESSAGE_LOCKOUT', 1),
        'withdrawal_max_attempts' => env('RATE_LIMIT_WITHDRAWAL', 3),
        'withdrawal_lockout_minutes' => env('RATE_LIMIT_WITHDRAWAL_LOCKOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Validation
    |--------------------------------------------------------------------------
    |
    | Configuration for order validation and fraud prevention.
    |
    */

    'order_validation' => [
        'enabled' => env('ORDER_VALIDATION_ENABLED', true),
        'duplicate_check_minutes' => env('ORDER_DUPLICATE_CHECK_MINUTES', 5),
        'max_amount_xmr' => env('ORDER_MAX_AMOUNT_XMR', 100), // 100 XMR
        'min_amount_xmr' => env('ORDER_MIN_AMOUNT_XMR', 0.001), // 0.001 XMR
        'require_pgp_for_high_value' => env('ORDER_REQUIRE_PGP_HIGH_VALUE', true),
        'high_value_threshold_xmr' => env('ORDER_HIGH_VALUE_THRESHOLD_XMR', 1), // 1 XMR
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    |
    | Configuration for session security.
    |
    */

    'session_security' => [
        'regenerate_on_login' => env('SESSION_REGENERATE_ON_LOGIN', true),
        'regenerate_on_role_change' => env('SESSION_REGENERATE_ON_ROLE_CHANGE', true),
        'timeout_minutes' => env('SESSION_TIMEOUT_MINUTES', 120), // 2 hours
        'idle_timeout_minutes' => env('SESSION_IDLE_TIMEOUT_MINUTES', 30), // 30 minutes
        'secure_cookies' => env('SESSION_SECURE_COOKIES', true),
        'http_only_cookies' => env('SESSION_HTTP_ONLY_COOKIES', true),
        'same_site' => env('SESSION_SAME_SITE', 'strict'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Security
    |--------------------------------------------------------------------------
    |
    | Configuration for admin security measures.
    |
    */

    'admin_security' => [
        'require_2fa' => env('ADMIN_REQUIRE_2FA', true),
        'log_all_actions' => env('ADMIN_LOG_ALL_ACTIONS', true),
        'max_requests_per_minute' => env('ADMIN_MAX_REQUESTS_PER_MINUTE', 50),
        'suspicious_activity_threshold' => env('ADMIN_SUSPICIOUS_ACTIVITY_THRESHOLD', 100),
        'require_pin_for_sensitive' => env('ADMIN_REQUIRE_PIN_SENSITIVE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | User Security
    |--------------------------------------------------------------------------
    |
    | Configuration for user security measures.
    |
    */

    'user_security' => [
        'require_pin_for_sensitive' => env('USER_REQUIRE_PIN_SENSITIVE', true),
        'log_sensitive_actions' => env('USER_LOG_SENSITIVE_ACTIONS', true),
        'max_requests_per_minute' => env('USER_MAX_REQUESTS_PER_MINUTE', 100),
        'suspicious_activity_threshold' => env('USER_SUSPICIOUS_ACTIVITY_THRESHOLD', 200),
        'require_pgp_for_high_value' => env('USER_REQUIRE_PGP_HIGH_VALUE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Vendor Security
    |--------------------------------------------------------------------------
    |
    | Configuration for vendor security measures.
    |
    */

    'vendor_security' => [
        'require_verification' => env('VENDOR_REQUIRE_VERIFICATION', true),
        'max_offers_per_hour' => env('VENDOR_MAX_OFFERS_PER_HOUR', 10),
        'max_trades_per_hour' => env('VENDOR_MAX_TRADES_PER_HOUR', 20),
        'require_pgp_for_high_value' => env('VENDOR_REQUIRE_PGP_HIGH_VALUE', true),
        'log_all_actions' => env('VENDOR_LOG_ALL_ACTIONS', true),
    ],
];

