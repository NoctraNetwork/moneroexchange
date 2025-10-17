#!/bin/bash

# IMMEDIATE Nginx Fix - Run this NOW
echo "🚨 IMMEDIATE Nginx Fix - Removing ALL rate limiting duplicates"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use sudo)"
    exit 1
fi

# Stop Nginx immediately
print_status "Stopping Nginx..."
systemctl stop nginx

# 1. COMPLETELY REMOVE ALL RATE LIMITING
print_status "REMOVING ALL existing rate limiting configurations..."

# Remove the rate limiting config file completely
rm -f /etc/nginx/conf.d/ratelimit.conf

# Remove ALL rate limiting from nginx.conf
print_status "Cleaning nginx.conf of ALL rate limiting..."
sed -i '/limit_req_zone/d' /etc/nginx/nginx.conf
sed -i '/limit_conn_zone/d' /etc/nginx/nginx.conf
sed -i '/limit_conn conn_limit_per_ip/d' /etc/nginx/nginx.conf
sed -i '/include.*ratelimit/d' /etc/nginx/nginx.conf

# 2. Create a completely clean nginx.conf backup
print_status "Creating clean nginx.conf..."
cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.backup.$(date +%Y%m%d_%H%M%S)

# 3. Test nginx.conf is clean
print_status "Testing clean nginx.conf..."
nginx -t

if [ $? -eq 0 ]; then
    print_status "✅ nginx.conf is now clean"
else
    print_error "❌ nginx.conf still has issues"
    print_error "Manual cleanup required"
    exit 1
fi

# 4. Start Nginx with clean config
print_status "Starting Nginx with clean configuration..."
systemctl start nginx

if [ $? -eq 0 ]; then
    print_status "✅ Nginx started successfully with clean config"
    print_status "Rate limiting duplicates completely removed!"
else
    print_error "❌ Failed to start Nginx"
    exit 1
fi

echo ""
print_status "🎉 IMMEDIATE FIX COMPLETE!"
print_status "Nginx is now running with NO rate limiting duplicates"
print_status "You can now run the installation script safely"

echo ""
print_status "Next steps:"
print_status "1. Run: sudo ./install-complete.sh"
print_status "2. The installation will now work without rate limiting errors"
