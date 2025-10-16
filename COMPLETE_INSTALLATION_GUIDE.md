# Complete Monero Exchange Installation Guide
## AWS Ubuntu Server - Step by Step

This guide will take you from a fresh AWS Ubuntu server to a fully working Monero Exchange application.

---

## 📋 Prerequisites

- AWS Ubuntu 20.04/22.04 server
- Root access or sudo privileges
- At least 2GB RAM
- At least 20GB disk space
- Internet connection

---

## 🚀 Step 1: Initial Server Setup

### 1.1 Connect to your server
```bash
ssh -i your-key.pem ubuntu@your-server-ip
```

### 1.2 Update the system
```bash
sudo apt update && sudo apt upgrade -y
```

### 1.3 Install essential packages
```bash
sudo apt install -y wget curl unzip software-properties-common apt-transport-https ca-certificates gnupg lsb-release
```

---

## 🗄️ Step 2: Install and Configure MySQL

### 2.1 Install MySQL
```bash
sudo apt install -y mysql-server mysql-client
```

### 2.2 Create MySQL configuration
```bash
sudo tee /etc/mysql/mysql.conf.d/mysqld.cnf > /dev/null << 'EOF'
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
query_cache_type = 1
query_cache_size = 32M
query_cache_limit = 2M

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
```

### 2.3 Fix MySQL permissions and start
```bash
sudo chown -R mysql:mysql /var/lib/mysql
sudo chmod -R 755 /var/lib/mysql
sudo systemctl start mysql
sudo systemctl enable mysql
```

### 2.4 Create database and user
```bash
sudo mysql -e "CREATE DATABASE IF NOT EXISTS moneroexchange CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'moneroexchange'@'localhost' IDENTIFIED BY 'Walnutdesk88?';"
sudo mysql -e "GRANT ALL PRIVILEGES ON moneroexchange.* TO 'moneroexchange'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
```

### 2.5 Test MySQL connection
```bash
mysql -u moneroexchange -p'Walnutdesk88?' -h 127.0.0.1 moneroexchange -e "SELECT 1;"
```

---

## 🌐 Step 3: Install and Configure Nginx

### 3.1 Install Nginx
```bash
sudo apt install -y nginx
```

### 3.2 Create rate limiting configuration
```bash
sudo mkdir -p /etc/nginx/conf.d
sudo tee /etc/nginx/conf.d/ratelimit.conf > /dev/null << 'EOF'
# Rate limiting zones (must be in http context)
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/m;
limit_req_zone $binary_remote_addr zone=general:10m rate=30r/m;

# Connection limiting
limit_conn_zone $binary_remote_addr zone=conn_limit_per_ip:10m;
limit_conn conn_limit_per_ip 20;
EOF
```

### 3.3 Add rate limiting to main nginx.conf
```bash
sudo sed -i '/http {/a\\tinclude /etc/nginx/conf.d/ratelimit.conf;' /etc/nginx/nginx.conf
```

### 3.4 Create site configuration
```bash
sudo tee /etc/nginx/sites-available/moneroexchange > /dev/null << 'EOF'
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
```

### 3.5 Enable site and start Nginx
```bash
sudo ln -sf /etc/nginx/sites-available/moneroexchange /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl start nginx
sudo systemctl enable nginx
```

---

## 🔴 Step 4: Install and Configure Redis

### 4.1 Install Redis
```bash
sudo apt install -y redis-server
```

### 4.2 Configure Redis
```bash
sudo tee /etc/redis/redis.conf > /dev/null << 'EOF'
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
```

### 4.3 Start Redis
```bash
sudo systemctl restart redis-server
sudo systemctl enable redis-server
```

### 4.4 Test Redis
```bash
redis-cli ping
```

---

## 🐘 Step 5: Install PHP 8.2 and Extensions

### 5.1 Add PHP repository
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
```

### 5.2 Install PHP 8.2 and extensions
```bash
sudo apt install -y php8.2-fpm php8.2-mysql php8.2-redis php8.2-curl php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath php8.2-intl
```

### 5.3 Configure PHP-FPM
```bash
sudo tee /etc/php/8.2/fpm/pool.d/moneroexchange.conf > /dev/null << 'EOF'
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
```

### 5.4 Start PHP-FPM
```bash
sudo systemctl restart php8.2-fpm
sudo systemctl enable php8.2-fpm
```

---

## 💰 Step 6: Install Monero

### 6.1 Create monero user and directories
```bash
sudo groupadd -r monero 2>/dev/null || true
sudo useradd -r -g monero -d /opt/monero -s /bin/false monero 2>/dev/null || true
sudo mkdir -p /opt/monero /var/lib/monero /var/log/monero /etc/monero /var/lib/monero/wallets
```

### 6.2 Download and install Monero
```bash
cd /tmp
wget -O monero-linux-x64-v0.18.4.3.tar.bz2 https://downloads.getmonero.org/cli/monero-linux-x64-v0.18.4.3.tar.bz2
tar -xjf monero-linux-x64-v0.18.4.3.tar.bz2
sudo cp monero-x86_64-linux-gnu-v0.18.4.3/monerod /usr/local/bin/
sudo cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-rpc /usr/local/bin/
sudo cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-cli /usr/local/bin/
sudo chmod +x /usr/local/bin/monerod /usr/local/bin/monero-wallet-rpc /usr/local/bin/monero-wallet-cli
```

### 6.3 Create Monero configuration
```bash
sudo tee /etc/monero/monerod.conf > /dev/null << 'EOF'
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
```

### 6.4 Create systemd services
```bash
# Monerod service
sudo tee /etc/systemd/system/monerod.service > /dev/null << 'EOF'
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

# Monero wallet RPC service
sudo tee /etc/systemd/system/monero-wallet-rpc.service > /dev/null << 'EOF'
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
```

### 6.5 Set permissions and start Monero services
```bash
sudo chown -R monero:monero /opt/monero /var/lib/monero /var/log/monero /etc/monero
sudo systemctl daemon-reload
sudo systemctl enable monerod
sudo systemctl enable monero-wallet-rpc
sudo systemctl start monerod
sleep 10
sudo systemctl start monero-wallet-rpc
```

---

## 📁 Step 7: Install Composer

### 7.1 Download and install Composer
```bash
cd /tmp
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

---

## 🚀 Step 8: Deploy Laravel Application

### 8.1 Create application directory
```bash
sudo mkdir -p /var/www/moneroexchange
sudo chown -R www-data:www-data /var/www/moneroexchange
```

### 8.2 Clone the repository
```bash
cd /var/www
sudo git clone https://github.com/NoctraNetwork/moneroexchange.git moneroexchange
sudo chown -R www-data:www-data /var/www/moneroexchange
```

### 8.3 Install dependencies
```bash
cd /var/www/moneroexchange
sudo -u www-data composer install --no-dev --optimize-autoloader
```

### 8.4 Create environment file
```bash
sudo cp .env.example .env
```

### 8.5 Configure environment file
```bash
sudo tee .env > /dev/null << 'EOF'
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

# Monero Configuration
MONERO_RPC_URL=http://127.0.0.1:18081
MONERO_RPC_USERNAME=monero
MONERO_RPC_PASSWORD=Walnutdesk88?
MONERO_WALLET_RPC_URL=http://127.0.0.1:18082
MONERO_WALLET_RPC_USERNAME=monero
MONERO_WALLET_RPC_PASSWORD=Walnutdesk88?

# Tor/Onion Configuration
TOR_ONION_HOST=
TOR_ONION_PORT=80
TOR_ONLY_MODE=false
TOR_BROWSER_DETECTION=true
LOG_TOR_REQUESTS=true
LOG_IP_HASHES=true
ANONYMIZE_LOGS=true

# Security Configuration
HSTS_ENABLE=false
ENHANCED_RATE_LIMITING=true
ADVANCED_CACHING=true
ENHANCED_SESSION_SECURITY=true
ADVANCED_VALIDATION=true
ENHANCED_LOGGING=true
EOF
```

### 8.6 Generate application key
```bash
sudo -u www-data php artisan key:generate
```

### 8.7 Run migrations
```bash
sudo -u www-data php artisan migrate --force
```

### 8.8 Set proper permissions
```bash
sudo chown -R www-data:www-data /var/www/moneroexchange
sudo chmod -R 755 /var/www/moneroexchange
sudo chmod -R 775 /var/www/moneroexchange/storage
sudo chmod -R 775 /var/www/moneroexchange/bootstrap/cache
```

---

## ✅ Step 9: Verify Installation

### 9.1 Check all services
```bash
sudo systemctl status mysql nginx redis-server php8.2-fpm monerod monero-wallet-rpc
```

### 9.2 Test web interface
```bash
curl -I http://127.0.0.1
```

### 9.3 Test database connection
```bash
mysql -u moneroexchange -p'Walnutdesk88?' moneroexchange -e "SELECT 1;"
```

### 9.4 Test Redis connection
```bash
redis-cli ping
```

### 9.5 Test Monero RPC
```bash
curl -u monero:Walnutdesk88? http://127.0.0.1:18081/json_rpc -d '{"jsonrpc":"2.0","id":"0","method":"get_info"}'
```

---

## 🔧 Step 10: Optional - Create Verification Script

### 10.1 Create verification script
```bash
sudo tee /usr/local/bin/verify-monero-exchange > /dev/null << 'EOF'
#!/bin/bash

echo "🔍 Verifying Monero Exchange installation..."

# Check services
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

# Test connections
echo "Testing connections..."

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

sudo chmod +x /usr/local/bin/verify-monero-exchange
```

### 10.2 Run verification
```bash
verify-monero-exchange
```

---

## 🎉 Installation Complete!

Your Monero Exchange is now installed and running. Here's what you have:

### **Services Running:**
- ✅ MySQL (Database)
- ✅ Nginx (Web server)
- ✅ Redis (Cache)
- ✅ PHP 8.2-FPM (PHP processor)
- ✅ Monerod (Monero daemon)
- ✅ Monero Wallet RPC (Wallet service)

### **Access Points:**
- **Web Interface:** http://127.0.0.1
- **Monero RPC:** http://127.0.0.1:18081
- **Wallet RPC:** http://127.0.0.1:18082

### **Credentials:**
- **Database:** moneroexchange / Walnutdesk88?
- **Monero RPC:** monero / Walnutdesk88?

### **Useful Commands:**
```bash
# Check all services
sudo systemctl status mysql nginx redis-server php8.2-fpm monerod monero-wallet-rpc

# Restart all services
sudo systemctl restart mysql nginx redis-server php8.2-fpm monerod monero-wallet-rpc

# View logs
sudo journalctl -u <service-name> -f

# Run verification
verify-monero-exchange
```

### **Next Steps:**
1. Visit http://127.0.0.1 in your browser
2. Create your first user account
3. Configure your Monero wallet
4. Set up your exchange settings

**Your Monero Exchange is ready to use!** 🚀
