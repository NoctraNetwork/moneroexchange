#!/bin/bash

# Complete AWS Server Setup for Monero Exchange
# This script fixes all issues: MySQL, Nginx, Redis, and Monero installation

echo "🚀 Setting up Monero Exchange on AWS server..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use sudo)"
    exit 1
fi

print_status "Starting complete AWS server setup..."

# 1. Update system
print_step "Updating system packages..."
apt update && apt upgrade -y

# 2. Install required packages
print_step "Installing required packages..."
apt install -y wget curl unzip software-properties-common apt-transport-https ca-certificates gnupg lsb-release

# 3. Fix MySQL
print_step "Fixing MySQL configuration..."

# Stop MySQL if running
systemctl stop mysql 2>/dev/null || true

# Create minimal MySQL config
cat > /etc/mysql/mysql.conf.d/mysqld.cnf << 'EOF'
[mysqld]
user = mysql
pid-file = /var/run/mysqld/mysqld.pid
socket = /var/run/mysqld/mysqld.sock
port = 3306
basedir = /usr
datadir = /var/lib/mysql
tmpdir = /tmp
lc-messages-dir = /usr/share/mysql
skip-external-locking

# Security settings
bind-address = 127.0.0.1
local-infile = 0

# Character set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# InnoDB settings
default-storage-engine = InnoDB
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
innodb_flush_log_at_trx_commit = 2
innodb_lock_wait_timeout = 50

# Connection settings
max_connections = 100
max_connect_errors = 10
connect_timeout = 10
wait_timeout = 28800
interactive_timeout = 28800

# Query cache
# Query cache removed (deprecated in MySQL 8.0+)

# Logging
log-error = /var/log/mysql/error.log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Binary logging
log-bin = mysql-bin
binlog_format = ROW
expire_logs_days = 7

# SSL settings (disabled for localhost)
skip-ssl

[mysql]
default-character-set = utf8mb4

[mysqldump]
default-character-set = utf8mb4
EOF

# Fix MySQL permissions
chown -R mysql:mysql /var/lib/mysql
chmod -R 755 /var/lib/mysql

# Start MySQL
print_status "Starting MySQL..."
systemctl start mysql
if [ $? -eq 0 ]; then
    print_status "✅ MySQL started successfully"
    systemctl enable mysql
else
    print_error "❌ Failed to start MySQL"
    print_status "Checking MySQL error log..."
    journalctl -xeu mysql.service --no-pager | tail -20
    exit 1
fi

# 4. Fix Nginx
print_step "Fixing Nginx configuration..."

# Create rate limiting config
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

# Add include to main nginx.conf if not present
if ! grep -q "include /etc/nginx/conf.d/ratelimit.conf;" /etc/nginx/nginx.conf; then
    sed -i '/http {/a\\tinclude /etc/nginx/conf.d/ratelimit.conf;' /etc/nginx/nginx.conf
fi

# Create site configuration
cat > /etc/nginx/sites-available/moneroexchange << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name 127.0.0.1 localhost;
    
    root /var/www/moneroexchange/public;
    index index.php index.html;
    
    # Security headers
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Content Security Policy (no JavaScript)
    add_header Content-Security-Policy "default-src 'none'; script-src 'none'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'; upgrade-insecure-requests; block-all-mixed-content;" always;
    
    # Rate limiting for specific endpoints
    location /login {
        limit_req zone=login burst=3 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location /register {
        limit_req zone=login burst=2 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location /api/ {
        limit_req zone=api burst=5 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Main location block
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /(storage|bootstrap/cache) {
        deny all;
    }
    
    # Static files caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # Logging
    access_log /var/log/nginx/moneroexchange_access.log;
    error_log /var/log/nginx/moneroexchange_error.log;
}
EOF

# Enable site
ln -sf /etc/nginx/sites-available/moneroexchange /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
print_status "Testing Nginx configuration..."
nginx -t
if [ $? -eq 0 ]; then
    print_status "✅ Nginx configuration is valid"
    systemctl restart nginx
    systemctl enable nginx
else
    print_error "❌ Nginx configuration test failed"
    exit 1
fi

# 5. Install and configure Redis
print_step "Installing and configuring Redis..."
apt install -y redis-server

# Configure Redis
cat > /etc/redis/redis.conf << 'EOF'
# Network
bind 127.0.0.1
port 6379
timeout 0
tcp-keepalive 300

# General
daemonize yes
supervised systemd
pidfile /var/run/redis/redis-server.pid
loglevel notice
logfile /var/log/redis/redis-server.log

# Memory management
maxmemory 256mb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000
stop-writes-on-bgsave-error yes
rdbcompression yes
rdbchecksum yes
dbfilename dump.rdb
dir /var/lib/redis

# Performance
tcp-backlog 511
databases 16
always-show-logo yes

# Slow log
slowlog-log-slower-than 10000
slowlog-max-len 128

# Latency monitoring
latency-monitor-threshold 100
EOF

systemctl restart redis-server
systemctl enable redis-server

# 6. Install PHP 8.2 and extensions
print_step "Installing PHP 8.2 and extensions..."
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.2-fpm php8.2-mysql php8.2-redis php8.2-curl php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath php8.2-intl

# Configure PHP-FPM
cat > /etc/php/8.2/fpm/pool.d/moneroexchange.conf << 'EOF'
[moneroexchange]
user = www-data
group = www-data
listen = /var/run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
php_admin_value[open_basedir] = /var/www/moneroexchange
php_admin_value[upload_max_filesize] = 10M
php_admin_value[post_max_size] = 10M
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 300
php_admin_value[disable_functions] = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source
EOF

systemctl restart php8.2-fpm
systemctl enable php8.2-fpm

# 7. Install Monero
print_step "Installing Monero..."

# Create monero user
groupadd -r monero 2>/dev/null || true
useradd -r -g monero -d /opt/monero -s /bin/false monero 2>/dev/null || true

# Create directories
mkdir -p /opt/monero
mkdir -p /var/lib/monero
mkdir -p /var/log/monero
mkdir -p /etc/monero
mkdir -p /var/lib/monero/wallets

# Download and install Monero
cd /tmp
wget -O monero-linux-x64-v0.18.4.3.tar.bz2 https://downloads.getmonero.org/cli/monero-linux-x64-v0.18.4.3.tar.bz2
tar -xjf monero-linux-x64-v0.18.4.3.tar.bz2

# Install binaries
cp monero-x86_64-linux-gnu-v0.18.4.3/monerod /usr/local/bin/
cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-rpc /usr/local/bin/
cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-cli /usr/local/bin/

chmod +x /usr/local/bin/monerod
chmod +x /usr/local/bin/monero-wallet-rpc
chmod +x /usr/local/bin/monero-wallet-cli

# Create monerod configuration
cat > /etc/monero/monerod.conf << 'EOF'
# Monero daemon configuration
data-dir=/var/lib/monero
log-file=/var/log/monero/monerod.log
rpc-bind-ip=127.0.0.1
rpc-bind-port=18081
rpc-login=monero:Walnutdesk88?
p2p-bind-ip=0.0.0.0
p2p-bind-port=18080
db-sync-mode=fast
out-peers=64
in-peers=1024
tx-proxy=socks5://127.0.0.1:9050
max-txpool-size=1000000
db-read-buffer-size=134217728
db-write-buffer-size=134217728
EOF

# Create systemd services
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
NoNewPrivileges=true
PrivateTmp=true
ProtectSystem=strict
ProtectHome=true
ReadWritePaths=/var/lib/monero /var/log/monero

[Install]
WantedBy=multi-user.target
EOF

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
NoNewPrivileges=true
PrivateTmp=true
ProtectSystem=strict
ProtectHome=true
ReadWritePaths=/var/lib/monero /var/log/monero

[Install]
WantedBy=multi-user.target
EOF

# Set permissions
chown -R monero:monero /opt/monero
chown -R monero:monero /var/lib/monero
chown -R monero:monero /var/log/monero
chown -R monero:monero /etc/monero

# Enable and start services
systemctl daemon-reload
systemctl enable monerod
systemctl enable monero-wallet-rpc
systemctl start monerod

# Wait for monerod to start
sleep 10
systemctl start monero-wallet-rpc

# 8. Create database and user
print_step "Creating database and user..."
mysql -e "CREATE DATABASE IF NOT EXISTS moneroexchange CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'moneroexchange'@'localhost' IDENTIFIED BY 'Walnutdesk88?';"
mysql -e "GRANT ALL PRIVILEGES ON moneroexchange.* TO 'moneroexchange'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# 9. Create application directory
print_step "Setting up application directory..."
mkdir -p /var/www/moneroexchange
chown -R www-data:www-data /var/www/moneroexchange
chmod -R 755 /var/www/moneroexchange

# 10. Final status check
print_step "Checking all services..."

services=("mysql" "nginx" "redis-server" "php8.2-fpm" "monerod" "monero-wallet-rpc")

for service in "${services[@]}"; do
    if systemctl is-active --quiet $service; then
        print_status "✅ $service is running"
    else
        print_error "❌ $service is not running"
        systemctl status $service --no-pager
    fi
done

# 11. Clean up
rm -rf /tmp/monero-linux-x64-v0.18.4.3.tar.bz2
rm -rf /tmp/monero-x86_64-linux-gnu-v0.18.4.3

print_status "🎉 AWS server setup completed!"
print_status ""
print_status "Next steps:"
print_status "1. Copy your Laravel application files to /var/www/moneroexchange"
print_status "2. Run: cd /var/www/moneroexchange && composer install"
print_status "3. Run: php artisan key:generate"
print_status "4. Run: php artisan migrate"
print_status "5. Set proper file permissions: chown -R www-data:www-data /var/www/moneroexchange"
print_status "6. Visit http://127.0.0.1 in your browser"
print_status ""
print_status "Service management:"
print_status "  Check all services: sudo systemctl status mysql nginx redis-server php8.2-fpm monerod monero-wallet-rpc"
print_status "  Restart all services: sudo systemctl restart mysql nginx redis-server php8.2-fpm monerod monero-wallet-rpc"
print_status "  View logs: sudo journalctl -u <service-name> -f"
