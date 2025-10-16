#!/bin/bash

# Comprehensive Setup Verification Script
# This script verifies all components are working correctly

echo "🔍 Verifying Monero Exchange setup..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

print_info() {
    echo -e "${BLUE}[i]${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use sudo)"
    exit 1
fi

echo "Starting comprehensive verification..."

# 1. Check system requirements
print_info "Checking system requirements..."

# Check Ubuntu version
if [ -f /etc/os-release ]; then
    . /etc/os-release
    print_status "OS: $NAME $VERSION"
else
    print_warning "Cannot determine OS version"
fi

# Check available memory
MEMORY_GB=$(free -g | awk 'NR==2{print $2}')
if [ "$MEMORY_GB" -ge 2 ]; then
    print_status "Memory: ${MEMORY_GB}GB (sufficient)"
else
    print_warning "Memory: ${MEMORY_GB}GB (may be insufficient for Monero)"
fi

# Check disk space
DISK_SPACE=$(df -h / | awk 'NR==2{print $4}')
print_status "Available disk space: $DISK_SPACE"

# 2. Check MySQL
print_info "Checking MySQL..."

if systemctl is-active --quiet mysql; then
    print_status "MySQL service is running"
    
    # Test MySQL connection
    if mysql -e "SELECT 1;" >/dev/null 2>&1; then
        print_status "MySQL connection successful"
        
        # Check if database exists
        if mysql -e "USE moneroexchange; SELECT 1;" >/dev/null 2>&1; then
            print_status "Database 'moneroexchange' exists"
        else
            print_warning "Database 'moneroexchange' does not exist"
        fi
        
        # Check if user exists
        if mysql -e "SELECT User FROM mysql.user WHERE User='moneroexchange';" | grep -q moneroexchange; then
            print_status "User 'moneroexchange' exists"
        else
            print_warning "User 'moneroexchange' does not exist"
        fi
    else
        print_error "MySQL connection failed"
    fi
else
    print_error "MySQL service is not running"
    print_info "Run: sudo systemctl start mysql"
fi

# 3. Check Nginx
print_info "Checking Nginx..."

if systemctl is-active --quiet nginx; then
    print_status "Nginx service is running"
    
    # Test Nginx configuration
    if nginx -t >/dev/null 2>&1; then
        print_status "Nginx configuration is valid"
    else
        print_error "Nginx configuration has errors"
        print_info "Run: sudo nginx -t"
    fi
    
    # Check if site is enabled
    if [ -L /etc/nginx/sites-enabled/moneroexchange ]; then
        print_status "Monero Exchange site is enabled"
    else
        print_warning "Monero Exchange site is not enabled"
    fi
    
    # Test HTTP response
    if curl -s -I http://127.0.0.1 | grep -q "200 OK\|404 Not Found"; then
        print_status "Nginx is responding to HTTP requests"
    else
        print_warning "Nginx is not responding to HTTP requests"
    fi
else
    print_error "Nginx service is not running"
    print_info "Run: sudo systemctl start nginx"
fi

# 4. Check Redis
print_info "Checking Redis..."

if systemctl is-active --quiet redis-server; then
    print_status "Redis service is running"
    
    # Test Redis connection
    if redis-cli ping | grep -q PONG; then
        print_status "Redis connection successful"
    else
        print_error "Redis connection failed"
    fi
else
    print_error "Redis service is not running"
    print_info "Run: sudo systemctl start redis-server"
fi

# 5. Check PHP-FPM
print_info "Checking PHP-FPM..."

if systemctl is-active --quiet php8.2-fpm; then
    print_status "PHP-FPM service is running"
    
    # Check PHP version
    PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2)
    print_status "PHP version: $PHP_VERSION"
    
    # Check required extensions
    REQUIRED_EXTENSIONS=("mysql" "redis" "curl" "gd" "mbstring" "xml" "zip" "bcmath" "intl")
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if php -m | grep -q "$ext"; then
            print_status "PHP extension '$ext' is loaded"
        else
            print_warning "PHP extension '$ext' is not loaded"
        fi
    done
else
    print_error "PHP-FPM service is not running"
    print_info "Run: sudo systemctl start php8.2-fpm"
fi

# 6. Check Monero
print_info "Checking Monero..."

# Check if binaries exist
if [ -f /usr/local/bin/monerod ]; then
    print_status "monerod binary exists"
else
    print_error "monerod binary not found"
fi

if [ -f /usr/local/bin/monero-wallet-rpc ]; then
    print_status "monero-wallet-rpc binary exists"
else
    print_error "monero-wallet-rpc binary not found"
fi

# Check monerod service
if systemctl is-active --quiet monerod; then
    print_status "monerod service is running"
    
    # Check if RPC is responding
    if curl -s -u monero:Walnutdesk88? http://127.0.0.1:18081/json_rpc -d '{"jsonrpc":"2.0","id":"0","method":"get_info"}' | grep -q "result"; then
        print_status "monerod RPC is responding"
    else
        print_warning "monerod RPC is not responding (may still be syncing)"
    fi
else
    print_warning "monerod service is not running"
    print_info "Run: sudo systemctl start monerod"
fi

# Check monero-wallet-rpc service
if systemctl is-active --quiet monero-wallet-rpc; then
    print_status "monero-wallet-rpc service is running"
    
    # Check if RPC is responding
    if curl -s -u monero:Walnutdesk88? http://127.0.0.1:18082/json_rpc -d '{"jsonrpc":"2.0","id":"0","method":"get_version"}' | grep -q "result"; then
        print_status "monero-wallet-rpc is responding"
    else
        print_warning "monero-wallet-rpc is not responding"
    fi
else
    print_warning "monero-wallet-rpc service is not running"
    print_info "Run: sudo systemctl start monero-wallet-rpc"
fi

# 7. Check file permissions
print_info "Checking file permissions..."

# Check application directory
if [ -d /var/www/moneroexchange ]; then
    OWNER=$(stat -c '%U:%G' /var/www/moneroexchange)
    if [ "$OWNER" = "www-data:www-data" ]; then
        print_status "Application directory has correct ownership"
    else
        print_warning "Application directory ownership: $OWNER (should be www-data:www-data)"
    fi
else
    print_warning "Application directory does not exist"
fi

# Check Monero directories
MONERO_DIRS=("/var/lib/monero" "/var/log/monero" "/etc/monero")
for dir in "${MONERO_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        OWNER=$(stat -c '%U:%G' "$dir")
        if [ "$OWNER" = "monero:monero" ]; then
            print_status "Directory $dir has correct ownership"
        else
            print_warning "Directory $dir ownership: $OWNER (should be monero:monero)"
        fi
    else
        print_warning "Directory $dir does not exist"
    fi
done

# 8. Check network ports
print_info "Checking network ports..."

PORTS=("80:nginx" "3306:mysql" "6379:redis" "18081:monerod" "18082:monero-wallet-rpc")
for port_info in "${PORTS[@]}"; do
    port=$(echo $port_info | cut -d: -f1)
    service=$(echo $port_info | cut -d: -f2)
    
    if netstat -tlnp | grep -q ":$port "; then
        print_status "Port $port ($service) is listening"
    else
        print_warning "Port $port ($service) is not listening"
    fi
done

# 9. Check configuration files
print_info "Checking configuration files..."

CONFIG_FILES=(
    "/etc/nginx/sites-available/moneroexchange:Nginx site config"
    "/etc/nginx/conf.d/ratelimit.conf:Nginx rate limiting"
    "/etc/mysql/mysql.conf.d/mysqld.cnf:MySQL config"
    "/etc/redis/redis.conf:Redis config"
    "/etc/monero/monerod.conf:Monero daemon config"
)

for config_info in "${CONFIG_FILES[@]}"; do
    file=$(echo $config_info | cut -d: -f1)
    description=$(echo $config_info | cut -d: -f2)
    
    if [ -f "$file" ]; then
        print_status "$description exists"
    else
        print_warning "$description not found: $file"
    fi
done

# 10. Check logs
print_info "Checking log files..."

LOG_DIRS=("/var/log/nginx" "/var/log/mysql" "/var/log/redis" "/var/log/monero")
for log_dir in "${LOG_DIRS[@]}"; do
    if [ -d "$log_dir" ]; then
        print_status "Log directory $log_dir exists"
    else
        print_warning "Log directory $log_dir does not exist"
    fi
done

# 11. Summary
echo ""
echo "=========================================="
print_info "VERIFICATION SUMMARY"
echo "=========================================="

# Count services running
RUNNING_SERVICES=0
TOTAL_SERVICES=6

services=("mysql" "nginx" "redis-server" "php8.2-fpm" "monerod" "monero-wallet-rpc")
for service in "${services[@]}"; do
    if systemctl is-active --quiet $service; then
        ((RUNNING_SERVICES++))
    fi
done

print_info "Services running: $RUNNING_SERVICES/$TOTAL_SERVICES"

if [ $RUNNING_SERVICES -eq $TOTAL_SERVICES ]; then
    print_status "All services are running! 🎉"
else
    print_warning "Some services are not running. Check the details above."
fi

echo ""
print_info "Next steps:"
echo "1. Copy your Laravel application to /var/www/moneroexchange"
echo "2. Run: cd /var/www/moneroexchange && composer install"
echo "3. Run: php artisan key:generate"
echo "4. Run: php artisan migrate"
echo "5. Set permissions: chown -R www-data:www-data /var/www/moneroexchange"
echo "6. Visit http://127.0.0.1 in your browser"

echo ""
print_info "Useful commands:"
echo "• Check all services: sudo systemctl status mysql nginx redis-server php8.2-fpm monerod monero-wallet-rpc"
echo "• Restart all services: sudo systemctl restart mysql nginx redis-server php8.2-fpm monerod monero-wallet-rpc"
echo "• View logs: sudo journalctl -u <service-name> -f"
echo "• Test MySQL: mysql -u moneroexchange -p'Walnutdesk88?' moneroexchange"
echo "• Test Redis: redis-cli ping"
echo "• Test Nginx: curl -I http://127.0.0.1"
