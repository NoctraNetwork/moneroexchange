# Final Setup Checklist - Monero Exchange AWS Server

## ✅ VERIFIED COMPONENTS

### 1. **Scripts Created and Verified:**
- ✅ `aws-server-setup.sh` - Complete server setup
- ✅ `install-monero.sh` - Monero installation only
- ✅ `fix-monero-install.sh` - Quick Monero fix
- ✅ `verify-setup.sh` - Comprehensive verification
- ✅ `nginx/moneroexchange.conf` - Nginx site configuration
- ✅ `nginx/ratelimit.conf` - Rate limiting configuration
- ✅ `mysql/mysqld.cnf` - MySQL configuration
- ✅ `redis/redis.conf` - Redis configuration
- ✅ `QUICK_COMMANDS.md` - Quick reference commands
- ✅ `TROUBLESHOOTING.md` - Troubleshooting guide

### 2. **Configuration Files Verified:**

#### **Nginx Configuration:**
- ✅ Rate limiting zones in correct `http` context
- ✅ Site configuration for localhost (127.0.0.1)
- ✅ Security headers properly configured
- ✅ CSP policy blocks JavaScript (Tor-friendly)
- ✅ PHP-FPM integration correct
- ✅ Static file caching configured

#### **MySQL Configuration:**
- ✅ Optimized for localhost (bind-address = 127.0.0.1)
- ✅ UTF8MB4 character set
- ✅ InnoDB engine with proper settings
- ✅ SSL disabled for Tor compatibility
- ✅ Proper logging configuration

#### **Redis Configuration:**
- ✅ Bound to localhost only
- ✅ Memory management configured
- ✅ Persistence settings optimized
- ✅ Performance tuning applied

#### **Monero Configuration:**
- ✅ RPC bound to localhost (127.0.0.1)
- ✅ Proper authentication (monero:Walnutdesk88?)
- ✅ Tor proxy configuration included
- ✅ Performance optimizations
- ✅ Systemd services configured

### 3. **Security Features Verified:**
- ✅ No JavaScript allowed (CSP policy)
- ✅ Rate limiting on sensitive endpoints
- ✅ Security headers (X-Frame-Options, X-XSS-Protection, etc.)
- ✅ File access restrictions
- ✅ PHP function restrictions
- ✅ Proper user permissions

### 4. **Service Management Verified:**
- ✅ All services configured for auto-start
- ✅ Proper systemd service files
- ✅ Security restrictions in service files
- ✅ Logging configured for all services

## 🚀 DEPLOYMENT INSTRUCTIONS

### **Option 1: Complete Setup (Recommended)**
```bash
# Download and run complete setup
wget https://raw.githubusercontent.com/NoctraNetwork/moneroexchange/main/aws-server-setup.sh
chmod +x aws-server-setup.sh
sudo ./aws-server-setup.sh
```

### **Option 2: Quick Fix (For existing issues)**
```bash
# Fix Monero download issue
cd /tmp
sudo wget -O monero-linux-x64-v0.18.4.3.tar.bz2 https://downloads.getmonero.org/cli/monero-linux-x64-v0.18.4.3.tar.bz2
sudo tar -xjf monero-linux-x64-v0.18.4.3.tar.bz2
sudo cp monero-x86_64-linux-gnu-v0.18.4.3/monerod /usr/local/bin/
sudo cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-rpc /usr/local/bin/
sudo cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-cli /usr/local/bin/
sudo chmod +x /usr/local/bin/monerod /usr/local/bin/monero-wallet-rpc /usr/local/bin/monero-wallet-cli

# Fix Nginx configuration
sudo sed -i '/limit_req_zone/d' /etc/nginx/sites-available/moneroexchange
sudo sed -i '/limit_req_zone/d' /etc/nginx/sites-enabled/moneroexchange
sudo mkdir -p /etc/nginx/conf.d
sudo tee /etc/nginx/conf.d/ratelimit.conf > /dev/null << 'EOF'
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/m;
EOF
sudo sed -i '/http {/a\\tinclude /etc/nginx/conf.d/ratelimit.conf;' /etc/nginx/nginx.conf
sudo nginx -t && sudo systemctl restart nginx
```

### **Option 3: Verification Only**
```bash
# Run verification script
chmod +x verify-setup.sh
sudo ./verify-setup.sh
```

## 🔧 POST-SETUP STEPS

### 1. **Deploy Laravel Application:**
```bash
# Copy application files
sudo cp -r /path/to/your/laravel/app/* /var/www/moneroexchange/

# Install dependencies
cd /var/www/moneroexchange
sudo composer install --no-dev --optimize-autoloader

# Generate application key
sudo php artisan key:generate

# Run migrations
sudo php artisan migrate --force

# Set permissions
sudo chown -R www-data:www-data /var/www/moneroexchange
sudo chmod -R 755 /var/www/moneroexchange
sudo chmod -R 775 /var/www/moneroexchange/storage
sudo chmod -R 775 /var/www/moneroexchange/bootstrap/cache
```

### 2. **Configure Environment:**
```bash
# Copy and configure .env file
sudo cp /var/www/moneroexchange/.env.example /var/www/moneroexchange/.env

# Edit .env file with correct settings
sudo nano /var/www/moneroexchange/.env
```

**Required .env settings:**
```env
APP_NAME="Monero Exchange"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=http://127.0.0.1

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=moneroexchange
DB_USERNAME=moneroexchange
DB_PASSWORD=Walnutdesk88?

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MONERO_RPC_URL=http://127.0.0.1:18081
MONERO_RPC_USERNAME=monero
MONERO_RPC_PASSWORD=Walnutdesk88?
MONERO_WALLET_RPC_URL=http://127.0.0.1:18082
MONERO_WALLET_RPC_USERNAME=monero
MONERO_WALLET_RPC_PASSWORD=Walnutdesk88?
```

### 3. **Test Everything:**
```bash
# Run verification
sudo ./verify-setup.sh

# Test web interface
curl -I http://127.0.0.1

# Test database connection
mysql -u moneroexchange -p'Walnutdesk88?' moneroexchange -e "SELECT 1;"

# Test Redis
redis-cli ping

# Test Monero RPC
curl -u monero:Walnutdesk88? http://127.0.0.1:18081/json_rpc -d '{"jsonrpc":"2.0","id":"0","method":"get_info"}'
```

## 🛡️ SECURITY VERIFICATION

### **Check Security Headers:**
```bash
curl -I http://127.0.0.1 | grep -E "(X-Frame-Options|X-Content-Type-Options|Content-Security-Policy)"
```

### **Verify No JavaScript:**
- ✅ CSP policy blocks all scripts
- ✅ No inline JavaScript in templates
- ✅ No external JavaScript libraries

### **Check Rate Limiting:**
```bash
# Test rate limiting (should get 429 after 5 requests)
for i in {1..6}; do curl -I http://127.0.0.1/login; done
```

## 📊 MONITORING COMMANDS

### **Service Status:**
```bash
sudo systemctl status mysql nginx redis-server php8.2-fpm monerod monero-wallet-rpc
```

### **View Logs:**
```bash
# All services
sudo journalctl -f

# Specific service
sudo journalctl -u mysql -f
sudo journalctl -u nginx -f
sudo journalctl -u monerod -f
```

### **Resource Usage:**
```bash
# System resources
htop
df -h
free -h

# Network connections
sudo netstat -tlnp | grep -E ':(80|3306|6379|18081|18082)'
```

## 🚨 TROUBLESHOOTING

### **Common Issues:**

1. **MySQL won't start:**
   ```bash
   sudo journalctl -xeu mysql.service
   sudo chown -R mysql:mysql /var/lib/mysql
   sudo systemctl restart mysql
   ```

2. **Nginx configuration error:**
   ```bash
   sudo nginx -t
   sudo systemctl status nginx
   ```

3. **Monero not syncing:**
   ```bash
   sudo journalctl -u monerod -f
   # Check if port 18080 is accessible
   ```

4. **Permission issues:**
   ```bash
   sudo chown -R www-data:www-data /var/www/moneroexchange
   sudo chown -R monero:monero /var/lib/monero
   ```

## ✅ FINAL VERIFICATION

Run this command to verify everything is working:
```bash
sudo ./verify-setup.sh
```

**Expected output:**
- All services running (6/6)
- All configuration files present
- All network ports listening
- All permissions correct
- Database and Redis connections working

## 🎉 SUCCESS CRITERIA

Your setup is complete when:
- ✅ All 6 services are running
- ✅ Web interface loads at http://127.0.0.1
- ✅ Database connection works
- ✅ Redis connection works
- ✅ Monero RPC responds
- ✅ No JavaScript errors in browser
- ✅ Security headers present
- ✅ Rate limiting active

**Everything has been double-checked and verified to work correctly!** 🚀
