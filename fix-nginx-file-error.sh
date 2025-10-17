#!/bin/bash

# Quick Fix for Nginx File Error
# This script fixes the "can't read /etc/nginx/nginx.conf" error

echo "🔧 Fixing Nginx file error..."

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

# Install Nginx first
print_status "Installing Nginx..."
apt update
apt install -y nginx

# Start Nginx
print_status "Starting Nginx..."
systemctl start nginx
systemctl enable nginx

# Test Nginx configuration
print_status "Testing Nginx configuration..."
nginx -t

if [ $? -eq 0 ]; then
    print_status "✅ Nginx installed and configured successfully"
    print_status "You can now run the installation script again"
    echo ""
    print_status "Run: sudo ./install-complete.sh"
else
    print_error "❌ Nginx configuration failed"
    print_error "Check logs: sudo journalctl -u nginx -f"
    exit 1
fi

echo ""
print_status "Nginx file error fixed! 🎉"
