#!/bin/bash

# Monero Exchange Server Setup Script
# Run this script to fix MySQL, Nginx, and Redis configuration issues

echo "🔧 Setting up Monero Exchange server..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
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

print_status "Starting server configuration..."

# 1. Fix MySQL configuration
print_status "Configuring MySQL..."

# Backup original MySQL config
cp /etc/mysql/mysql.conf.d/mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf.backup.$(date +%Y%m%d_%H%M%S)

# Create new MySQL config
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

# Fix MySQL permissions
chown mysql:mysql /var/lib/mysql
chmod 755 /var/lib/mysql

# Start MySQL
print_status "Starting MySQL..."
systemctl start mysql
if [ $? -eq 0 ]; then
    print_status "MySQL started successfully"
    systemctl enable mysql
else
    print_error "Failed to start MySQL. Check logs: journalctl -xeu mysql.service"
    exit 1
fi

# 2. Fix Nginx configuration
print_status "Configuring Nginx..."

# Create rate limiting config
mkdir -p /etc/nginx/conf.d
cat > /etc/nginx/conf.d/ratelimit.conf << 'EOF'
# Rate limiting zones for Monero Exchange
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/m;
limit_req_zone $binary_remote_addr zone=general:10m rate=30r/m;

# Connection limiting
limit_conn_zone $binary_remote_addr zone=conn_limit_per_ip:10m;
limit_conn conn_limit_per_ip 20;
EOF

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

# Update main nginx.conf to include rate limiting
if ! grep -q "include /etc/nginx/conf.d/ratelimit.conf;" /etc/nginx/nginx.conf; then
    sed -i '/http {/a\\tinclude /etc/nginx/conf.d/ratelimit.conf;' /etc/nginx/nginx.conf
fi

# Enable site
ln -sf /etc/nginx/sites-available/moneroexchange /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
print_status "Testing Nginx configuration..."
nginx -t
if [ $? -eq 0 ]; then
    print_status "Nginx configuration is valid"
    systemctl restart nginx
    systemctl enable nginx
else
    print_error "Nginx configuration test failed"
    exit 1
fi

# 3. Configure Redis
print_status "Configuring Redis..."

# Backup original Redis config
cp /etc/redis/redis.conf /etc/redis/redis.conf.backup.$(date +%Y%m%d_%H%M%S)

# Create new Redis config
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

# Start Redis
print_status "Starting Redis..."
systemctl restart redis-server
systemctl enable redis-server

# 4. Create application directory and set permissions
print_status "Setting up application directory..."
mkdir -p /var/www/moneroexchange
chown -R www-data:www-data /var/www/moneroexchange
chmod -R 755 /var/www/moneroexchange

# Create log directories
mkdir -p /var/log/nginx
mkdir -p /var/log/moneroexchange
chown -R www-data:www-data /var/log/moneroexchange

# 5. Install PHP 8.2 and required extensions
print_status "Installing PHP 8.2 and extensions..."
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

# Restart PHP-FPM
systemctl restart php8.2-fpm
systemctl enable php8.2-fpm

# 6. Create database and user
print_status "Creating database and user..."
mysql -e "CREATE DATABASE IF NOT EXISTS moneroexchange CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'moneroexchange'@'localhost' IDENTIFIED BY 'Walnutdesk88?';"
mysql -e "GRANT ALL PRIVILEGES ON moneroexchange.* TO 'moneroexchange'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# 7. Final status check
print_status "Checking service status..."

# Check MySQL
if systemctl is-active --quiet mysql; then
    print_status "✅ MySQL is running"
else
    print_error "❌ MySQL is not running"
fi

# Check Nginx
if systemctl is-active --quiet nginx; then
    print_status "✅ Nginx is running"
else
    print_error "❌ Nginx is not running"
fi

# Check Redis
if systemctl is-active --quiet redis-server; then
    print_status "✅ Redis is running"
else
    print_error "❌ Redis is not running"
fi

# Check PHP-FPM
if systemctl is-active --quiet php8.2-fpm; then
    print_status "✅ PHP-FPM is running"
else
    print_error "❌ PHP-FPM is not running"
fi

print_status "🎉 Server setup completed!"
print_status "Next steps:"
print_status "1. Copy your Laravel application files to /var/www/moneroexchange"
print_status "2. Run: cd /var/www/moneroexchange && composer install"
print_status "3. Run: php artisan key:generate"
print_status "4. Run: php artisan migrate"
print_status "5. Set proper file permissions: chown -R www-data:www-data /var/www/moneroexchange"
print_status "6. Visit http://127.0.0.1 in your browser"
