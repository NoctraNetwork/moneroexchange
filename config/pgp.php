<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GnuPG Path
    |--------------------------------------------------------------------------
    |
    | Path to the GnuPG binary.
    |
    */

    'gnupg_path' => env('GPG_PATH', '/usr/bin/gpg'),

    /*
    |--------------------------------------------------------------------------
    | Temporary Directory
    |--------------------------------------------------------------------------
    |
    | Directory for temporary files during PGP operations.
    |
    */

    'temp_dir' => env('TEMP_DIR', sys_get_temp_dir()),

    /*
    |--------------------------------------------------------------------------
    | PGP Key Validation
    |--------------------------------------------------------------------------
    |
    | Settings for PGP key validation.
    |
    */

    'min_key_size' => 2048,
    'max_key_size' => 4096,
    'allowed_key_types' => ['RSA', 'DSA', 'ELG', 'ECDH', 'ECDSA', 'EDDSA'],

    /*
    |--------------------------------------------------------------------------
    | PGP Verification
    |--------------------------------------------------------------------------
    |
    | Settings for PGP signature verification.
    |
    */

    'verification_timeout' => 30, // seconds
    'max_verification_attempts' => 3,

    /*
    |--------------------------------------------------------------------------
    | PGP Encryption
    |--------------------------------------------------------------------------
    |
    | Settings for PGP encryption operations.
    |
    */

    'encryption_timeout' => 60, // seconds
    'max_encryption_attempts' => 3,
    'preferred_cipher' => 'AES256',
    'preferred_digest' => 'SHA512',
    'preferred_compress' => 'ZLIB',
];

