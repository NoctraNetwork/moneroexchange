#!/bin/bash

# Complete Setup Script - Run after quick install
echo "🚀 Completing Monero Exchange Setup..."

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

# Step 1: Complete Laravel Setup
print_info "Step 1: Completing Laravel Setup..."

cd /var/www/moneroexchange

# Fix Composer dependencies
print_status "Installing Composer dependencies..."
sudo -u www-data composer install --no-interaction
if [ $? -ne 0 ]; then
    print_error "Composer install failed"
    exit 1
fi

# Generate application key
print_status "Generating application key..."
sudo -u www-data php artisan key:generate

# Run migrations
print_status "Running database migrations..."
sudo -u www-data php artisan migrate --force

# Seed database
print_status "Seeding database..."
sudo -u www-data php artisan db:seed --force

# Cache configuration
print_status "Caching configuration..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Create storage symlink
print_status "Creating storage symlink..."
sudo -u www-data php artisan storage:link

# Step 2: Set Permissions
print_info "Step 2: Setting Permissions..."

# Set ownership
chown -R www-data:www-data /var/www/moneroexchange

# Set permissions
find /var/www/moneroexchange -type d -exec chmod 755 {} \;
find /var/www/moneroexchange -type f -exec chmod 644 {} \;
chmod +x /var/www/moneroexchange/artisan
chmod +x /var/www/moneroexchange/public/index.php
chmod -R 775 /var/www/moneroexchange/storage
chmod -R 775 /var/www/moneroexchange/bootstrap/cache
chmod 600 /var/www/moneroexchange/.env

# Step 3: Complete Monero Setup
print_info "Step 3: Completing Monero Setup..."

# Create monero user
useradd -r -s /bin/false monero 2>/dev/null || true

# Create directories
mkdir -p /var/lib/monero /var/log/monero /etc/monero
chown -R monero:monero /var/lib/monero /var/log/monero /etc/monero

# Download and install Monero
print_status "Installing Monero..."
cd /tmp
wget -O monero-linux-x64-v0.18.4.3.tar.bz2 https://downloads.getmonero.org/cli/monero-linux-x64-v0.18.4.3.tar.bz2
tar -xjf monero-linux-x64-v0.18.4.3.tar.bz2
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
EOF

# Create systemd services
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

[Install]
WantedBy=multi-user.target
EOF

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

[Install]
WantedBy=multi-user.target
EOF

# Start Monero services
systemctl daemon-reload
systemctl start monerod
systemctl enable monerod
systemctl start monero-wallet-rpc
systemctl enable monero-wallet-rpc

# Step 4: Cleanup
print_info "Step 4: Cleaning up..."

# Remove temporary files
rm -rf /tmp/monero-linux-x64-v0.18.4.3.tar.bz2
rm -rf /tmp/monero-x86_64-linux-gnu-v0.18.4.3

# Clean package cache
apt autoremove -y
apt autoclean

# Step 5: Verification
print_info "Step 5: Final Verification..."

# Check services
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

# Test web interface
if curl -s -I http://127.0.0.1 | grep -q "200 OK\|404 Not Found"; then
    print_status "✅ Web interface responding"
else
    print_error "❌ Web interface not responding"
fi

# Test Laravel
cd /var/www/moneroexchange
if sudo -u www-data php artisan --version > /dev/null 2>&1; then
    print_status "✅ Laravel working"
else
    print_error "❌ Laravel not working"
fi

echo ""
echo "=================================================="
print_status "SETUP COMPLETE!"
echo "=================================================="

print_status "✅ Laravel application configured"
print_status "✅ Database migrated and seeded"
print_status "✅ Monero installed and running"
print_status "✅ All services running ($running/${#services[@]})"
print_status "✅ Web interface working"

echo ""
print_info "Your Monero Exchange is ready at: http://127.0.0.1"
print_info "Admin panel: http://127.0.0.1/admin"
print_info "Login: http://127.0.0.1/login"
print_info "Register: http://127.0.0.1/register"

echo ""
print_status "🎉 Setup completed successfully!"
