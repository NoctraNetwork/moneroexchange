#!/bin/bash

# Complete Monero Exchange Installation Script
# This script installs everything from start to finish

set -e  # Exit on any error

echo "🚀 Starting Complete Monero Exchange Installation..."

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

# Step 1: Update system
print_info "Step 1: Updating system..."
apt update && apt upgrade -y
apt install -y wget curl unzip software-properties-common apt-transport-https ca-certificates gnupg lsb-release
print_status "System updated"

# Step 2: Install MySQL
print_info "Step 2: Installing MySQL..."
apt install -y mysql-server mysql-client

# Configure MySQL
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
bind-address = 127.0.0.1
local-infile = 0
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
default-storage-engine = InnoDB
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
innodb_flush_log_at_trx_commit = 2
innodb_lock_wait_timeout = 50
max_connections = 100
max_connect_errors = 10
connect_timeout = 10
wait_timeout = 28800
interactive_timeout = 28800
# Query cache removed (deprecated in MySQL 8.0+)
log-error = /var/log/mysql/error.log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
log-bin = mysql-bin
binlog_format = ROW
expire_logs_days = 7
skip-ssl
[mysql]
default-character-set = utf8mb4
[mysqldump]
default-character-set = utf8mb4
EOF

chown -R mysql:mysql /var/lib/mysql
chmod -R 755 /var/lib/mysql
systemctl start mysql
systemctl enable mysql

# Create database and user
mysql -e "CREATE DATABASE IF NOT EXISTS moneroexchange CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'moneroexchange'@'localhost' IDENTIFIED BY 'Walnutdesk88?';"
mysql -e "GRANT ALL PRIVILEGES ON moneroexchange.* TO 'moneroexchange'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
print_status "MySQL installed and configured"

# Step 3: Install Nginx
print_info "Step 3: Installing Nginx..."
apt install -y nginx

# Create rate limiting config
mkdir -p /etc/nginx/conf.d
cat > /etc/nginx/conf.d/ratelimit.conf << 'EOF'
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/m;
limit_req_zone $binary_remote_addr zone=general:10m rate=30r/m;
limit_conn_zone $binary_remote_addr zone=conn_limit_per_ip:10m;
limit_conn conn_limit_per_ip 20;
EOF

# Add to main nginx.conf
sed -i '/http {/a\\tinclude /etc/nginx/conf.d/ratelimit.conf;' /etc/nginx/nginx.conf

# Create site config
cat > /etc/nginx/sites-available/moneroexchange << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name 127.0.0.1 localhost;
    
    root /var/www/moneroexchange/public;
    index index.php index.html;
    
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'none'; script-src 'none'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'; upgrade-insecure-requests; block-all-mixed-content;" always;
    
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
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
    }
    
    location ~ /\. {
        deny all;
    }
    
    location ~ /(storage|bootstrap/cache) {
        deny all;
    }
    
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    access_log /var/log/nginx/moneroexchange_access.log;
    error_log /var/log/nginx/moneroexchange_error.log;
}
EOF

ln -sf /etc/nginx/sites-available/moneroexchange /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl start nginx
systemctl enable nginx
print_status "Nginx installed and configured"

# Step 4: Install Redis
print_info "Step 4: Installing Redis..."
apt install -y redis-server

cat > /etc/redis/redis.conf << 'EOF'
bind 127.0.0.1
port 6379
timeout 0
tcp-keepalive 300
daemonize yes
supervised systemd
pidfile /var/run/redis/redis-server.pid
loglevel notice
logfile /var/log/redis/redis-server.log
maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
stop-writes-on-bgsave-error yes
rdbcompression yes
rdbchecksum yes
dbfilename dump.rdb
dir /var/lib/redis
tcp-backlog 511
databases 16
always-show-logo yes
slowlog-log-slower-than 10000
slowlog-max-len 128
latency-monitor-threshold 100
EOF

systemctl restart redis-server
systemctl enable redis-server
print_status "Redis installed and configured"

# Step 5: Install PHP 8.2
print_info "Step 5: Installing PHP 8.2..."
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.2-fpm php8.2-mysql php8.2-redis php8.2-curl php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath php8.2-intl

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
print_status "PHP 8.2 installed and configured"

# Step 6: Install Monero
print_info "Step 6: Installing Monero..."
groupadd -r monero 2>/dev/null || true
useradd -r -g monero -d /opt/monero -s /bin/false monero 2>/dev/null || true
mkdir -p /opt/monero /var/lib/monero /var/log/monero /etc/monero /var/lib/monero/wallets

cd /tmp
wget -O monero-linux-x64-v0.18.4.3.tar.bz2 https://downloads.getmonero.org/cli/monero-linux-x64-v0.18.4.3.tar.bz2
if [ $? -ne 0 ]; then
    print_error "Failed to download Monero"
    exit 1
fi
tar -xjf monero-linux-x64-v0.18.4.3.tar.bz2
if [ $? -ne 0 ]; then
    print_error "Failed to extract Monero"
    exit 1
fi
cp monero-x86_64-linux-gnu-v0.18.4.3/monerod /usr/local/bin/
cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-rpc /usr/local/bin/
cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-cli /usr/local/bin/
chmod +x /usr/local/bin/monerod /usr/local/bin/monero-wallet-rpc /usr/local/bin/monero-wallet-cli

cat > /etc/monero/monerod.conf << 'EOF'
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

chown -R monero:monero /opt/monero /var/lib/monero /var/log/monero /etc/monero
systemctl daemon-reload
systemctl enable monerod
systemctl enable monero-wallet-rpc
systemctl start monerod
sleep 10
systemctl start monero-wallet-rpc
print_status "Monero installed and configured"

# Step 7: Install Composer
print_info "Step 7: Installing Composer..."
cd /tmp
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
print_status "Composer installed"

# Step 8: Deploy Laravel Application
print_info "Step 8: Deploying Laravel Application..."
mkdir -p /var/www/moneroexchange
chown -R www-data:www-data /var/www/moneroexchange

# Clone repository
cd /var/www
git clone https://github.com/NoctraNetwork/moneroexchange.git moneroexchange
if [ $? -ne 0 ]; then
    print_error "Failed to clone repository"
    exit 1
fi
chown -R www-data:www-data /var/www/moneroexchange

# Install dependencies
cd /var/www/moneroexchange
sudo -u www-data composer install --no-dev --optimize-autoloader
if [ $? -ne 0 ]; then
    print_error "Failed to install Composer dependencies"
    exit 1
fi

# Create .env file
cat > .env << 'EOF'
APP_NAME="Monero Exchange"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://127.0.0.1

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=moneroexchange
DB_USERNAME=moneroexchange
DB_PASSWORD=Walnutdesk88?

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

MONERO_RPC_URL=http://127.0.0.1:18081
MONERO_RPC_USERNAME=monero
MONERO_RPC_PASSWORD=Walnutdesk88?
MONERO_WALLET_RPC_URL=http://127.0.0.1:18082
MONERO_WALLET_RPC_USERNAME=monero
MONERO_WALLET_RPC_PASSWORD=Walnutdesk88?

TOR_ONION_HOST=
TOR_ONION_PORT=80
TOR_ONLY_MODE=false
TOR_BROWSER_DETECTION=true
LOG_TOR_REQUESTS=true
LOG_IP_HASHES=true
ANONYMIZE_LOGS=true

HSTS_ENABLE=false
ENHANCED_RATE_LIMITING=true
ADVANCED_CACHING=true
ENHANCED_SESSION_SECURITY=true
ADVANCED_VALIDATION=true
ENHANCED_LOGGING=true
EOF

# Generate key and run migrations
sudo -u www-data php artisan key:generate
if [ $? -ne 0 ]; then
    print_error "Failed to generate application key"
    exit 1
fi
sudo -u www-data php artisan migrate --force
if [ $? -ne 0 ]; then
    print_error "Failed to run database migrations"
    exit 1
fi

# Set permissions
chown -R www-data:www-data /var/www/moneroexchange
chmod -R 755 /var/www/moneroexchange
chmod -R 775 /var/www/moneroexchange/storage
chmod -R 775 /var/www/moneroexchange/bootstrap/cache
print_status "Laravel application deployed"

# Step 9: Create verification script
print_info "Step 9: Creating verification script..."
cat > /usr/local/bin/verify-monero-exchange << 'EOF'
#!/bin/bash

echo "🔍 Verifying Monero Exchange installation..."

services=("mysql" "nginx" "redis-server" "php8.2-fpm" "monerod" "monero-wallet-rpc")
running=0

for service in "${services[@]}"; do
    if systemctl is-active --quiet $service; then
        echo "✅ $service is running"
        ((running++))
    else
        echo "❌ $service is not running"
    fi
done

echo "Services running: $running/${#services[@]}"

if curl -s -I http://127.0.0.1 | grep -q "200 OK\|404 Not Found"; then
    echo "✅ Web interface responding"
else
    echo "❌ Web interface not responding"
fi

if mysql -u moneroexchange -p'Walnutdesk88?' -h 127.0.0.1 moneroexchange -e "SELECT 1;" >/dev/null 2>&1; then
    echo "✅ Database connection working"
else
    echo "❌ Database connection failed"
fi

if redis-cli ping | grep -q PONG; then
    echo "✅ Redis connection working"
else
    echo "❌ Redis connection failed"
fi

if curl -s -u monero:Walnutdesk88? http://127.0.0.1:18081/json_rpc -d '{"jsonrpc":"2.0","id":"0","method":"get_info"}' | grep -q "result"; then
    echo "✅ Monero RPC working"
else
    echo "❌ Monero RPC not responding"
fi

echo "Verification complete!"
EOF

chmod +x /usr/local/bin/verify-monero-exchange

# Step 10: Final verification
print_info "Step 10: Running final verification..."
verify-monero-exchange

# Clean up
rm -rf /tmp/monero-linux-x64-v0.18.4.3.tar.bz2
rm -rf /tmp/monero-x86_64-linux-gnu-v0.18.4.3

echo ""
echo "🎉 INSTALLATION COMPLETE!"
echo ""
echo "Your Monero Exchange is now running at: http://127.0.0.1"
echo ""
echo "Useful commands:"
echo "  Check status: sudo systemctl status mysql nginx redis-server php8.2-fpm monerod monero-wallet-rpc"
echo "  Restart all: sudo systemctl restart mysql nginx redis-server php8.2-fpm monerod monero-wallet-rpc"
echo "  Verify setup: verify-monero-exchange"
echo "  View logs: sudo journalctl -u <service-name> -f"
echo ""
echo "Database credentials: moneroexchange / Walnutdesk88?"
echo "Monero RPC credentials: monero / Walnutdesk88?"
echo ""
echo "Next steps:"
echo "1. Visit http://127.0.0.1 in your browser"
echo "2. Create your first user account"
echo "3. Configure your Monero wallet"
echo "4. Set up your exchange settings"
echo ""
echo "Installation completed successfully! 🚀"
