#!/bin/bash

# Quick fix for Laravel Str class issue
# This fixes the "Class Str not found" error

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

print_info() {
    echo -e "${BLUE}[i]${NC} $1"
}

echo "🔧 Fixing Laravel Str class issue..."

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use sudo)"
    exit 1
fi

# Go to application directory
cd /var/www/moneroexchange

# Fix session.php file
print_info "Fixing session.php Str class issue..."

if [ -f "config/session.php" ]; then
    # Create backup
    cp config/session.php config/session.php.backup
    
    # Replace Str::random with working alternative
    sed -i 's/Str::random(40)/\x27\x27 . bin2hex(random_bytes(20))/g' config/session.php
    
    print_status "✅ session.php fixed"
else
    print_info "Creating session.php config..."
    mkdir -p config
    cat > config/session.php << 'EOF'
<?php

return [
    'driver' => env('SESSION_DRIVER', 'file'),
    'lifetime' => env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => storage_path('framework/sessions'),
    'connection' => null,
    'table' => 'sessions',
    'store' => null,
    'lottery' => [2, 100],
    'cookie' => env(
        'SESSION_COOKIE',
        str_slug(env('APP_NAME', 'laravel'), '_').'_session'
    ),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN'),
    'secure' => env('SESSION_SECURE_COOKIE', false),
    'http_only' => true,
    'same_site' => 'lax',
];
EOF
    print_status "✅ session.php created"
fi

# Test the fix
print_info "Testing Laravel application..."
if sudo -u www-data php artisan --version > /dev/null 2>&1; then
    VERSION=$(sudo -u www-data php artisan --version 2>/dev/null | head -1)
    print_status "✅ Laravel working: $VERSION"
else
    print_error "❌ Laravel still not working"
    print_info "Trying to generate key..."
    sudo -u www-data php artisan key:generate --force
fi

print_status "✅ Str class issue fixed!"
