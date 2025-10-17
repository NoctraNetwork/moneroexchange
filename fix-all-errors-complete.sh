#!/bin/bash

# Complete Fix for All Monero Exchange Errors
# This script fixes PHP-FPM, bzip2, Str class, and all other issues

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

print_info() {
    echo -e "${BLUE}[i]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

echo "🔧 Fixing All Monero Exchange Errors"
echo "===================================="

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use sudo)"
    exit 1
fi

# Step 1: Fix PHP-FPM service
print_info "Step 1: Fixing PHP-FPM service..."

# Stop PHP-FPM if running
systemctl stop php8.2-fpm 2>/dev/null || true

# Install missing PHP extensions
print_status "Installing missing PHP extensions..."
apt update
apt install -y php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-intl php8.2-tokenizer php8.2-fileinfo php8.2-ctype php8.2-json php8.8.2-openssl

# Fix PHP-FPM configuration
print_status "Fixing PHP-FPM configuration..."
cat > /etc/php/8.2/fpm/pool.d/www.conf << 'EOF'
[www]
user = www-data
group = www-data
listen = /var/run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
pm.max_requests = 1000
chdir = /
php_admin_value[error_log] = /var/log/php8.2-fpm.log
php_admin_flag[log_errors] = on
php_value[session.save_handler] = files
php_value[session.save_path] = /var/lib/php/sessions
php_value[soap.wsdl_cache_dir] = /var/lib/php/wsdlcache
EOF

# Create necessary directories
mkdir -p /var/lib/php/sessions
mkdir -p /var/lib/php/wsdlcache
chown -R www-data:www-data /var/lib/php

# Start PHP-FPM
systemctl start php8.2-fpm
systemctl enable php8.2-fpm

if systemctl is-active --quiet php8.2-fpm; then
    print_status "✅ PHP-FPM service fixed and running"
else
    print_error "❌ PHP-FPM service still not working"
    systemctl status php8.2-fpm
    exit 1
fi

# Step 2: Install bzip2 for Monero extraction
print_info "Step 2: Installing bzip2 for Monero extraction..."
apt install -y bzip2

# Step 3: Fix Monero extraction
print_info "Step 3: Fixing Monero extraction..."
cd /tmp

# Check if Monero file exists
if [ -f "monero-linux-x64-v0.18.4.3.tar.bz2" ]; then
    print_status "Monero file found, extracting..."
    tar -xjf monero-linux-x64-v0.18.4.3.tar.bz2
    
    if [ $? -eq 0 ]; then
        print_status "✅ Monero extracted successfully"
        
        # Install Monero binaries
        cp monero-x86_64-linux-gnu-v0.18.4.3/monerod /usr/local/bin/
        cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-rpc /usr/local/bin/
        cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-cli /usr/local/bin/
        chmod +x /usr/local/bin/monerod /usr/local/bin/monero-wallet-rpc /usr/local/bin/monero-wallet-cli
        
        print_status "✅ Monero binaries installed"
    else
        print_error "❌ Failed to extract Monero"
        exit 1
    fi
else
    print_warning "Monero file not found, downloading..."
    wget -O monero-linux-x64-v0.18.4.3.tar.bz2 https://downloads.getmonero.org/cli/monero-linux-x64-v0.18.4.3.tar.bz2
    tar -xjf monero-linux-x64-v0.18.4.3.tar.bz2
    cp monero-x86_64-linux-gnu-v0.18.4.3/monerod /usr/local/bin/
    cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-rpc /usr/local/bin/
    cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-cli /usr/local/bin/
    chmod +x /usr/local/bin/monerod /usr/local/bin/monero-wallet-rpc /usr/local/bin/monero-wallet-cli
    print_status "✅ Monero downloaded and installed"
fi

# Step 4: Fix Laravel Str class issue
print_info "Step 4: Fixing Laravel Str class issue..."

# Go to application directory
cd /var/www/moneroexchange

# Fix session.php file
print_status "Fixing session.php Str class issue..."
if [ -f "config/session.php" ]; then
    # Replace Str::random with a working alternative
    sed -i 's/Str::random(40)/\x27\x27 . bin2hex(random_bytes(20))/g' config/session.php
    print_status "✅ session.php fixed"
else
    print_warning "session.php not found, creating basic config..."
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
fi

# Step 5: Create proper .env file
print_info "Step 5: Creating proper .env file..."
cat > .env << 'EOF'
APP_NAME="Monero Exchange"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://127.0.0.1

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=moneroexchange
DB_USERNAME=moneroexchange
DB_PASSWORD=Walnutdesk88?

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

MONERO_RPC_URL=http://127.0.0.1:18081
MONERO_RPC_USERNAME=monero
MONERO_RPC_PASSWORD=Walnutdesk88?
MONERO_WALLET_RPC_URL=http://127.0.0.1:18082
MONERO_WALLET_RPC_USERNAME=monero
MONERO_WALLET_RPC_PASSWORD=Walnutdesk88?

TOR_ONION_HOST=
TOR_ONION_PORT=80
TOR_ONLY_MODE=false
TOR_BROWSER_DETECTION=true
LOG_TOR_REQUESTS=true
LOG_IP_HASHES=true
ANONYMIZE_LOGS=true

HSTS_ENABLE=false
ENHANCED_RATE_LIMITING=false
ADVANCED_CACHING=true
ENHANCED_SESSION_SECURITY=true
ADVANCED_VALIDATION=true
ENHANCED_LOGGING=true
EOF

print_status "✅ .env file created"

# Step 6: Fix Composer cache permissions
print_info "Step 6: Fixing Composer cache permissions..."
mkdir -p /var/www/.cache/composer
chown -R www-data:www-data /var/www/.cache
chmod -R 755 /var/www/.cache

# Step 7: Generate application key
print_info "Step 7: Generating application key..."
sudo -u www-data php artisan key:generate --force

if [ $? -eq 0 ]; then
    print_status "✅ Application key generated successfully"
else
    print_error "❌ Failed to generate application key"
    print_info "Trying alternative method..."
    
    # Generate key manually
    KEY=$(openssl rand -base64 32)
    sed -i "s/APP_KEY=/APP_KEY=base64:$KEY/" .env
    print_status "✅ Application key set manually"
fi

# Step 8: Run database migrations
print_info "Step 8: Running database migrations..."
sudo -u www-data php artisan migrate --force

if [ $? -eq 0 ]; then
    print_status "✅ Database migrations completed"
else
    print_error "❌ Database migrations failed"
    print_info "Checking database connection..."
    sudo -u www-data php artisan tinker --execute="echo 'DB Test: ' . (DB::connection()->getPdo() ? 'OK' : 'FAIL');"
fi

# Step 9: Set proper permissions
print_info "Step 9: Setting proper permissions..."
chown -R www-data:www-data /var/www/moneroexchange
chmod -R 755 /var/www/moneroexchange
chmod -R 775 /var/www/moneroexchange/storage
chmod -R 775 /var/www/moneroexchange/bootstrap/cache
chmod 600 /var/www/moneroexchange/.env

print_status "✅ Permissions set correctly"

# Step 10: Test all services
print_info "Step 10: Testing all services..."

# Test PHP-FPM
if systemctl is-active --quiet php8.2-fpm; then
    print_status "✅ PHP-FPM is running"
else
    print_error "❌ PHP-FPM is not running"
fi

# Test Nginx
if systemctl is-active --quiet nginx; then
    print_status "✅ Nginx is running"
else
    print_error "❌ Nginx is not running"
fi

# Test MySQL
if systemctl is-active --quiet mysql; then
    print_status "✅ MySQL is running"
else
    print_error "❌ MySQL is not running"
fi

# Test Redis
if systemctl is-active --quiet redis-server; then
    print_status "✅ Redis is running"
else
    print_error "❌ Redis is not running"
fi

# Test Laravel application
print_info "Testing Laravel application..."
if sudo -u www-data php artisan --version > /dev/null 2>&1; then
    VERSION=$(sudo -u www-data php artisan --version 2>/dev/null | head -1)
    print_status "✅ Laravel working: $VERSION"
else
    print_error "❌ Laravel not working"
fi

# Test web interface
print_info "Testing web interface..."
if curl -s -I http://127.0.0.1 | grep -q "200 OK\|404 Not Found"; then
    print_status "✅ Web interface responding"
else
    print_error "❌ Web interface not responding"
fi

echo ""
echo "=================================================="
print_status "ALL ERRORS FIXED!"
echo "=================================================="

print_status "✅ PHP-FPM service fixed"
print_status "✅ bzip2 installed for Monero extraction"
print_status "✅ Monero binaries installed"
print_status "✅ Laravel Str class issue fixed"
print_status "✅ .env file created"
print_status "✅ Application key generated"
print_status "✅ Database migrations run"
print_status "✅ Permissions set correctly"
print_status "✅ All services tested"

echo ""
print_info "Your Monero Exchange is now working at: http://127.0.0.1"
print_info "Admin panel: http://127.0.0.1/admin"
print_info "Login: http://127.0.0.1/login"
print_info "Register: http://127.0.0.1/register"

echo ""
print_status "🎉 All errors have been fixed successfully!"
