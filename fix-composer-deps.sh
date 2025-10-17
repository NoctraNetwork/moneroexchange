#!/bin/bash

# Quick Fix for Composer Dependencies
echo "🔧 Fixing Composer Dependencies..."

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

# Set application directory
APP_DIR="/var/www/moneroexchange"

if [ ! -d "$APP_DIR" ]; then
    print_error "Laravel application not found at $APP_DIR"
    exit 1
fi

cd "$APP_DIR"

# Check if composer.json exists
if [ ! -f "composer.json" ]; then
    print_error "composer.json not found"
    exit 1
fi

print_status "Found composer.json, installing dependencies..."

# Install dependencies with multiple fallback attempts
print_status "Attempt 1: Installing with optimization..."
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction
if [ $? -eq 0 ]; then
    print_status "✅ Dependencies installed with optimization"
else
    print_error "Attempt 1 failed, trying without optimization..."
    
    print_status "Attempt 2: Installing without optimization..."
    sudo -u www-data composer install --no-interaction
    if [ $? -eq 0 ]; then
        print_status "✅ Dependencies installed without optimization"
    else
        print_error "Attempt 2 failed, trying with development dependencies..."
        
        print_status "Attempt 3: Installing with dev dependencies..."
        sudo -u www-data composer install --no-interaction
        if [ $? -eq 0 ]; then
            print_status "✅ Dependencies installed with dev dependencies"
        else
            print_error "All attempts failed"
            exit 1
        fi
    fi
fi

# Verify installation
if [ -d "vendor" ] && [ -f "vendor/autoload.php" ]; then
    print_status "✅ Vendor directory and autoload.php created successfully"
    
    # Test Laravel
    print_status "Testing Laravel..."
    if sudo -u www-data php artisan --version > /dev/null 2>&1; then
        print_status "✅ Laravel is working"
        
        # Generate key
        print_status "Generating application key..."
        sudo -u www-data php artisan key:generate
        if [ $? -eq 0 ]; then
            print_status "✅ Application key generated successfully"
        else
            print_error "❌ Failed to generate application key"
        fi
    else
        print_error "❌ Laravel is not working"
    fi
else
    print_error "❌ Vendor directory or autoload.php not found"
    exit 1
fi

echo ""
print_status "🎉 Composer dependencies fixed successfully!"
print_status "You can now run Laravel commands"
