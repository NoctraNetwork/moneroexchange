# 🚀 POST-INSTALLATION COMMANDS GUIDE

## Complete Commands to Finish Monero Exchange Setup

---

## 📋 **STEP 1: COMPLETE LARAVEL SETUP**

### **1.1 Fix Composer Dependencies**
```bash
# Navigate to application directory
cd /var/www/moneroexchange

# Fix Composer dependencies
sudo -u www-data composer install --no-interaction

# Verify vendor directory exists
ls -la vendor/autoload.php
```

### **1.2 Generate Application Key**
```bash
# Generate Laravel application key
sudo -u www-data php artisan key:generate

# Verify .env file has key
grep "APP_KEY=" .env
```

### **1.3 Run Database Migrations**
```bash
# Run all migrations
sudo -u www-data php artisan migrate --force

# Check migration status
sudo -u www-data php artisan migrate:status
```

### **1.4 Seed Database**
```bash
# Seed the database with initial data
sudo -u www-data php artisan db:seed --force

# Check if data was seeded
mysql -u moneroexchange -p'Walnutdesk88?' -e "USE moneroexchange; SELECT COUNT(*) as users FROM users;"
```

### **1.5 Cache Configuration**
```bash
# Clear and cache all configurations
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache
```

### **1.6 Create Storage Symlink**
```bash
# Create storage symlink
sudo -u www-data php artisan storage:link

# Verify symlink
ls -la public/storage
```

---

## 📋 **STEP 2: SET PROPER PERMISSIONS**

### **2.1 Set Ownership**
```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/moneroexchange

# Verify ownership
ls -la /var/www/moneroexchange
```

### **2.2 Set Directory Permissions**
```bash
# Set directory permissions
sudo find /var/www/moneroexchange -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/moneroexchange -type f -exec chmod 644 {} \;
```

### **2.3 Set Executable Permissions**
```bash
# Set executable permissions for specific files
sudo chmod +x /var/www/moneroexchange/artisan
sudo chmod +x /var/www/moneroexchange/public/index.php
```

### **2.4 Set Writable Permissions**
```bash
# Set writable permissions for Laravel directories
sudo chmod -R 775 /var/www/moneroexchange/storage
sudo chmod -R 775 /var/www/moneroexchange/bootstrap/cache
sudo chmod -R 775 /var/www/moneroexchange/public/uploads
```

### **2.5 Secure .env File**
```bash
# Secure .env file
sudo chmod 600 /var/www/moneroexchange/.env
sudo chown www-data:www-data /var/www/moneroexchange/.env
```

---

## 📋 **STEP 3: COMPLETE MONERO SETUP**

### **3.1 Create Monero User**
```bash
# Create monero user if not exists
sudo useradd -r -s /bin/false monero 2>/dev/null || true
```

### **3.2 Create Monero Directories**
```bash
# Create directories
sudo mkdir -p /var/lib/monero
sudo mkdir -p /var/log/monero
sudo mkdir -p /etc/monero
sudo mkdir -p /opt/monero

# Set permissions
sudo chown -R monero:monero /var/lib/monero
sudo chown -R monero:monero /var/log/monero
sudo chown -R monero:monero /etc/monero
```

### **3.3 Download and Install Monero**
```bash
# Download Monero
cd /tmp
sudo wget -O monero-linux-x64-v0.18.4.3.tar.bz2 https://downloads.getmonero.org/cli/monero-linux-x64-v0.18.4.3.tar.bz2

# Extract Monero
sudo tar -xjf monero-linux-x64-v0.18.4.3.tar.bz2

# Install binaries
sudo cp monero-x86_64-linux-gnu-v0.18.4.3/monerod /usr/local/bin/
sudo cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-rpc /usr/local/bin/
sudo cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-cli /usr/local/bin/

# Set executable permissions
sudo chmod +x /usr/local/bin/monerod
sudo chmod +x /usr/local/bin/monero-wallet-rpc
sudo chmod +x /usr/local/bin/monero-wallet-cli
```

### **3.4 Create Monero Configuration**
```bash
# Create monerod configuration
sudo tee /etc/monero/monerod.conf > /dev/null << 'EOF'
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

### **3.5 Create Systemd Services**
```bash
# Create monerod service
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

# Create monero-wallet-rpc service
sudo tee /etc/systemd/system/monero-wallet-rpc.service > /dev/null << 'EOF'
[Unit]
Description=Monero Wallet RPC
After=network.target monerod.service
Requires=monerod.service

[Service]
Type=simple
User=monero
Group=monero
ExecStart=/usr/local/bin/monero-wallet-rpc --rpc-bind-ip=127.0.0.1 --rpc-bind-port=18082 --rpc-login=monero:Walnutdesk88? --wallet-dir=/var/lib/monero
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal
SyslogIdentifier=monero-wallet-rpc
NoNewPrivileges=true
PrivateTmp=true
ProtectSystem=strict
ProtectHome=true
ReadWritePaths=/var/lib/monero

[Install]
WantedBy=multi-user.target
EOF
```

### **3.6 Start Monero Services**
```bash
# Reload systemd
sudo systemctl daemon-reload

# Start and enable monerod
sudo systemctl start monerod
sudo systemctl enable monerod

# Start and enable monero-wallet-rpc
sudo systemctl start monero-wallet-rpc
sudo systemctl enable monero-wallet-rpc

# Check status
sudo systemctl status monerod
sudo systemctl status monero-wallet-rpc
```

---

## 📋 **STEP 4: VERIFY ALL SERVICES**

### **4.1 Check All Services**
```bash
# Check all services status
sudo systemctl status mysql nginx redis-server php8.2-fpm monerod monero-wallet-rpc

# Check which services are running
sudo systemctl is-active mysql nginx redis-server php8.2-fpm monerod monero-wallet-rpc
```

### **4.2 Test Web Interface**
```bash
# Test web interface
curl -I http://127.0.0.1

# Test specific pages
curl http://127.0.0.1/login
curl http://127.0.0.1/register
curl http://127.0.0.1/offers
```

### **4.3 Test Laravel Application**
```bash
# Test Laravel
cd /var/www/moneroexchange
sudo -u www-data php artisan --version
sudo -u www-data php artisan route:list
sudo -u www-data php artisan tinker --execute="echo 'Laravel working';"
```

### **4.4 Test Database Connection**
```bash
# Test database
sudo -u www-data php artisan tinker --execute="echo 'DB: ' . (DB::connection()->getPdo() ? 'Connected' : 'Failed');"

# Test Redis
sudo -u www-data php artisan tinker --execute="echo 'Redis: ' . (Redis::ping() ? 'Connected' : 'Failed');"
```

---

## 📋 **STEP 5: CLEANUP UNNECESSARY FILES**

### **5.1 Remove Temporary Files**
```bash
# Remove Monero download files
sudo rm -rf /tmp/monero-linux-x64-v0.18.4.3.tar.bz2
sudo rm -rf /tmp/monero-x86_64-linux-gnu-v0.18.4.3

# Remove Composer cache
sudo -u www-data composer clear-cache

# Remove Laravel logs (optional)
sudo rm -f /var/www/moneroexchange/storage/logs/*.log
```

### **5.2 Remove Unused Packages**
```bash
# Remove unused packages
sudo apt autoremove -y
sudo apt autoclean

# Clean package cache
sudo apt clean
```

### **5.3 Remove Installation Scripts**
```bash
# Remove installation scripts from home directory
rm -f ~/install-*.sh
rm -f ~/fix-*.sh
rm -f ~/nuclear-*.sh
rm -f ~/quick-*.sh
```

### **5.4 Clean Up Nginx Logs**
```bash
# Clean old nginx logs
sudo truncate -s 0 /var/log/nginx/access.log
sudo truncate -s 0 /var/log/nginx/error.log
sudo truncate -s 0 /var/log/nginx/moneroexchange_access.log
sudo truncate -s 0 /var/log/nginx/moneroexchange_error.log
```

---

## 📋 **STEP 6: CREATE MAINTENANCE SCRIPTS**

### **6.1 Create Service Management Script**
```bash
# Create service management script
sudo tee /usr/local/bin/monero-exchange-status << 'EOF'
#!/bin/bash
echo "🔍 Monero Exchange Service Status"
echo "================================="

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

# Test web interface
if curl -s -I http://127.0.0.1 | grep -q "200 OK\|404 Not Found"; then
    echo "✅ Web interface responding"
else
    echo "❌ Web interface not responding"
fi
EOF

sudo chmod +x /usr/local/bin/monero-exchange-status
```

### **6.2 Create Backup Script**
```bash
# Create backup script
sudo tee /usr/local/bin/monero-exchange-backup << 'EOF'
#!/bin/bash
BACKUP_DIR="/var/backups/monero-exchange"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u moneroexchange -p'Walnutdesk88?' moneroexchange > $BACKUP_DIR/database_$DATE.sql

# Backup application
tar -czf $BACKUP_DIR/application_$DATE.tar.gz /var/www/moneroexchange

# Backup Monero data
tar -czf $BACKUP_DIR/monero_$DATE.tar.gz /var/lib/monero

echo "Backup completed: $BACKUP_DIR"
EOF

sudo chmod +x /usr/local/bin/monero-exchange-backup
```

---

## 📋 **STEP 7: FINAL VERIFICATION**

### **7.1 Complete System Check**
```bash
# Run complete verification
monero-exchange-status

# Test all Laravel functionality
cd /var/www/moneroexchange
sudo -u www-data php artisan route:list | wc -l
sudo -u www-data php artisan migrate:status
```

### **7.2 Check File Structure**
```bash
# Verify complete file structure
ls -la /var/www/moneroexchange/
ls -la /var/www/moneroexchange/app/
ls -la /var/www/moneroexchange/resources/views/
ls -la /var/www/moneroexchange/public/
```

### **7.3 Test All Pages**
```bash
# Test all main pages
curl -s http://127.0.0.1 | grep -q "Monero Exchange" && echo "✅ Home page working"
curl -s http://127.0.0.1/login | grep -q "Login" && echo "✅ Login page working"
curl -s http://127.0.0.1/register | grep -q "Register" && echo "✅ Register page working"
curl -s http://127.0.0.1/offers | grep -q "Offers" && echo "✅ Offers page working"
```

---

## 🎉 **COMPLETION CHECKLIST**

- [ ] Composer dependencies installed
- [ ] Application key generated
- [ ] Database migrated and seeded
- [ ] Configuration cached
- [ ] Storage symlink created
- [ ] Permissions set correctly
- [ ] Monero installed and configured
- [ ] All services running
- [ ] Web interface working
- [ ] Laravel application functional
- [ ] Unnecessary files cleaned up
- [ ] Maintenance scripts created
- [ ] Final verification passed

---

## 🚀 **QUICK COMMANDS REFERENCE**

```bash
# Check status
monero-exchange-status

# Restart all services
sudo systemctl restart mysql nginx redis-server php8.2-fpm monerod monero-wallet-rpc

# View logs
sudo journalctl -u monerod -f
sudo journalctl -u monero-wallet-rpc -f
sudo tail -f /var/log/nginx/error.log

# Backup
monero-exchange-backup

# Laravel commands
cd /var/www/moneroexchange
sudo -u www-data php artisan [command]
```

**Your Monero Exchange is now fully installed and ready to use!** 🎉
