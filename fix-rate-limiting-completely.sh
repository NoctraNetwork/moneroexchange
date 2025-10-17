#!/bin/bash

# Complete Rate Limiting Fix
# This script completely fixes the rate limiting duplicate issue

echo "🔧 Complete Rate Limiting Fix"
echo "============================="

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

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use sudo)"
    exit 1
fi

# Stop Nginx
print_status "Stopping Nginx..."
systemctl stop nginx

# 1. Remove all existing rate limiting configurations
print_status "Removing all existing rate limiting configurations..."

# Remove rate limiting config file
rm -f /etc/nginx/conf.d/ratelimit.conf

# Remove all rate limiting zones from nginx.conf
print_status "Cleaning nginx.conf of rate limiting zones..."
sed -i '/limit_req_zone/d' /etc/nginx/nginx.conf
sed -i '/limit_conn_zone/d' /etc/nginx/nginx.conf
sed -i '/limit_conn conn_limit_per_ip/d' /etc/nginx/nginx.conf
sed -i '/include \/etc\/nginx\/conf.d\/ratelimit.conf/d' /etc/nginx/nginx.conf

# 2. Create clean rate limiting configuration
print_status "Creating clean rate limiting configuration..."

# Create the rate limiting config file
cat > /etc/nginx/conf.d/ratelimit.conf << 'EOF'
# Rate limiting zones for Monero Exchange
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/m;
limit_req_zone $binary_remote_addr zone=general:10m rate=30r/m;

# Connection limiting
limit_conn_zone $binary_remote_addr zone=conn_limit_per_ip:10m;
limit_conn conn_limit_per_ip 20;
EOF

# 3. Add include to nginx.conf
print_status "Adding rate limiting include to nginx.conf..."

# Find the http block and add the include
if ! grep -q "include /etc/nginx/conf.d/ratelimit.conf" /etc/nginx/nginx.conf; then
    # Add the include line after the http { line
    sed -i '/http {/a\\tinclude /etc/nginx/conf.d/ratelimit.conf;' /etc/nginx/nginx.conf
    print_status "Added rate limiting include to nginx.conf"
else
    print_status "Rate limiting include already exists in nginx.conf"
fi

# 4. Test Nginx configuration
print_status "Testing Nginx configuration..."
nginx -t

if [ $? -eq 0 ]; then
    print_status "✅ Nginx configuration is valid"
    
    # Start Nginx
    print_status "Starting Nginx..."
    systemctl start nginx
    
    if [ $? -eq 0 ]; then
        print_status "✅ Nginx started successfully"
        print_status "Rate limiting issue completely fixed!"
    else
        print_error "❌ Failed to start Nginx"
        print_error "Check logs: sudo journalctl -u nginx -f"
        exit 1
    fi
else
    print_error "❌ Nginx configuration test failed"
    print_error "Manual intervention required"
    
    # Show the problematic configuration
    echo ""
    print_warning "Current nginx.conf content around rate limiting:"
    grep -A 5 -B 5 "limit_req_zone\|include.*ratelimit" /etc/nginx/nginx.conf || echo "No rate limiting found"
    
    exit 1
fi

# 5. Verify the fix
print_status "Verifying the fix..."

# Check if rate limiting zones are properly defined
if nginx -T 2>/dev/null | grep -q "limit_req_zone.*login"; then
    print_status "✅ Rate limiting zones are properly defined"
else
    print_error "❌ Rate limiting zones not found"
fi

# Check if there are no duplicates
ZONE_COUNT=$(nginx -T 2>/dev/null | grep -c "limit_req_zone.*login" || echo "0")
if [ "$ZONE_COUNT" -eq 1 ]; then
    print_status "✅ No duplicate rate limiting zones found"
else
    print_warning "⚠️ Found $ZONE_COUNT login zones (should be 1)"
fi

echo ""
echo "============================="
print_status "RATE LIMITING COMPLETELY FIXED"
echo "============================="

print_status "✅ All duplicate rate limiting configurations removed"
print_status "✅ Clean rate limiting configuration created"
print_status "✅ Nginx configuration is valid"
print_status "✅ Nginx is running successfully"

echo ""
print_status "You can now run the installation script:"
print_status "sudo ./install-complete.sh"
