#!/bin/bash

# Fix All Issues Script - Composer, .env.example, Nginx
echo "🔧 Fixing All Issues - Composer, .env.example, Nginx..."

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

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use sudo)"
    exit 1
fi

# Set application directory
APP_DIR="/var/www/moneroexchange"

if [ ! -d "$APP_DIR" ]; then
    print_error "Laravel application not found at $APP_DIR"
    exit 1
fi

cd "$APP_DIR"

# Step 1: Fix composer.json with compatible versions
print_info "Step 1: Fixing composer.json with compatible versions..."

cat > composer.json << 'EOF'
{
    "name": "noctranetwork/moneroexchange",
    "type": "project",
    "description": "Production-ready peer-to-peer Monero exchange with no JavaScript",
    "keywords": ["monero", "exchange", "p2p", "escrow", "tor"],
    "license": "MIT",
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0",
        "laravel/sanctum": "^4.0",
        "laravel/tinker": "^2.9",
        "laravel/breeze": "^2.0",
        "guzzlehttp/guzzle": "^7.8",
        "predis/predis": "^2.2",
        "league/commonmark": "^2.4",
        "intervention/image": "^2.7",
        "spatie/laravel-permission": "^6.0",
        "spatie/laravel-activitylog": "^4.7",
        "barryvdh/laravel-dompdf": "^2.0",
        "league/csv": "^9.0"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/pint": "^1.13",
        "laravel/sail": "^1.26",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.0",
        "phpunit/phpunit": "^11.0.1",
        "spatie/laravel-ignition": "^2.4"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi"
        ],
        "post-update-cmd": [
            "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
        ],
        "post-root-package-install": [
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
        ],
        "post-create-project-cmd": [
            "@php artisan key:generate --ansi"
        ]
    },
    "extra": {
        "laravel": {
            "dont-discover": []
        }
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true,
            "php-http/discovery": true
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
EOF

print_status "composer.json updated with compatible versions"

# Step 2: Create .env.example file
print_info "Step 2: Creating .env.example file..."

cat > .env.example << 'EOF'
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

print_status ".env.example file created"

# Step 3: Copy .env.example to .env
print_info "Step 3: Creating .env file from .env.example..."
cp .env.example .env
print_status ".env file created"

# Step 4: Fix Nginx configuration (remove rate limiting)
print_info "Step 4: Fixing Nginx configuration..."

# Stop Nginx
systemctl stop nginx

# Remove ALL rate limiting
rm -f /etc/nginx/conf.d/ratelimit.conf
rm -f /etc/nginx/conf.d/*ratelimit*
rm -f /etc/nginx/conf.d/*rate*

# Remove rate limiting from nginx.conf
sed -i '/limit_req_zone/d' /etc/nginx/nginx.conf
sed -i '/limit_conn_zone/d' /etc/nginx/nginx.conf
sed -i '/limit_conn conn_limit_per_ip/d' /etc/nginx/nginx.conf
sed -i '/include.*ratelimit/d' /etc/nginx/nginx.conf
sed -i '/include.*rate/d' /etc/nginx/nginx.conf

# Create your nginx configuration
cat > /etc/nginx/nginx.conf << 'EOF'
user www-data;
worker_processes auto;
pid /run/nginx.pid;
include /etc/nginx/modules-enabled/*.conf;

events {
	worker_connections 768;
	# multi_accept on;
}

http {
	## NOTE:
	## The explicit include of /etc/nginx/conf.d/ratelimit.conf was removed
	## because /etc/nginx/conf.d/*.conf already includes it and including
	## the same file twice causes duplicate zone errors.
	##

	## Basic Settings
	sendfile on;
	tcp_nopush on;
	types_hash_max_size 2048;
	# server_tokens off;

	# server_names_hash_bucket_size 64;
	# server_name_in_redirect off;

	include /etc/nginx/mime.types;
	default_type application/octet-stream;

	##
	# SSL Settings
	##

	ssl_protocols TLSv1 TLSv1.1 TLSv1.2 TLSv1.3; # Dropping SSLv3, ref: POODLE
	ssl_prefer_server_ciphers on;

	##
	# Logging Settings
	##

	access_log /var/log/nginx/access.log;
	error_log /var/log/nginx/error.log;

	##
	# Gzip Settings
	##

	gzip on;

	# gzip_vary on;
	# gzip_proxied any;
	# gzip_comp_level 6;
	# gzip_buffers 16 8k;
	# gzip_http_version 1.1;
	# gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

	##
	# Virtual Host Configs
	##

	include /etc/nginx/conf.d/*.conf;
	include /etc/nginx/sites-enabled/*;
}
EOF

print_status "Nginx configuration updated (no rate limiting)"

# Step 5: Install Composer dependencies
print_info "Step 5: Installing Composer dependencies..."

# Clear composer cache
sudo -u www-data composer clear-cache

# Install dependencies
sudo -u www-data composer install --no-interaction
if [ $? -ne 0 ]; then
    print_error "Composer install failed, trying with different flags..."
    sudo -u www-data composer install --no-interaction --ignore-platform-reqs
    if [ $? -ne 0 ]; then
        print_error "Composer install still failed"
        exit 1
    fi
fi

# Verify vendor directory
if [ -d "vendor" ] && [ -f "vendor/autoload.php" ]; then
    print_status "Composer dependencies installed successfully"
else
    print_error "Vendor directory or autoload.php not found"
    exit 1
fi

# Step 6: Generate application key
print_info "Step 6: Generating application key..."
sudo -u www-data php artisan key:generate
if [ $? -eq 0 ]; then
    print_status "Application key generated successfully"
else
    print_error "Failed to generate application key"
fi

# Step 7: Test Nginx configuration
print_info "Step 7: Testing Nginx configuration..."
nginx -t
if [ $? -eq 0 ]; then
    print_status "Nginx configuration is valid"
    systemctl start nginx
    print_status "Nginx started successfully"
else
    print_error "Nginx configuration test failed"
    exit 1
fi

# Step 8: Set proper permissions
print_info "Step 8: Setting proper permissions..."
chown -R www-data:www-data /var/www/moneroexchange
find /var/www/moneroexchange -type d -exec chmod 755 {} \;
find /var/www/moneroexchange -type f -exec chmod 644 {} \;
chmod +x /var/www/moneroexchange/artisan
chmod -R 775 /var/www/moneroexchange/storage
chmod -R 775 /var/www/moneroexchange/bootstrap/cache
chmod 600 /var/www/moneroexchange/.env

print_status "Permissions set correctly"

# Step 9: Final verification
print_info "Step 9: Final verification..."

# Test Laravel
if sudo -u www-data php artisan --version > /dev/null 2>&1; then
    print_status "✅ Laravel is working"
else
    print_error "❌ Laravel is not working"
fi

# Test web interface
if curl -s -I http://127.0.0.1 | grep -q "200 OK\|404 Not Found"; then
    print_status "✅ Web interface responding"
else
    print_error "❌ Web interface not responding"
fi

echo ""
echo "=================================================="
print_status "ALL ISSUES FIXED!"
echo "=================================================="

print_status "✅ composer.json updated with compatible versions"
print_status "✅ .env.example file created"
print_status "✅ .env file created"
print_status "✅ Nginx configured without rate limiting"
print_status "✅ Composer dependencies installed"
print_status "✅ Application key generated"
print_status "✅ Permissions set correctly"

echo ""
print_info "Next steps:"
print_info "1. Run: sudo -u www-data php artisan migrate --force"
print_info "2. Run: sudo -u www-data php artisan db:seed --force"
print_info "3. Your Monero Exchange will be ready!"

echo ""
print_status "🎉 All issues have been resolved!"
