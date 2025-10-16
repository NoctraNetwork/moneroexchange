# Monero Exchange Server Troubleshooting Guide

## Current Issues and Solutions

### 1. MySQL Service Failed to Start

**Error:** `Job for mysql.service failed because the control process exited with error code`

**Solutions:**

#### Check MySQL Error Log
```bash
sudo journalctl -xeu mysql.service
sudo tail -f /var/log/mysql/error.log
```

#### Common Fixes:

**A. Port 3306 Already in Use**
```bash
# Check what's using port 3306
sudo netstat -tlnp | grep 3306
sudo lsof -i :3306

# Kill the process if needed
sudo kill -9 <PID>
```

**B. MySQL Data Directory Issues**
```bash
# Check ownership
sudo chown -R mysql:mysql /var/lib/mysql
sudo chmod -R 755 /var/lib/mysql

# Check disk space
df -h
```

**C. Reset MySQL Configuration**
```bash
# Backup current config
sudo cp /etc/mysql/mysql.conf.d/mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf.backup

# Use minimal config
sudo tee /etc/mysql/mysql.conf.d/mysqld.cnf > /dev/null << 'EOF'
[mysqld]
user = mysql
pid-file = /var/run/mysqld/mysqld.pid
socket = /var/run/mysqld/mysqld.sock
port = 3306
basedir = /usr
datadir = /var/lib/mysql
tmpdir = /tmp
skip-external-locking
bind-address = 127.0.0.1
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
default-storage-engine = InnoDB
skip-ssl
EOF

# Restart MySQL
sudo systemctl restart mysql
```

### 2. Nginx Configuration Error

**Error:** `"limit_req_zone" directive is not allowed here`

**Solution:**

#### Step 1: Remove limit_req_zone from server block
```bash
# Remove the problematic lines
sudo sed -i '/limit_req_zone/d' /etc/nginx/sites-available/moneroexchange
sudo sed -i '/limit_req_zone/d' /etc/nginx/sites-enabled/moneroexchange
```

#### Step 2: Create proper rate limiting config
```bash
# Create rate limiting config in conf.d
sudo mkdir -p /etc/nginx/conf.d
sudo tee /etc/nginx/conf.d/ratelimit.conf > /dev/null << 'EOF'
# Rate limiting zones (must be in http context)
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/m;
EOF
```

#### Step 3: Include rate limiting in main nginx.conf
```bash
# Add include to http block
sudo sed -i '/http {/a\\tinclude /etc/nginx/conf.d/ratelimit.conf;' /etc/nginx/nginx.conf
```

#### Step 4: Test and restart
```bash
sudo nginx -t
sudo systemctl restart nginx
```

### 3. Complete Working Nginx Configuration

**File: `/etc/nginx/sites-available/moneroexchange`**
```nginx
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
```

### 4. Quick Commands to Run

```bash
# 1. Fix Nginx immediately
sudo sed -i '/limit_req_zone/d' /etc/nginx/sites-available/moneroexchange
sudo sed -i '/limit_req_zone/d' /etc/nginx/sites-enabled/moneroexchange
sudo nginx -t && sudo systemctl restart nginx

# 2. Check MySQL status
sudo systemctl status mysql
sudo journalctl -xeu mysql.service

# 3. Check all services
sudo systemctl status mysql nginx redis-server php8.2-fpm

# 4. Check ports
sudo netstat -tlnp | grep -E ':(80|3306|6379)'

# 5. Check logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/mysql/error.log
```

### 5. Service Management Commands

```bash
# Start services
sudo systemctl start mysql
sudo systemctl start nginx
sudo systemctl start redis-server
sudo systemctl start php8.2-fpm

# Enable services
sudo systemctl enable mysql
sudo systemctl enable nginx
sudo systemctl enable redis-server
sudo systemctl enable php8.2-fpm

# Check status
sudo systemctl status mysql nginx redis-server php8.2-fpm

# Restart services
sudo systemctl restart mysql nginx redis-server php8.2-fpm
```

### 6. Database Setup

```bash
# Create database and user
sudo mysql -e "CREATE DATABASE IF NOT EXISTS moneroexchange CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'moneroexchange'@'localhost' IDENTIFIED BY 'Walnutdesk88?';"
sudo mysql -e "GRANT ALL PRIVILEGES ON moneroexchange.* TO 'moneroexchange'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
```

### 7. File Permissions

```bash
# Set proper permissions
sudo chown -R www-data:www-data /var/www/moneroexchange
sudo chmod -R 755 /var/www/moneroexchange
sudo chmod -R 775 /var/www/moneroexchange/storage
sudo chmod -R 775 /var/www/moneroexchange/bootstrap/cache
```

### 8. Testing the Setup

```bash
# Test Nginx
curl -I http://127.0.0.1

# Test MySQL connection
mysql -u moneroexchange -p'Walnutdesk88?' -h 127.0.0.1 moneroexchange -e "SELECT 1;"

# Test Redis
redis-cli ping
```

## Emergency Recovery

If everything is broken:

```bash
# 1. Stop all services
sudo systemctl stop mysql nginx redis-server php8.2-fpm

# 2. Reset MySQL
sudo rm -rf /var/lib/mysql/*
sudo mysql_install_db --user=mysql --basedir=/usr --datadir=/var/lib/mysql

# 3. Reset Nginx
sudo rm /etc/nginx/sites-enabled/*
sudo cp /etc/nginx/sites-available/default /etc/nginx/sites-enabled/

# 4. Start services one by one
sudo systemctl start mysql
sudo systemctl start nginx
sudo systemctl start redis-server
sudo systemctl start php8.2-fpm
```

## Contact

If you continue to have issues, check:
1. System logs: `journalctl -xe`
2. Service logs: `sudo systemctl status <service>`
3. Application logs: `/var/log/nginx/` and `/var/log/mysql/`
4. Disk space: `df -h`
5. Memory usage: `free -h`
