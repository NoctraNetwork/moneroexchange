#!/bin/bash

# Cleanup Script for Monero Exchange Installation
# Run this before installation to clean up any existing configurations

echo "🧹 Cleaning up existing configurations before installation..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use sudo)"
    exit 1
fi

echo "Starting cleanup process..."

# 1. Stop all services
print_status "Stopping services..."
systemctl stop nginx 2>/dev/null || true
systemctl stop mysql 2>/dev/null || true
systemctl stop redis-server 2>/dev/null || true
systemctl stop php8.2-fpm 2>/dev/null || true
systemctl stop monerod 2>/dev/null || true
systemctl stop monero-wallet-rpc 2>/dev/null || true

# 2. Clean up Nginx configurations
print_status "Cleaning up Nginx configurations..."
rm -f /etc/nginx/conf.d/ratelimit.conf
rm -f /etc/nginx/sites-available/moneroexchange
rm -f /etc/nginx/sites-enabled/moneroexchange
rm -f /etc/nginx/sites-enabled/default

# Remove rate limiting include from nginx.conf
sed -i '/include \/etc\/nginx\/conf.d\/ratelimit.conf;/d' /etc/nginx/nginx.conf

# 3. Clean up MySQL configurations
print_status "Cleaning up MySQL configurations..."
# Backup existing config
cp /etc/mysql/mysql.conf.d/mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true

# 4. Clean up Redis configurations
print_status "Cleaning up Redis configurations..."
# Backup existing config
cp /etc/redis/redis.conf /etc/redis/redis.conf.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true

# 5. Clean up Monero configurations
print_status "Cleaning up Monero configurations..."
rm -f /etc/systemd/system/monerod.service
rm -f /etc/systemd/system/monero-wallet-rpc.service
rm -f /etc/monero/monerod.conf
rm -rf /var/lib/monero
rm -rf /var/log/monero
rm -rf /opt/monero

# 6. Clean up application directory
print_status "Cleaning up application directory..."
rm -rf /var/www/moneroexchange

# 7. Clean up PHP-FPM pool
print_status "Cleaning up PHP-FPM pool..."
rm -f /etc/php/8.2/fpm/pool.d/moneroexchange.conf

# 8. Clean up verification script
print_status "Cleaning up verification script..."
rm -f /usr/local/bin/verify-monero-exchange

# 9. Reload systemd
print_status "Reloading systemd..."
systemctl daemon-reload

# 10. Clean up temporary files
print_status "Cleaning up temporary files..."
rm -rf /tmp/monero-linux-x64-v0.18.4.3.tar.bz2
rm -rf /tmp/monero-x86_64-linux-gnu-v0.18.4.3
rm -f /tmp/composer.phar

echo ""
echo "=========================================="
print_status "CLEANUP COMPLETE"
echo "=========================================="

print_status "All existing configurations have been cleaned up"
print_status "You can now run the installation script safely"

echo ""
print_info "Next steps:"
echo "1. Run: sudo ./install-complete.sh"
echo "2. Or run: sudo ./aws-server-setup.sh"
echo "3. Or follow the manual guide: COMPLETE_INSTALLATION_GUIDE.md"

echo ""
print_status "Cleanup completed successfully! 🧹"
