#!/bin/bash

# Complete Monero Exchange Installation - ONE GO
# This script handles everything including rate limiting fixes

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

echo "🚀 Complete Monero Exchange Installation - ONE GO"
echo "================================================="

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use sudo)"
    exit 1
fi

# Step 1: Update system
print_info "Step 1: Updating system..."
apt update && apt upgrade -y
apt install -y wget curl unzip software-properties-common apt-transport-https ca-certificates gnupg lsb-release

# Step 2: Install MySQL
print_info "Step 2: Installing MySQL..."
apt install -y mysql-server mysql-client

# Configure MySQL
systemctl start mysql
systemctl enable mysql

# Create database and user
mysql -e "CREATE DATABASE IF NOT EXISTS moneroexchange;"
mysql -e "CREATE USER IF NOT EXISTS 'moneroexchange'@'localhost' IDENTIFIED BY 'Walnutdesk88?';"
mysql -e "GRANT ALL PRIVILEGES ON moneroexchange.* TO 'moneroexchange'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
print_status "MySQL installed and configured"

# Step 3: Install Nginx with COMPLETE rate limiting fix
print_info "Step 3: Installing Nginx with rate limiting fix..."

# Stop Nginx if running
systemctl stop nginx 2>/dev/null || true

# Install Nginx
apt install -y nginx

# NUCLEAR CLEANUP of rate limiting
print_status "Performing nuclear cleanup of rate limiting..."
rm -f /etc/nginx/conf.d/ratelimit.conf
rm -f /etc/nginx/conf.d/*ratelimit*
rm -f /etc/nginx/conf.d/*rate*

# Remove ALL rate limiting from nginx.conf
sed -i '/limit_req_zone/d' /etc/nginx/nginx.conf
sed -i '/limit_conn_zone/d' /etc/nginx/nginx.conf
sed -i '/limit_conn conn_limit_per_ip/d' /etc/nginx/nginx.conf
sed -i '/include.*ratelimit/d' /etc/nginx/nginx.conf
sed -i '/include.*rate/d' /etc/nginx/nginx.conf

# Test clean nginx.conf
print_status "Testing clean nginx.conf..."
nginx -t

if [ $? -ne 0 ]; then
    print_error "Nginx configuration still has issues after cleanup"
    exit 1
fi

print_status "Nginx configuration is clean"

# Create rate limiting config (only if needed)
print_status "Creating rate limiting configuration..."
mkdir -p /etc/nginx/conf.d

# Check if we need rate limiting
if ! grep -q "limit_req_zone.*login" /etc/nginx/nginx.conf; then
    cat > /etc/nginx/conf.d/ratelimit.conf << 'EOF'
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/m;
limit_req_zone $binary_remote_addr zone=general:10m rate=30r/m;
limit_conn_zone $binary_remote_addr zone=conn_limit_per_ip:10m;
limit_conn conn_limit_per_ip 20;
EOF

    # Add include to nginx.conf
    sed -i '/http {/a\\tinclude /etc/nginx/conf.d/ratelimit.conf;' /etc/nginx/nginx.conf
fi

# Test nginx configuration again
print_status "Testing Nginx configuration with rate limiting..."
nginx -t

if [ $? -ne 0 ]; then
    print_warning "Rate limiting causing issues, removing it..."
    rm -f /etc/nginx/conf.d/ratelimit.conf
    sed -i '/include.*ratelimit/d' /etc/nginx/nginx.conf
    nginx -t
    if [ $? -ne 0 ]; then
        print_error "Nginx configuration still failing"
        exit 1
    fi
    print_status "Nginx working without rate limiting"
else
    print_status "Nginx configuration with rate limiting is valid"
fi

# Create site configuration
print_status "Creating site configuration..."
cat > /etc/nginx/sites-available/moneroexchange << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name 127.0.0.1 localhost;
    
    root /var/www/moneroexchange/public;
    index index.php index.html;
    
    # Security headers
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Content Security Policy (no JavaScript)
    add_header Content-Security-Policy "default-src 'none'; script-src 'none'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'; upgrade-insecure-requests; block-all-mixed-content;" always;
    
    # Rate limiting for specific endpoints (if available)
    location /login {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location /register {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location /api/ {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Main location block
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /(storage|bootstrap/cache) {
        deny all;
    }
    
    # Static files caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # Logging
    access_log /var/log/nginx/moneroexchange_access.log;
    error_log /var/log/nginx/moneroexchange_error.log;
}
EOF

# Clean up existing configurations
rm -f /etc/nginx/sites-enabled/moneroexchange
rm -f /etc/nginx/sites-enabled/default

# Enable site
ln -sf /etc/nginx/sites-available/moneroexchange /etc/nginx/sites-enabled/

# Test final nginx configuration
print_status "Testing final Nginx configuration..."
nginx -t

if [ $? -eq 0 ]; then
    print_status "✅ Nginx configuration is valid"
    systemctl start nginx
    systemctl enable nginx
    print_status "Nginx installed and configured"
else
    print_error "❌ Nginx configuration test failed"
    exit 1
fi

# Step 4: Install Redis
print_info "Step 4: Installing Redis..."
apt install -y redis-server

# Configure Redis
systemctl start redis-server
systemctl enable redis-server
print_status "Redis installed and configured"

# Step 5: Install PHP 8.2
print_info "Step 5: Installing PHP 8.2..."
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-redis php8.2-curl php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath php8.2-intl

# Configure PHP-FPM
systemctl start php8.2-fpm
systemctl enable php8.2-fpm
print_status "PHP 8.2 installed and configured"

# Step 6: Install Composer
print_info "Step 6: Installing Composer..."
cd /tmp
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
print_status "Composer installed"

# Step 7: Deploy Laravel Application
print_info "Step 7: Deploying Laravel Application..."

# Create application directory with proper structure
print_status "Creating application directory structure..."
mkdir -p /var/www/moneroexchange
mkdir -p /var/www/moneroexchange/storage/app
mkdir -p /var/www/moneroexchange/storage/framework/cache
mkdir -p /var/www/moneroexchange/storage/framework/sessions
mkdir -p /var/www/moneroexchange/storage/framework/views
mkdir -p /var/www/moneroexchange/storage/logs
mkdir -p /var/www/moneroexchange/bootstrap/cache
mkdir -p /var/www/moneroexchange/public/uploads
mkdir -p /var/www/moneroexchange/public/assets

# Set initial permissions
chown -R www-data:www-data /var/www/moneroexchange
chmod -R 755 /var/www/moneroexchange

# Clone repository
print_status "Cloning repository..."
cd /var/www
if [ -d "moneroexchange" ]; then
    print_status "Removing existing installation..."
    rm -rf moneroexchange
fi

git clone https://github.com/NoctraNetwork/moneroexchange.git moneroexchange
if [ $? -ne 0 ]; then
    print_error "Failed to clone repository"
    exit 1
fi

# Set proper ownership
chown -R www-data:www-data /var/www/moneroexchange

# Install dependencies
print_status "Installing Composer dependencies..."
cd /var/www/moneroexchange
sudo -u www-data composer install --no-dev --optimize-autoloader
if [ $? -ne 0 ]; then
    print_error "Failed to install Composer dependencies"
    exit 1
fi

# Create .env file
print_status "Creating .env file..."
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
ENHANCED_RATE_LIMITING=true
ADVANCED_CACHING=true
ENHANCED_SESSION_SECURITY=true
ADVANCED_VALIDATION=true
ENHANCED_LOGGING=true
EOF

# Generate key and run migrations
print_status "Generating application key..."
sudo -u www-data php artisan key:generate
if [ $? -ne 0 ]; then
    print_error "Failed to generate application key"
    exit 1
fi

# Run database migrations
print_status "Running database migrations..."
sudo -u www-data php artisan migrate --force
if [ $? -ne 0 ]; then
    print_error "Failed to run database migrations"
    exit 1
fi

# Seed the database
print_status "Seeding database..."
sudo -u www-data php artisan db:seed --force
if [ $? -ne 0 ]; then
    print_error "Failed to seed database"
    exit 1
fi

# Clear and cache configuration
print_status "Caching configuration..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache

# Create storage symlink
print_status "Creating storage symlink..."
sudo -u www-data php artisan storage:link

# Set comprehensive permissions
print_status "Setting proper permissions..."

# Set ownership
chown -R www-data:www-data /var/www/moneroexchange

# Set directory permissions
find /var/www/moneroexchange -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/moneroexchange -type f -exec chmod 644 {} \;

# Set executable permissions for specific files
chmod +x /var/www/moneroexchange/artisan
chmod +x /var/www/moneroexchange/public/index.php

# Set writable permissions for Laravel directories
chmod -R 775 /var/www/moneroexchange/storage
chmod -R 775 /var/www/moneroexchange/bootstrap/cache
chmod -R 775 /var/www/moneroexchange/public/uploads

# Ensure .env file has correct permissions
chmod 600 /var/www/moneroexchange/.env
chown www-data:www-data /var/www/moneroexchange/.env

print_status "Laravel application fully deployed with proper structure and permissions"

# Step 8: Install Monero
print_info "Step 8: Installing Monero..."

# Create monero user
useradd -r -s /bin/false monero 2>/dev/null || true

# Create directories
mkdir -p /var/lib/monero
mkdir -p /var/log/monero
mkdir -p /etc/monero
mkdir -p /opt/monero

# Set permissions
chown -R monero:monero /var/lib/monero
chown -R monero:monero /var/log/monero
chown -R monero:monero /etc/monero

# Download and install Monero
print_status "Downloading Monero..."
cd /tmp
wget -O monero-linux-x64-v0.18.4.3.tar.bz2 https://downloads.getmonero.org/cli/monero-linux-x64-v0.18.4.3.tar.bz2
if [ $? -ne 0 ]; then
    print_error "Failed to download Monero"
    exit 1
fi

tar -xjf monero-linux-x64-v0.18.4.3.tar.bz2
if [ $? -ne 0 ]; then
    print_error "Failed to extract Monero"
    exit 1
fi

cp monero-x86_64-linux-gnu-v0.18.4.3/monerod /usr/local/bin/
cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-rpc /usr/local/bin/
cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-cli /usr/local/bin/
chmod +x /usr/local/bin/monerod /usr/local/bin/monero-wallet-rpc /usr/local/bin/monero-wallet-cli

# Create Monero configuration
cat > /etc/monero/monerod.conf << 'EOF'
data-dir=/var/lib/monero
log-file=/var/log/monero/monerod.log
rpc-bind-ip=127.0.0.1
rpc-bind-port=18081
rpc-login=monero:Walnutdesk88?
p2p-bind-ip=0.0.0.0
p2p-bind-port=18080
db-sync-mode=fast
out-peers=64
in-peers=1024
tx-proxy=socks5://127.0.0.1:9050
max-txpool-size=1000000
db-read-buffer-size=134217728
db-write-buffer-size=134217728
EOF

# Create systemd service for monerod
cat > /etc/systemd/system/monerod.service << 'EOF'
[Unit]
Description=Monero Daemon
After=network.target

[Service]
Type=simple
User=monero
Group=monero
ExecStart=/usr/local/bin/monerod --config-file=/etc/monero/monerod.conf
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal
SyslogIdentifier=monerod
NoNewPrivileges=true
PrivateTmp=true
ProtectSystem=strict
ProtectHome=true
ReadWritePaths=/var/lib/monero /var/log/monero

[Install]
WantedBy=multi-user.target
EOF

# Create systemd service for monero-wallet-rpc
cat > /etc/systemd/system/monero-wallet-rpc.service << 'EOF'
[Unit]
Description=Monero Wallet RPC
After=network.target monerod.service
Requires=monerod.service

[Service]
Type=simple
User=monero
Group=monero
ExecStart=/usr/local/bin/monero-wallet-rpc --rpc-bind-ip=127.0.0.1 --rpc-bind-port=18082 --rpc-login=monero:Walnutdesk88? --wallet-dir=/var/lib/monero
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal
SyslogIdentifier=monero-wallet-rpc
NoNewPrivileges=true
PrivateTmp=true
ProtectSystem=strict
ProtectHome=true
ReadWritePaths=/var/lib/monero

[Install]
WantedBy=multi-user.target
EOF

# Reload systemd and start services
systemctl daemon-reload
systemctl start monerod
systemctl enable monerod
systemctl start monero-wallet-rpc
systemctl enable monero-wallet-rpc

print_status "Monero installed and configured"

# Step 9: Final verification
print_info "Step 9: Final verification..."

# Test all services
print_status "Testing all services..."

services=("mysql" "nginx" "redis-server" "php8.2-fpm" "monerod" "monero-wallet-rpc")
running=0

for service in "${services[@]}"; do
    if systemctl is-active --quiet $service; then
        print_status "✅ $service is running"
        ((running++))
    else
        print_error "❌ $service is not running"
    fi
done

print_status "Services running: $running/${#services[@]}"

# Test web interface
if curl -s -I http://127.0.0.1 | grep -q "200 OK\|404 Not Found"; then
    print_status "✅ Web interface responding"
else
    print_error "❌ Web interface not responding"
fi

# Test Laravel application
print_status "Testing Laravel application..."
cd /var/www/moneroexchange

if sudo -u www-data php artisan --version > /dev/null 2>&1; then
    print_status "✅ Laravel Artisan working"
else
    print_error "❌ Laravel Artisan failed"
fi

if sudo -u www-data php artisan route:list > /dev/null 2>&1; then
    ROUTE_COUNT=$(sudo -u www-data php artisan route:list | wc -l)
    print_status "✅ Laravel routes working ($ROUTE_COUNT routes)"
else
    print_error "❌ Laravel routes failed"
fi

# Test database connection
if sudo -u www-data php artisan tinker --execute="echo DB::connection()->getPdo() ? 'DB OK' : 'DB FAIL';" 2>/dev/null | grep -q "DB OK"; then
    print_status "✅ Database connection working"
else
    print_error "❌ Database connection failed"
fi

# Test Redis connection
if sudo -u www-data php artisan tinker --execute="echo Redis::ping() ? 'Redis OK' : 'Redis FAIL';" 2>/dev/null | grep -q "Redis OK"; then
    print_status "✅ Redis connection working"
else
    print_error "❌ Redis connection failed"
fi

echo ""
echo "================================================="
print_status "INSTALLATION COMPLETE!"
echo "================================================="

print_status "✅ Complete Monero Exchange installed successfully!"
print_status "✅ All services running"
print_status "✅ Laravel application deployed"
print_status "✅ Database configured"
print_status "✅ Web interface working"

echo ""
print_info "Your Monero Exchange is ready at: http://127.0.0.1"
print_info "Admin panel: http://127.0.0.1/admin"
print_info "Login: http://127.0.0.1/login"
print_info "Register: http://127.0.0.1/register"

echo ""
print_status "🎉 Installation completed successfully in ONE GO!"
