#!/bin/bash

# Test Installation Script
# This script tests all components before running the actual installation

echo "🧪 Testing Monero Exchange Installation Components..."

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

echo "Starting component testing..."

# Test 1: Check system requirements
print_info "Testing system requirements..."

# Check Ubuntu version
if [ -f /etc/os-release ]; then
    . /etc/os-release
    if [[ "$VERSION_ID" == "20.04" || "$VERSION_ID" == "22.04" ]]; then
        print_status "Ubuntu version $VERSION_ID is supported"
    else
        print_warning "Ubuntu version $VERSION_ID may not be fully supported"
    fi
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
DISK_SPACE_GB=$(df -BG / | awk 'NR==2{print $4}' | sed 's/G//')
if [ "$DISK_SPACE_GB" -ge 20 ]; then
    print_status "Disk space: ${DISK_SPACE_GB}GB (sufficient)"
else
    print_warning "Disk space: ${DISK_SPACE_GB}GB (may be insufficient)"
fi

# Test 2: Check internet connectivity
print_info "Testing internet connectivity..."

if ping -c 1 8.8.8.8 >/dev/null 2>&1; then
    print_status "Internet connectivity working"
else
    print_error "No internet connectivity"
    exit 1
fi

# Test 3: Check package repositories
print_info "Testing package repositories..."

apt update >/dev/null 2>&1
if [ $? -eq 0 ]; then
    print_status "Package repositories accessible"
else
    print_error "Cannot access package repositories"
    exit 1
fi

# Test 4: Check if required packages are available
print_info "Testing package availability..."

PACKAGES=("mysql-server" "nginx" "redis-server" "wget" "curl" "git")
for package in "${PACKAGES[@]}"; do
    if apt-cache show "$package" >/dev/null 2>&1; then
        print_status "Package $package is available"
    else
        print_error "Package $package is not available"
        exit 1
    fi
done

# Test 5: Check PHP repository
print_info "Testing PHP repository..."

add-apt-repository ppa:ondrej/php -y >/dev/null 2>&1
apt update >/dev/null 2>&1
if apt-cache show php8.2-fpm >/dev/null 2>&1; then
    print_status "PHP 8.2 repository is accessible"
else
    print_error "PHP 8.2 repository is not accessible"
    exit 1
fi

# Test 6: Check Monero download
print_info "Testing Monero download..."

cd /tmp
wget --spider https://downloads.getmonero.org/cli/monero-linux-x64-v0.18.4.3.tar.bz2 >/dev/null 2>&1
if [ $? -eq 0 ]; then
    print_status "Monero download URL is accessible"
else
    print_error "Monero download URL is not accessible"
    exit 1
fi

# Test 7: Check GitHub repository
print_info "Testing GitHub repository..."

if curl -s https://api.github.com/repos/NoctraNetwork/moneroexchange >/dev/null 2>&1; then
    print_status "GitHub repository is accessible"
else
    print_error "GitHub repository is not accessible"
    exit 1
fi

# Test 8: Check Composer
print_info "Testing Composer..."

if curl -s https://getcomposer.org/installer >/dev/null 2>&1; then
    print_status "Composer installer is accessible"
else
    print_error "Composer installer is not accessible"
    exit 1
fi

# Test 9: Check port availability
print_info "Testing port availability..."

PORTS=("80" "3306" "6379" "18081" "18082")
for port in "${PORTS[@]}"; do
    if ! netstat -tlnp | grep -q ":$port "; then
        print_status "Port $port is available"
    else
        print_warning "Port $port is already in use"
    fi
done

# Test 10: Check directory permissions
print_info "Testing directory permissions..."

DIRS=("/var/www" "/etc/nginx" "/etc/mysql" "/etc/redis" "/usr/local/bin")
for dir in "${DIRS[@]}"; do
    if [ -d "$dir" ] || [ -w "$(dirname "$dir")" ]; then
        print_status "Directory $dir is writable"
    else
        print_error "Directory $dir is not writable"
        exit 1
    fi
done

# Test 11: Check systemd
print_info "Testing systemd..."

if systemctl --version >/dev/null 2>&1; then
    print_status "Systemd is available"
else
    print_error "Systemd is not available"
    exit 1
fi

# Test 12: Check required commands
print_info "Testing required commands..."

COMMANDS=("wget" "curl" "git" "tar" "chmod" "chown" "systemctl" "mysql" "nginx" "redis-cli")
for cmd in "${COMMANDS[@]}"; do
    if command -v "$cmd" >/dev/null 2>&1; then
        print_status "Command $cmd is available"
    else
        print_warning "Command $cmd is not available (will be installed)"
    fi
done

echo ""
echo "=========================================="
print_info "TESTING SUMMARY"
echo "=========================================="

print_status "All critical components tested successfully!"
print_status "System is ready for Monero Exchange installation"

echo ""
print_info "You can now run the installation:"
echo "  sudo ./install-complete.sh"
echo ""
print_info "Or follow the manual guide:"
echo "  Follow COMPLETE_INSTALLATION_GUIDE.md"
echo ""
print_status "Installation testing completed! 🎉"
