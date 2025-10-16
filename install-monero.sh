#!/bin/bash

# Monero Installation Script for AWS Server
# This script installs Monero daemon and wallet RPC with proper permissions

echo "🔧 Installing Monero on AWS server..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use sudo)"
    exit 1
fi

# Set variables
MONERO_VERSION="0.18.4.3"
MONERO_DIR="/opt/monero"
MONERO_USER="monero"
MONERO_GROUP="monero"

print_status "Installing Monero v${MONERO_VERSION}..."

# 1. Create monero user and group
print_status "Creating monero user and group..."
groupadd -r $MONERO_GROUP 2>/dev/null || true
useradd -r -g $MONERO_GROUP -d $MONERO_DIR -s /bin/false $MONERO_USER 2>/dev/null || true

# 2. Create directories
print_status "Creating directories..."
mkdir -p $MONERO_DIR
mkdir -p /var/log/monero
mkdir -p /var/lib/monero
mkdir -p /etc/monero

# 3. Download Monero
print_status "Downloading Monero..."
cd /tmp
wget -O monero-linux-x64-v${MONERO_VERSION}.tar.bz2 https://downloads.getmonero.org/cli/monero-linux-x64-v${MONERO_VERSION}.tar.bz2

if [ $? -ne 0 ]; then
    print_error "Failed to download Monero"
    exit 1
fi

# 4. Extract Monero
print_status "Extracting Monero..."
tar -xjf monero-linux-x64-v${MONERO_VERSION}.tar.bz2

if [ $? -ne 0 ]; then
    print_error "Failed to extract Monero"
    exit 1
fi

# 5. Install binaries
print_status "Installing Monero binaries..."
cp monero-x86_64-linux-gnu-v${MONERO_VERSION}/monerod /usr/local/bin/
cp monero-x86_64-linux-gnu-v${MONERO_VERSION}/monero-wallet-rpc /usr/local/bin/
cp monero-x86_64-linux-gnu-v${MONERO_VERSION}/monero-wallet-cli /usr/local/bin/

# Set permissions
chmod +x /usr/local/bin/monerod
chmod +x /usr/local/bin/monero-wallet-rpc
chmod +x /usr/local/bin/monero-wallet-cli

# 6. Create systemd service for monerod
print_status "Creating monerod systemd service..."
cat > /etc/systemd/system/monerod.service << 'EOF'
[Unit]
Description=Monero Daemon
After=network.target

[Service]
Type=simple
User=monero
Group=monero
ExecStart=/usr/local/bin/monerod --config-file=/etc/monero/monerod.conf
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal
SyslogIdentifier=monerod

# Security settings
NoNewPrivileges=true
PrivateTmp=true
ProtectSystem=strict
ProtectHome=true
ReadWritePaths=/var/lib/monero /var/log/monero

[Install]
WantedBy=multi-user.target
EOF

# 7. Create monerod configuration
print_status "Creating monerod configuration..."
cat > /etc/monero/monerod.conf << 'EOF'
# Monero daemon configuration
# Data directory
data-dir=/var/lib/monero

# Log file
log-file=/var/log/monero/monerod.log

# RPC settings
rpc-bind-ip=127.0.0.1
rpc-bind-port=18081
rpc-login=monero:Walnutdesk88?

# P2P settings
p2p-bind-ip=0.0.0.0
p2p-bind-port=18080

# Database settings
db-sync-mode=fast

# Network settings
out-peers=64
in-peers=1024

# Tor settings (for onion sites)
tx-proxy=socks5://127.0.0.1:9050

# Performance settings
max-txpool-size=1000000
db-read-buffer-size=134217728
db-write-buffer-size=134217728
EOF

# 8. Create systemd service for monero-wallet-rpc
print_status "Creating monero-wallet-rpc systemd service..."
cat > /etc/systemd/system/monero-wallet-rpc.service << 'EOF'
[Unit]
Description=Monero Wallet RPC
After=network.target monerod.service
Requires=monerod.service

[Service]
Type=simple
User=monero
Group=monero
ExecStart=/usr/local/bin/monero-wallet-rpc --rpc-bind-ip=127.0.0.1 --rpc-bind-port=18082 --rpc-login=monero:Walnutdesk88? --daemon-address=127.0.0.1:18081 --wallet-dir=/var/lib/monero/wallets
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal
SyslogIdentifier=monero-wallet-rpc

# Security settings
NoNewPrivileges=true
PrivateTmp=true
ProtectSystem=strict
ProtectHome=true
ReadWritePaths=/var/lib/monero /var/log/monero

[Install]
WantedBy=multi-user.target
EOF

# 9. Set proper ownership and permissions
print_status "Setting permissions..."
chown -R $MONERO_USER:$MONERO_GROUP $MONERO_DIR
chown -R $MONERO_USER:$MONERO_GROUP /var/lib/monero
chown -R $MONERO_USER:$MONERO_GROUP /var/log/monero
chown -R $MONERO_USER:$MONERO_GROUP /etc/monero

chmod 755 $MONERO_DIR
chmod 755 /var/lib/monero
chmod 755 /var/log/monero
chmod 755 /etc/monero
chmod 644 /etc/monero/monerod.conf

# 10. Create wallet directory
print_status "Creating wallet directory..."
mkdir -p /var/lib/monero/wallets
chown -R $MONERO_USER:$MONERO_GROUP /var/lib/monero/wallets
chmod 755 /var/lib/monero/wallets

# 11. Reload systemd and enable services
print_status "Enabling services..."
systemctl daemon-reload
systemctl enable monerod
systemctl enable monero-wallet-rpc

# 12. Start services
print_status "Starting Monero services..."
systemctl start monerod

# Wait a moment for monerod to start
sleep 5

systemctl start monero-wallet-rpc

# 13. Check status
print_status "Checking service status..."

if systemctl is-active --quiet monerod; then
    print_status "✅ monerod is running"
else
    print_error "❌ monerod failed to start"
    systemctl status monerod --no-pager
fi

if systemctl is-active --quiet monero-wallet-rpc; then
    print_status "✅ monero-wallet-rpc is running"
else
    print_error "❌ monero-wallet-rpc failed to start"
    systemctl status monero-wallet-rpc --no-pager
fi

# 14. Clean up
print_status "Cleaning up..."
rm -rf /tmp/monero-linux-x64-v${MONERO_VERSION}.tar.bz2
rm -rf /tmp/monero-x86_64-linux-gnu-v${MONERO_VERSION}

# 15. Show useful commands
print_status "🎉 Monero installation completed!"
print_status ""
print_status "Useful commands:"
print_status "  Check monerod status: sudo systemctl status monerod"
print_status "  Check wallet-rpc status: sudo systemctl status monero-wallet-rpc"
print_status "  View monerod logs: sudo journalctl -u monerod -f"
print_status "  View wallet-rpc logs: sudo journalctl -u monero-wallet-rpc -f"
print_status "  Restart services: sudo systemctl restart monerod monero-wallet-rpc"
print_status ""
print_status "Configuration files:"
print_status "  Monerod config: /etc/monero/monerod.conf"
print_status "  Wallet directory: /var/lib/monero/wallets"
print_status "  Logs: /var/log/monero/"
print_status ""
print_status "RPC endpoints:"
print_status "  Monerod RPC: http://127.0.0.1:18081"
print_status "  Wallet RPC: http://127.0.0.1:18082"
print_status "  Username: monero"
print_status "  Password: Walnutdesk88?"
