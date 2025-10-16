<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Monero Daemon Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Monero daemon (monerod) connection.
    |
    */

    'daemon_url' => env('MONEROD_URL', 'http://127.0.0.1:18081'),
    'daemon_user' => env('MONEROD_USER', ''),
    'daemon_pass' => env('MONEROD_PASS', ''),

    /*
    |--------------------------------------------------------------------------
    | Monero Wallet RPC Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Monero wallet RPC connection.
    |
    */

    'wallet_rpc_url' => env('MONERO_WALLET_RPC_URL', 'http://127.0.0.1:18083'),
    'wallet_rpc_user' => env('MONERO_WALLET_RPC_USER', ''),
    'wallet_rpc_pass' => env('MONERO_WALLET_RPC_PASS', ''),
    'wallet_name' => env('MONERO_WALLET_NAME', 'escrow_wallet'),
    'wallet_password' => env('MONERO_WALLET_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Monero Transaction Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Monero transactions and confirmations.
    |
    */

    'confirmations' => env('XMR_CONFIRMATIONS', 10),
    'min_withdrawal_atomic' => env('XMR_MIN_WITHDRAWAL_ATOMIC', 1000000000000), // 0.001 XMR
    'atomic_units' => env('XMR_ATOMIC_UNITS', 1000000000000),

    /*
    |--------------------------------------------------------------------------
    | Fee Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for platform fees.
    |
    */

    'trade_fee_bps' => env('TRADE_FEE_BPS', 25), // 0.25%
    'withdrawal_fee_bps' => env('WITHDRAWAL_FEE_BPS', 25), // 0.25%
    'fee_bps' => env('FEE_BPS', 50), // 0.5%

    /*
    |--------------------------------------------------------------------------
    | Price Index Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for price index services.
    |
    */

    'price_driver' => env('MONERO_PRICE_DRIVER', 'fixed'), // 'fixed' or 'floating'
    'price_cache_ttl' => env('CACHE_TTL_PRICES', 60), // seconds

    /*
    |--------------------------------------------------------------------------
    | Escrow Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for escrow system.
    |
    */

    'escrow_account_index' => 0,
    'escrow_subaddress_index' => 0,
    'escrow_scan_interval' => 30, // seconds
    'escrow_confirmations_required' => env('XMR_CONFIRMATIONS', 10),

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security settings for Monero operations.
    |
    */

    'rpc_timeout' => 30, // seconds
    'rpc_retry_attempts' => 3,
    'rpc_retry_delay' => 1, // seconds

    /*
    |--------------------------------------------------------------------------
    | Development Configuration
    |--------------------------------------------------------------------------
    |
    | Development and testing settings.
    |
    */

    'testnet' => env('MONERO_TESTNET', false),
    'debug' => env('MONERO_DEBUG', false),
];

