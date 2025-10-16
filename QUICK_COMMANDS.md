# Quick Commands for AWS Server

## Immediate Fixes

### 1. Fix the Monero download/extraction issue:
```bash
# Download and extract with proper permissions
cd /tmp
sudo wget -O monero-linux-x64-v0.18.4.3.tar.bz2 https://downloads.getmonero.org/cli/monero-linux-x64-v0.18.4.3.tar.bz2
sudo tar -xjf monero-linux-x64-v0.18.4.3.tar.bz2
sudo cp monero-x86_64-linux-gnu-v0.18.4.3/monerod /usr/local/bin/
sudo cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-rpc /usr/local/bin/
sudo cp monero-x86_64-linux-gnu-v0.18.4.3/monero-wallet-cli /usr/local/bin/
sudo chmod +x /usr/local/bin/monerod
sudo chmod +x /usr/local/bin/monero-wallet-rpc
sudo chmod +x /usr/local/bin/monero-wallet-cli
```

### 2. Fix Nginx configuration:
```bash
# Remove limit_req_zone from server block
sudo sed -i '/limit_req_zone/d' /etc/nginx/sites-available/moneroexchange
sudo sed -i '/limit_req_zone/d' /etc/nginx/sites-enabled/moneroexchange

# Create proper rate limiting config
sudo mkdir -p /etc/nginx/conf.d
sudo tee /etc/nginx/conf.d/ratelimit.conf > /dev/null << 'EOF'
# Rate limiting zones (must be in http context)
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/m;
EOF

# Add include to main nginx.conf
sudo sed -i '/http {/a\\tinclude /etc/nginx/conf.d/ratelimit.conf;' /etc/nginx/nginx.conf

# Test and restart
sudo nginx -t
sudo systemctl restart nginx
```

### 3. Fix MySQL:
```bash
# Check MySQL status
sudo systemctl status mysql

# If failed, check logs
sudo journalctl -xeu mysql.service

# Check if port 3306 is in use
sudo netstat -tlnp | grep 3306

# If port is in use, kill the process
sudo lsof -i :3306
sudo kill -9 <PID>

# Restart MySQL
sudo systemctl restart mysql
```

### 4. Create monero user and directories:
```bash
# Create user and directories
sudo groupadd -r monero 2>/dev/null || true
sudo useradd -r -g monero -d /opt/monero -s /bin/false monero 2>/dev/null || true
sudo mkdir -p /opt/monero /var/lib/monero /var/log/monero /etc/monero /var/lib/monero/wallets
sudo chown -R monero:monero /opt/monero /var/lib/monero /var/log/monero /etc/monero
```

### 5. Test all services:
```bash
# Check all services
sudo systemctl status mysql nginx redis-server php8.2-fpm

# Test Nginx
curl -I http://127.0.0.1

# Test MySQL
mysql -u moneroexchange -p'Walnutdesk88?' -h 127.0.0.1 moneroexchange -e "SELECT 1;"

# Test Redis
redis-cli ping
```

## Complete Setup (Run this for full setup):

```bash
# Make the script executable and run it
chmod +x aws-server-setup.sh
sudo ./aws-server-setup.sh
```

## Individual Service Management:

```bash
# Start services
sudo systemctl start mysql nginx redis-server php8.2-fpm

# Stop services
sudo systemctl stop mysql nginx redis-server php8.2-fpm

# Restart services
sudo systemctl restart mysql nginx redis-server php8.2-fpm

# Enable services
sudo systemctl enable mysql nginx redis-server php8.2-fpm

# Check status
sudo systemctl status mysql nginx redis-server php8.2-fpm

# View logs
sudo journalctl -u mysql -f
sudo journalctl -u nginx -f
sudo journalctl -u redis-server -f
sudo journalctl -u php8.2-fpm -f
```

## Troubleshooting Commands:

```bash
# Check disk space
df -h

# Check memory usage
free -h

# Check running processes
ps aux | grep -E "(mysql|nginx|redis|php|monero)"

# Check open ports
sudo netstat -tlnp | grep -E ':(80|3306|6379|18081|18082)'

# Check system logs
sudo journalctl -xe

# Check specific service logs
sudo journalctl -u mysql --no-pager
sudo journalctl -u nginx --no-pager
sudo journalctl -u redis-server --no-pager
```

## File Locations:

- **Nginx config**: `/etc/nginx/sites-available/moneroexchange`
- **Rate limiting**: `/etc/nginx/conf.d/ratelimit.conf`
- **MySQL config**: `/etc/mysql/mysql.conf.d/mysqld.cnf`
- **Redis config**: `/etc/redis/redis.conf`
- **Monero config**: `/etc/monero/monerod.conf`
- **Application**: `/var/www/moneroexchange`
- **Logs**: `/var/log/nginx/`, `/var/log/mysql/`, `/var/log/redis/`, `/var/log/monero/`
