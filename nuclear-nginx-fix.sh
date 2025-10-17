#!/bin/bash

# NUCLEAR Nginx Fix - Completely removes ALL rate limiting
echo "💥 NUCLEAR Nginx Fix - Removing ALL rate limiting completely"

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

# Stop Nginx
print_status "Stopping Nginx..."
systemctl stop nginx

# 1. NUCLEAR OPTION - Remove ALL rate limiting files
print_status "NUCLEAR: Removing ALL rate limiting files..."
rm -f /etc/nginx/conf.d/ratelimit.conf
rm -f /etc/nginx/conf.d/*ratelimit*
rm -f /etc/nginx/conf.d/*rate*

# 2. NUCLEAR OPTION - Remove ALL rate limiting from nginx.conf
print_status "NUCLEAR: Removing ALL rate limiting from nginx.conf..."
sed -i '/limit_req_zone/d' /etc/nginx/nginx.conf
sed -i '/limit_conn_zone/d' /etc/nginx/nginx.conf
sed -i '/limit_conn conn_limit_per_ip/d' /etc/nginx/nginx.conf
sed -i '/include.*ratelimit/d' /etc/nginx/nginx.conf
sed -i '/include.*rate/d' /etc/nginx/nginx.conf

# 3. Test clean nginx.conf
print_status "Testing clean nginx.conf..."
nginx -t

if [ $? -eq 0 ]; then
    print_status "✅ nginx.conf is clean and working"
    
    # Start Nginx
    print_status "Starting Nginx..."
    systemctl start nginx
    
    if [ $? -eq 0 ]; then
        print_status "✅ Nginx started successfully"
        print_status "ALL rate limiting completely removed!"
        
        echo ""
        print_status "🎉 NUCLEAR FIX COMPLETE!"
        print_status "Nginx is running with NO rate limiting at all"
        print_status "You can now run the installation script"
        
        echo ""
        print_status "Next steps:"
        print_status "1. Run: sudo ./install-complete.sh"
        print_status "2. The installation will work without rate limiting errors"
        
    else
        print_error "❌ Failed to start Nginx"
        exit 1
    fi
else
    print_error "❌ nginx.conf still has issues after nuclear cleanup"
    print_error "Manual intervention required"
    
    # Show what's still in nginx.conf
    echo ""
    print_error "Remaining content in nginx.conf:"
    grep -n "limit_req\|limit_conn\|include.*rate" /etc/nginx/nginx.conf || echo "No rate limiting found"
    
    exit 1
fi
