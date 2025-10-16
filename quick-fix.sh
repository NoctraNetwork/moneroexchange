#!/bin/bash

# Quick Fix Script for Monero Exchange Server Issues
# Run this to fix the immediate MySQL and Nginx problems

echo "🔧 Quick fix for server issues..."

# Fix Nginx configuration by removing limit_req_zone from server block
echo "Fixing Nginx configuration..."

# Remove limit_req_zone lines from the site config
sed -i '/limit_req_zone/d' /etc/nginx/sites-available/moneroexchange
sed -i '/limit_req_zone/d' /etc/nginx/sites-enabled/moneroexchange

# Create proper rate limiting config in conf.d
mkdir -p /etc/nginx/conf.d
cat > /etc/nginx/conf.d/ratelimit.conf << 'EOF'
# Rate limiting zones (must be in http context)
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/m;
EOF

# Add include to main nginx.conf if not present
if ! grep -q "include /etc/nginx/conf.d/ratelimit.conf;" /etc/nginx/nginx.conf; then
    sed -i '/http {/a\\tinclude /etc/nginx/conf.d/ratelimit.conf;' /etc/nginx/nginx.conf
fi

# Test Nginx configuration
echo "Testing Nginx configuration..."
nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Nginx configuration fixed!"
    systemctl restart nginx
    echo "✅ Nginx restarted successfully"
else
    echo "❌ Nginx configuration still has issues"
    echo "Check the error above and fix manually"
fi

# Fix MySQL by checking the error log
echo "Checking MySQL status..."
systemctl status mysql --no-pager

echo "If MySQL is still failing, check the error log:"
echo "journalctl -xeu mysql.service"

echo "Common MySQL fixes:"
echo "1. Check if port 3306 is already in use: netstat -tlnp | grep 3306"
echo "2. Check MySQL error log: tail -f /var/log/mysql/error.log"
echo "3. Reset MySQL root password if needed"
echo "4. Check disk space: df -h"

echo "🔧 Quick fix completed!"
