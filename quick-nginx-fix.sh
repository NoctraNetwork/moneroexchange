#!/bin/bash

# Quick Nginx Fix - Run this before install-complete.sh
echo "🔧 Quick Nginx Fix - Installing Nginx first..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
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

# Update system
print_status "Updating system..."
apt update

# Install Nginx
print_status "Installing Nginx..."
apt install -y nginx

# Start Nginx
print_status "Starting Nginx..."
systemctl start nginx
systemctl enable nginx

# Test Nginx
print_status "Testing Nginx configuration..."
nginx -t

if [ $? -eq 0 ]; then
    print_status "✅ Nginx installed successfully"
    print_status "You can now run: sudo ./install-complete.sh"
else
    print_error "❌ Nginx installation failed"
    exit 1
fi

echo ""
print_status "Nginx fix complete! 🎉"
print_status "Now run: sudo ./install-complete.sh"
