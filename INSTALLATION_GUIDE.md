# Monero Exchange - Complete Installation Guide for Ubuntu 22.04

This guide will walk you through the complete installation of Monero Exchange on Ubuntu 22.04 LTS, including all dependencies, database setup, Monero integration, and security configuration.

## Quick Start (TL;DR)

If you just want to get Monero Exchange running quickly:

```bash
# 1. Update system and create user
sudo apt update && sudo apt upgrade -y
sudo adduser moneroexchange
sudo usermod -aG sudo moneroexchange
su - moneroexchange

# 2. Install PHP first (this fixes the common "php not found" error)
sudo apt install -y php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-curl php8.1-mbstring php8.1-zip php8.1-bcmath php8.1-gd php8.1-redis php8.1-intl php8.1-soap

# 3. Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# 4. Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# 5. Install MySQL and Redis
sudo apt install -y mysql-server redis-server nginx

# 6. Continue with full installation guide below...
```

**⚠️ Important:** Always install PHP first before Composer to avoid the "Command 'php' not found" error!

## Table of Contents

1. [System Requirements](#system-requirements)
2. [Initial Server Setup](#initial-server-setup)
3. [Install System Dependencies](#install-system-dependencies)
4. [Install and Configure Database](#install-and-configure-database)
5. [Install and Configure Redis](#install-and-configure-redis)
6. [Install and Configure Nginx](#install-and-configure-nginx)
7. [Install and Configure PHP](#install-and-configure-php)
8. [Install and Configure Monero](#install-and-configure-monero)
9. [Install and Configure Monero Exchange](#install-and-configure-monero-exchange)
10. [Security Configuration](#security-configuration)
11. [SSL Certificate Setup](#ssl-certificate-setup)
12. [Final Configuration](#final-configuration)
13. [Testing and Verification](#testing-and-verification)
14. [Maintenance and Monitoring](#maintenance-and-monitoring)
15. [Troubleshooting](#troubleshooting)

## System Requirements

### Minimum Requirements
- **OS**: Ubuntu 22.04 LTS (64-bit)
- **RAM**: 4GB minimum, 8GB recommended
- **Storage**: 100GB minimum, 500GB recommended
- **CPU**: 2 cores minimum, 4 cores recommended
- **Network**: Stable internet connection

### Recommended Production Setup
- **OS**: Ubuntu 22.04 LTS (64-bit)
- **RAM**: 16GB or more
- **Storage**: 1TB SSD
- **CPU**: 8 cores or more
- **Network**: Dedicated IP with good bandwidth

## Initial Server Setup

### 1. Update System Packages

```bash
# Update package lists
sudo apt update && sudo apt upgrade -y

# Install essential packages
sudo apt install -y curl wget git unzip software-properties-common apt-transport-https ca-certificates gnupg lsb-release
```

### 2. Create Monero Exchange User

```bash
# Create dedicated user for Monero Exchange
sudo adduser moneroexchange
sudo usermod -aG sudo moneroexchange

# Switch to moneroexchange user
su - moneroexchange
```

### 3. Configure Firewall

```bash
# Enable UFW firewall
sudo ufw enable

# Allow SSH (adjust port if needed)
sudo ufw allow ssh

# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow Monero RPC ports (internal only)
sudo ufw allow from 127.0.0.1 to any port 18081
sudo ufw allow from 127.0.0.1 to any port 18083

# Check firewall status
sudo ufw status
```

## Install System Dependencies

### 1. Install PHP First

```bash
# Install PHP 8.1 and essential extensions
sudo apt install -y php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-curl php8.1-mbstring php8.1-zip php8.1-bcmath php8.1-gd php8.1-redis php8.1-intl php8.1-soap

# Install additional PHP extensions
sudo apt install -y php8.1-dev php8.1-xmlrpc php8.1-common php8.1-opcache php8.1-readline

# Verify PHP installation
php --version
```

### 2. Install Composer

```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php

# Move Composer to global location
sudo mv composer.phar /usr/local/bin/composer

# Make Composer executable
sudo chmod +x /usr/local/bin/composer

# Verify Composer installation
composer --version
```

### 3. Install Node.js

```bash
# Install Node.js 18.x
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Verify Node.js installation
node --version
npm --version
```

### 4. Install Monero Dependencies

```bash
# Install essential development tools
sudo apt install -y build-essential cmake pkg-config libssl-dev libzmq3-dev libunbound-dev libsodium-dev libunwind8-dev liblzma-dev libreadline6-dev libldns-dev libexpat1-dev libgtest-dev libhidapi-dev libusb-1.0-0-dev libudev-dev libprotobuf-dev protobuf-compiler

# Install additional dependencies
sudo apt install -y libboost-all-dev libevent-dev libminiupnpc-dev libnatpmp-dev libdb++-dev libdb-dev libdb5.3++-dev libdb5.3-dev
```

## Install and Configure Database

### 1. Install MySQL 8.0

```bash
# Install MySQL server
sudo apt install -y mysql-server mysql-client

# Secure MySQL installation
sudo mysql_secure_installation
```

### 2. Configure MySQL for Monero Exchange

```bash
# Login to MySQL as root
sudo mysql -u root -p

# Create database and user
CREATE DATABASE moneroexchange CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'moneroexchange'@'localhost' IDENTIFIED BY 'Walnutdesk88?';
GRANT ALL PRIVILEGES ON moneroexchange.* TO 'moneroexchange'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Configure MySQL Settings

```bash
# Edit MySQL configuration
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Add/modify these settings:

```ini
[mysqld]
# Basic settings
bind-address = 127.0.0.1
port = 3306
socket = /var/run/mysqld/mysqld.sock

# Character set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# InnoDB settings
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Query cache
query_cache_type = 1
query_cache_size = 64M

# Connection settings
max_connections = 200
max_connect_errors = 1000

# Timeout settings
wait_timeout = 600
interactive_timeout = 600

# Logging
log-error = /var/log/mysql/error.log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Security
local_infile = 0
```

```bash
# Restart MySQL
sudo systemctl restart mysql
sudo systemctl enable mysql
```

## Install and Configure Redis

### 1. Install Redis

```bash
# Install Redis
sudo apt install -y redis-server

# Configure Redis
sudo nano /etc/redis/redis.conf
```

Modify these settings:

```ini
# Network
bind 127.0.0.1
port 6379
timeout 300

# Memory management
maxmemory 256mb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Security
requirepass your_redis_password_here
```

```bash
# Restart Redis
sudo systemctl restart redis-server
sudo systemctl enable redis-server
```

## Install and Configure Nginx

### 1. Install Nginx

```bash
# Install Nginx
sudo apt install -y nginx

# Start and enable Nginx
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 2. Configure Nginx for Monero Exchange

```bash
# Create Nginx configuration for Monero Exchange
sudo nano /etc/nginx/sites-available/moneroexchange
```

Add this configuration:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/moneroexchange/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;
    
    # Content Security Policy (NO JAVASCRIPT)
    add_header Content-Security-Policy "default-src 'none'; script-src 'none'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'; upgrade-insecure-requests; block-all-mixed-content;" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # Main location block
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
    }

    # Static files
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Security - deny access to sensitive files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    location ~ /(\.env|\.git|composer\.(json|lock)|package\.(json|lock)|yarn\.lock|webpack\.mix\.js)$ {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Deny access to storage and vendor directories
    location ~ ^/(storage|vendor)/ {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Rate limiting
    limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/m;
    
    location ~ ^/(login|register) {
        limit_req zone=login burst=3 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ ^/api/ {
        limit_req zone=api burst=5 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

### 3. Enable the Site

```bash
# Enable the site
sudo ln -s /etc/nginx/sites-available/moneroexchange /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

## Install and Configure PHP

### 1. Configure PHP-FPM

```bash
# PHP 8.1 and extensions are already installed from previous step
# Now configure PHP-FPM

# Edit PHP-FPM configuration
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
```

Modify these settings:

```ini
[www]
user = www-data
group = www-data
listen = /var/run/php/php8.1-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

; Security
php_admin_value[disable_functions] = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source
php_admin_value[open_basedir] = /var/www/noctra
```

### 3. Configure PHP Settings

```bash
# Edit PHP configuration
sudo nano /etc/php/8.1/fpm/php.ini
```

Modify these settings:

```ini
; Basic settings
max_execution_time = 300
max_input_time = 300
memory_limit = 512M
post_max_size = 100M
upload_max_filesize = 100M
max_file_uploads = 20

; Error reporting
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

; Session settings
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
session.cookie_samesite = "Strict"

; Security
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off

; OPcache
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1
```

```bash
# Create log directory
sudo mkdir -p /var/log/php
sudo chown www-data:www-data /var/log/php

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
sudo systemctl enable php8.1-fpm
```

## Install and Configure Monero

### 1. Download and Install Monero

```bash
# Create Monero directory
sudo mkdir -p /opt/monero
cd /opt/monero

# Download Monero (replace with latest version)
wget https://downloads.getmonero.org/cli/linux64
tar -xzf linux64

# Move binaries to system location
sudo mv monero-*/monerod /usr/local/bin/
sudo mv monero-*/monero-wallet-rpc /usr/local/bin/
sudo chmod +x /usr/local/bin/monerod /usr/local/bin/monero-wallet-rpc

# Create Monero data directory
sudo mkdir -p /var/lib/monero
sudo chown moneroexchange:moneroexchange /var/lib/monero
```

### 2. Create Monero Systemd Services

```bash
# Create monerod service
sudo nano /etc/systemd/system/monerod.service
```

Add this configuration:

```ini
[Unit]
Description=Monero Daemon
After=network.target

[Service]
Type=simple
User=moneroexchange
Group=moneroexchange
WorkingDirectory=/var/lib/monero
ExecStart=/usr/local/bin/monerod --data-dir=/var/lib/monero --rpc-bind-ip=127.0.0.1 --rpc-bind-port=18081 --confirm-external-bind --rpc-login=monero_user:monero_password --log-level=1
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

```bash
# Create monero-wallet-rpc service
sudo nano /etc/systemd/system/monero-wallet-rpc.service
```

Add this configuration:

```ini
[Unit]
Description=Monero Wallet RPC
After=network.target monerod.service
Requires=monerod.service

[Service]
Type=simple
User=moneroexchange
Group=moneroexchange
WorkingDirectory=/var/lib/monero
ExecStart=/usr/local/bin/monero-wallet-rpc --daemon-address=127.0.0.1:18081 --rpc-bind-ip=127.0.0.1 --rpc-bind-port=18083 --rpc-login=wallet_user:wallet_password --wallet-file=/var/lib/monero/escrow_wallet --password=escrow_wallet_password --log-level=1
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

### 3. Start Monero Services

```bash
# Reload systemd
sudo systemctl daemon-reload

# Start monerod
sudo systemctl start monerod
sudo systemctl enable monerod

# Wait for blockchain sync (this can take several hours)
# Check sync status
sudo journalctl -u monerod -f
```

### 4. Create Escrow Wallet

```bash
# Create escrow wallet (run as moneroexchange user)
su - moneroexchange
cd /var/lib/monero

# Create wallet
monero-wallet-cli --daemon-address=127.0.0.1:18081 --generate-new-wallet=escrow_wallet --password=escrow_wallet_password

# Note down the seed phrase and store it securely!
# The seed phrase is: [WRITE DOWN THE 25-WORD SEED PHRASE]

# Exit wallet CLI
exit
```

### 5. Start Wallet RPC Service

```bash
# Start wallet RPC service
sudo systemctl start monero-wallet-rpc
sudo systemctl enable monero-wallet-rpc

# Check status
sudo systemctl status monero-wallet-rpc
```

## Install and Configure Monero Exchange

### 1. Clone and Setup Monero Exchange

```bash
# Switch to moneroexchange user
su - moneroexchange

# Create web directory
sudo mkdir -p /var/www
sudo chown moneroexchange:moneroexchange /var/www

# Clone Monero Exchange repository
cd /var/www
git clone <your-repository-url> moneroexchange
cd moneroexchange

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
npm install --production

# Build assets
npm run build
```

### 2. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit environment configuration
nano .env
```

Configure these essential settings:

```env
APP_NAME="Monero Exchange"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

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
SESSION_DRIVER=database
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password_here
REDIS_PORT=6379

# Monero Configuration
MONEROD_URL=http://127.0.0.1:18081
MONERO_WALLET_RPC_URL=http://127.0.0.1:18083
MONERO_WALLET_RPC_USER=wallet_user
MONERO_WALLET_RPC_PASS=wallet_password
MONERO_WALLET_NAME=escrow_wallet
MONERO_WALLET_PASSWORD=escrow_wallet_password
XMR_CONFIRMATIONS=10
XMR_ATOMIC_UNITS=1000000000000

# Security Settings
AUTH_REQUIRE_PIN_ON_LOGIN=true
CSP_ENABLE=true
HSTS_ENABLE=true
SESSION_SECURE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict

# Fees (in basis points, 100 = 1%)
TRADE_FEE_BPS=25
WITHDRAWAL_FEE_BPS=25

# Rate Limiting
RATE_LIMIT_LOGIN=5
RATE_LIMIT_REGISTER=3
RATE_LIMIT_PIN=10
RATE_LIMIT_API=100
RATE_LIMIT_ADMIN=200
RATE_LIMIT_TRADE=20
RATE_LIMIT_OFFER=10
RATE_LIMIT_WITHDRAWAL=5
```

### 3. Generate Application Key

```bash
# Generate application key
php artisan key:generate
```

### 4. Set Proper Permissions

```bash
# Set ownership
sudo chown -R moneroexchange:www-data /var/www/moneroexchange

# Set permissions
sudo chmod -R 755 /var/www/moneroexchange
sudo chmod -R 775 /var/www/moneroexchange/storage
sudo chmod -R 775 /var/www/moneroexchange/bootstrap/cache

# Set proper permissions for web files
sudo chown -R www-data:www-data /var/www/moneroexchange/storage
sudo chown -R www-data:www-data /var/www/moneroexchange/bootstrap/cache
```

### 5. Run Database Migrations

```bash
# Run migrations
php artisan migrate --force

# Seed database (optional)
php artisan db:seed --force
```

### 6. Create Cron Jobs

```bash
# Edit crontab
crontab -e
```

Add these cron jobs:

```bash
# Laravel scheduler (runs every minute)
* * * * * cd /var/www/moneroexchange && php artisan schedule:run >> /dev/null 2>&1

# Monero deposit scanning (every 5 minutes)
*/5 * * * * cd /var/www/moneroexchange && php artisan xmr:scan >> /var/log/moneroexchange-scan.log 2>&1

# Clean up old logs (daily at 2 AM)
0 2 * * * find /var/log -name "*.log" -mtime +30 -delete
```

## Security Configuration

### 1. Install Fail2ban

```bash
# Install Fail2ban
sudo apt install -y fail2ban

# Configure Fail2ban for Monero Exchange
sudo nano /etc/fail2ban/jail.local
```

Add this configuration:

```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[sshd]
enabled = true
port = ssh
logpath = /var/log/auth.log

[nginx-http-auth]
enabled = true
filter = nginx-http-auth
port = http,https
logpath = /var/log/nginx/error.log

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
port = http,https
logpath = /var/log/nginx/error.log
maxretry = 10
```

```bash
# Start Fail2ban
sudo systemctl start fail2ban
sudo systemctl enable fail2ban
```

### 2. Configure SSH Security

```bash
# Edit SSH configuration
sudo nano /etc/ssh/sshd_config
```

Modify these settings:

```bash
# Disable root login
PermitRootLogin no

# Disable password authentication (use key-based auth)
PasswordAuthentication no
PubkeyAuthentication yes

# Change SSH port (optional)
Port 2222

# Disable X11 forwarding
X11Forwarding no

# Limit users
AllowUsers noctra
```

```bash
# Restart SSH
sudo systemctl restart ssh
```

### 3. Install and Configure UFW

```bash
# Configure UFW rules
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow SSH (adjust port if changed)
sudo ufw allow 2222/tcp

# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable UFW
sudo ufw enable
```

## SSL Certificate Setup

### 1. Install Certbot

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Test automatic renewal
sudo certbot renew --dry-run
```

### 2. Update Nginx Configuration for HTTPS

```bash
# Edit Nginx configuration
sudo nano /etc/nginx/sites-available/noctra
```

Update the configuration to include HTTPS:

```nginx
# Redirect HTTP to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

# HTTPS server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/moneroexchange/public;
    index index.php index.html;

    # SSL configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_trusted_certificate /etc/letsencrypt/live/your-domain.com/chain.pem;
    
    # SSL security settings
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # HSTS
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;
    
    # Content Security Policy (NO JAVASCRIPT)
    add_header Content-Security-Policy "default-src 'none'; script-src 'none'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'; upgrade-insecure-requests; block-all-mixed-content;" always;

    # Rest of configuration remains the same...
}
```

## Final Configuration

### 1. Create Log Directories

```bash
# Create log directories
sudo mkdir -p /var/log/noctra
sudo chown noctra:noctra /var/log/noctra

# Create log rotation configuration
sudo nano /etc/logrotate.d/noctra
```

Add this configuration:

```
/var/log/noctra/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 noctra noctra
}
```

### 2. Configure System Monitoring

```bash
# Install monitoring tools
sudo apt install -y htop iotop nethogs

# Create monitoring script
sudo nano /usr/local/bin/noctra-monitor.sh
```

Add this monitoring script:

```bash
#!/bin/bash

# Monero Exchange System Monitor
echo "=== Monero Exchange System Status ==="
echo "Date: $(date)"
echo ""

# Check services
echo "=== Service Status ==="
systemctl is-active nginx mysql redis-server php8.1-fpm monerod monero-wallet-rpc

echo ""
echo "=== Disk Usage ==="
df -h

echo ""
echo "=== Memory Usage ==="
free -h

echo ""
echo "=== Monero Status ==="
curl -s http://127.0.0.1:18081/json_rpc -d '{"jsonrpc":"2.0","id":"0","method":"get_info"}' | jq '.result.synchronized'

echo ""
echo "=== Monero Exchange Logs (last 10 lines) ==="
tail -10 /var/log/noctra/*.log 2>/dev/null || echo "No logs found"
```

```bash
# Make script executable
sudo chmod +x /usr/local/bin/noctra-monitor.sh
```

### 3. Create Backup Script

```bash
# Create backup script
sudo nano /usr/local/bin/noctra-backup.sh
```

Add this backup script:

```bash
#!/bin/bash

# Monero Exchange Backup Script
BACKUP_DIR="/var/backups/noctra"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u noctrared -p'Walnutdesk88?' noctra > $BACKUP_DIR/noctra_db_$DATE.sql

# Application backup
tar -czf $BACKUP_DIR/noctra_app_$DATE.tar.gz -C /var/www noctra

# Monero wallet backup
cp /var/lib/monero/escrow_wallet* $BACKUP_DIR/

# Clean old backups (keep 7 days)
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
find $BACKUP_DIR -name "escrow_wallet*" -mtime +7 -delete

echo "Backup completed: $DATE"
```

```bash
# Make script executable
sudo chmod +x /usr/local/bin/noctra-backup.sh

# Add to crontab for daily backups
echo "0 3 * * * /usr/local/bin/noctra-backup.sh" | sudo crontab -
```

## Testing and Verification

### 1. Test All Services

```bash
# Check service status
sudo systemctl status nginx mysql redis-server php8.1-fpm monerod monero-wallet-rpc

# Test database connection
mysql -u noctrared -p'Walnutdesk88?' -e "SHOW DATABASES;"

# Test Redis connection
redis-cli -a your_redis_password_here ping

# Test Monero daemon
curl -s http://127.0.0.1:18081/json_rpc -d '{"jsonrpc":"2.0","id":"0","method":"get_info"}'

# Test Monero wallet RPC
curl -s http://127.0.0.1:18083/json_rpc -d '{"jsonrpc":"2.0","id":"0","method":"get_balance"}'
```

### 2. Test Monero Exchange Application

```bash
# Test Laravel application
cd /var/www/noctra
php artisan route:list
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Test database migrations
php artisan migrate:status

# Test scheduled tasks
php artisan schedule:list
```

### 3. Test Web Interface

1. **Open your browser** and navigate to `https://your-domain.com`
2. **Check SSL certificate** - should show as secure
3. **Test registration** - create a test user account
4. **Test login** - verify authentication works
5. **Test PIN system** - verify PIN verification works
6. **Check security headers** - use browser dev tools to verify CSP

### 4. Test Monero Integration

```bash
# Test Monero commands
cd /var/www/noctra
php artisan xmr:health
php artisan xmr:scan
```

## Maintenance and Monitoring

### 1. Regular Maintenance Tasks

```bash
# Daily monitoring
/usr/local/bin/noctra-monitor.sh

# Weekly maintenance
cd /var/www/noctra
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart

# Monthly security updates
sudo apt update && sudo apt upgrade -y
```

### 2. Log Monitoring

```bash
# Monitor application logs
tail -f /var/log/noctra/*.log

# Monitor system logs
sudo journalctl -u nginx -f
sudo journalctl -u mysql -f
sudo journalctl -u monerod -f
```

### 3. Performance Monitoring

```bash
# Monitor system resources
htop
iotop
nethogs

# Monitor database performance
mysql -u noctrared -p'Walnutdesk88?' -e "SHOW PROCESSLIST;"
```

## Troubleshooting

### Common Issues

1. **"Command 'php' not found" Error**
   ```bash
   # Install PHP CLI first
   sudo apt install php8.1-cli
   
   # Verify installation
   php --version
   ```

2. **Composer Installation Fails**
   ```bash
   # Make sure PHP is installed first
   sudo apt install php8.1-cli
   
   # Then install Composer
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   sudo chmod +x /usr/local/bin/composer
   
   # Verify Composer
   composer --version
   ```

3. **"composer.phar: No such file or directory" Error**
   ```bash
   # This happens when the curl command fails
   # Make sure PHP is installed first
   sudo apt install php8.1-cli
   
   # Try Composer installation again
   curl -sS https://getcomposer.org/installer | php
   ```

4. **Permission Issues**
   ```bash
   sudo chown -R moneroexchange:www-data /var/www/moneroexchange
   sudo chmod -R 755 /var/www/moneroexchange
   sudo chmod -R 775 /var/www/moneroexchange/storage
   sudo chmod -R 775 /var/www/moneroexchange/bootstrap/cache
   ```

5. **Database Connection Issues**
   ```bash
   # Check MySQL status
   sudo systemctl status mysql
   
   # Check database credentials
   mysql -u moneroexchange -p'Walnutdesk88?' -e "SELECT 1;"
   ```

6. **Node.js Installation Issues**
   ```bash
   # Update package lists first
   sudo apt update
   
   # Install Node.js repository
   curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
   
   # Install Node.js
   sudo apt install -y nodejs
   
   # Verify installation
   node --version
   npm --version
   ```

7. **Monero Sync Issues**
   ```bash
   # Check Monero daemon status
   sudo systemctl status monerod
   
   # Check sync progress
   curl -s http://127.0.0.1:18081/json_rpc -d '{"jsonrpc":"2.0","id":"0","method":"get_info"}'
   ```

8. **Nginx Configuration Issues**
   ```bash
   # Test Nginx configuration
   sudo nginx -t
   
   # Check Nginx error logs
   sudo tail -f /var/log/nginx/error.log
   ```

### Installation Order Checklist

Make sure you follow this exact order:

1. ✅ **Update system packages**
2. ✅ **Create moneroexchange user**
3. ✅ **Install PHP 8.1 and extensions**
4. ✅ **Install Composer**
5. ✅ **Install Node.js**
6. ✅ **Install Monero dependencies**
7. ✅ **Install and configure MySQL**
8. ✅ **Install and configure Redis**
9. ✅ **Install and configure Nginx**
10. ✅ **Download and install Monero**
11. ✅ **Clone and setup Monero Exchange**
12. ✅ **Configure environment**
13. ✅ **Run migrations and setup**

### Quick Fix Commands

If you encounter issues, run these commands in order:

```bash
# Fix PHP issues
sudo apt install php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-curl php8.1-mbstring php8.1-zip php8.1-bcmath php8.1-gd php8.1-redis php8.1-intl php8.1-soap

# Fix Composer issues
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Fix Node.js issues
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Fix permission issues
sudo chown -R moneroexchange:www-data /var/www/moneroexchange
sudo chmod -R 755 /var/www/moneroexchange
sudo chmod -R 775 /var/www/moneroexchange/storage
sudo chmod -R 775 /var/www/moneroexchange/bootstrap/cache
```

## Security Checklist

- [ ] Firewall configured and enabled
- [ ] SSH secured with key-based authentication
- [ ] Fail2ban installed and configured
- [ ] SSL certificate installed and auto-renewal configured
- [ ] All services running with proper user permissions
- [ ] Database secured with strong passwords
- [ ] Monero wallet seed phrase stored securely
- [ ] Regular backups configured
- [ ] Log rotation configured
- [ ] Security headers properly configured
- [ ] Content Security Policy blocking JavaScript
- [ ] Rate limiting configured
- [ ] Regular security updates scheduled

## Final Notes

1. **Store Monero seed phrase securely** - This is critical for wallet recovery
2. **Regular backups** - Database and application files should be backed up daily
3. **Monitor logs** - Check logs regularly for errors or suspicious activity
4. **Keep system updated** - Regular security updates are essential
5. **Test thoroughly** - Verify all functionality before going live
6. **Document everything** - Keep track of all passwords and configurations

Your Monero Exchange should now be fully installed and configured on Ubuntu 22.04! The system is designed to be secure, scalable, and maintainable for production use.
