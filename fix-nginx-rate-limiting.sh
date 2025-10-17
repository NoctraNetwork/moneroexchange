#!/bin/bash

# Quick Fix for Nginx Rate Limiting Issue
# This script fixes the "limit_req_zone already bound" error

echo "🔧 Fixing Nginx rate limiting configuration..."

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

# Remove existing rate limiting configuration
print_status "Removing existing rate limiting configuration..."
rm -f /etc/nginx/conf.d/ratelimit.conf

# Remove existing include line from nginx.conf
print_status "Removing existing include line from nginx.conf..."
sed -i '/include \/etc\/nginx\/conf.d\/ratelimit.conf;/d' /etc/nginx/nginx.conf

# Create new rate limiting configuration
print_status "Creating new rate limiting configuration..."
mkdir -p /etc/nginx/conf.d
cat > /etc/nginx/conf.d/ratelimit.conf << 'EOF'
# Rate limiting zones (must be in http context)
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/m;
limit_req_zone $binary_remote_addr zone=general:10m rate=30r/m;

# Connection limiting
limit_conn_zone $binary_remote_addr zone=conn_limit_per_ip:10m;
limit_conn conn_limit_per_ip 20;
EOF

# Add include line to nginx.conf
print_status "Adding include line to nginx.conf..."
sed -i '/http {/a\\tinclude /etc/nginx/conf.d/ratelimit.conf;' /etc/nginx/nginx.conf

# Test Nginx configuration
print_status "Testing Nginx configuration..."
nginx -t

if [ $? -eq 0 ]; then
    print_status "✅ Nginx configuration is valid"
    
    # Start Nginx
    print_status "Starting Nginx..."
    systemctl start nginx
    
    if [ $? -eq 0 ]; then
        print_status "✅ Nginx started successfully"
        print_status "Rate limiting configuration fixed!"
    else
        print_error "❌ Failed to start Nginx"
        print_error "Check logs: sudo journalctl -u nginx -f"
        exit 1
    fi
else
    print_error "❌ Nginx configuration test failed"
    print_error "Check the configuration manually"
    exit 1
fi

echo ""
print_status "Nginx rate limiting issue fixed! 🎉"
print_status "You can now run the installation script again"
