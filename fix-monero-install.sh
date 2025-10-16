#!/bin/bash

# Quick Fix for Monero Installation Issues
# Run this script to fix the permission and extraction problems

echo "🔧 Fixing Monero installation issues..."

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Please run as root (use sudo)"
    exit 1
fi

# Set variables
MONERO_VERSION="0.18.4.3"
MONERO_DIR="/opt/monero"

echo "📁 Creating proper directories..."
mkdir -p $MONERO_DIR
chmod 755 $MONERO_DIR

echo "📥 Downloading Monero with proper permissions..."
cd /tmp
wget -O monero-linux-x64-v${MONERO_VERSION}.tar.bz2 https://downloads.getmonero.org/cli/monero-linux-x64-v${MONERO_VERSION}.tar.bz2

if [ $? -ne 0 ]; then
    echo "❌ Failed to download Monero"
    exit 1
fi

echo "📦 Extracting Monero with proper permissions..."
tar -xjf monero-linux-x64-v${MONERO_VERSION}.tar.bz2

if [ $? -ne 0 ]; then
    echo "❌ Failed to extract Monero"
    exit 1
fi

echo "📋 Installing Monero binaries..."
cp monero-x86_64-linux-gnu-v${MONERO_VERSION}/monerod /usr/local/bin/
cp monero-x86_64-linux-gnu-v${MONERO_VERSION}/monero-wallet-rpc /usr/local/bin/
cp monero-x86_64-linux-gnu-v${MONERO_VERSION}/monero-wallet-cli /usr/local/bin/

# Set proper permissions
chmod +x /usr/local/bin/monerod
chmod +x /usr/local/bin/monero-wallet-rpc
chmod +x /usr/local/bin/monero-wallet-cli

echo "👤 Creating monero user..."
groupadd -r monero 2>/dev/null || true
useradd -r -g monero -d $MONERO_DIR -s /bin/false monero 2>/dev/null || true

echo "📁 Creating data directories..."
mkdir -p /var/lib/monero
mkdir -p /var/log/monero
mkdir -p /etc/monero
mkdir -p /var/lib/monero/wallets

echo "🔐 Setting proper ownership..."
chown -R monero:monero $MONERO_DIR
chown -R monero:monero /var/lib/monero
chown -R monero:monero /var/log/monero
chown -R monero:monero /etc/monero

echo "🧹 Cleaning up..."
rm -rf /tmp/monero-linux-x64-v${MONERO_VERSION}.tar.bz2
rm -rf /tmp/monero-x86_64-linux-gnu-v${MONERO_VERSION}

echo "✅ Monero installation fixed!"
echo ""
echo "Next steps:"
echo "1. Run: sudo ./install-monero.sh (for full systemd setup)"
echo "2. Or manually start: sudo monerod --rpc-bind-ip=127.0.0.1 --rpc-bind-port=18081"
echo "3. Check if working: sudo systemctl status monerod"
