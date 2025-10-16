# Monero Exchange Installation Guide

This guide will help you install and configure Monero Exchange on Ubuntu 22.04 LTS.

## Prerequisites

- Ubuntu 22.04 LTS
- Root or sudo access
- At least 4GB RAM
- At least 50GB disk space
- Domain name (optional, for production)

## 1. System Updates

```bash
sudo apt update && sudo apt upgrade -y
```

## 2. Install Required Software

### Install PHP 8.1 and Extensions

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.1-fpm php8.1-cli php8.1-mysql php8.1-xml php8.1-gd php8.1-curl php8.1-mbstring php8.1-zip php8.1-bcmath php8.1-intl php8.1-redis
```

### Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### Install Node.js and NPM

```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

### Install Nginx

```bash
sudo apt install -y nginx
```

### Install MySQL 8.0

```bash
sudo apt install -y mysql-server-8.0
sudo mysql_secure_installation
```

### Install Redis

```bash
sudo apt install -y redis-server
```

### Install Additional Tools

```bash
sudo apt install -y git unzip exiftool gnupg supervisor
```

## 3. Install Monero

### Add Monero Repository

```bash
wget -qO - https://download.moneroocean.org/moneroocean.gpg.key | sudo apt-key add -
echo "deb https://download.moneroocean.org/ubuntu focal main" | sudo tee /etc/apt/sources.list.d/moneroocean.list
sudo apt update
```

### Install Monero

```bash
sudo apt install -y monero-wallet-rpc monerod
```

## 4. Database Setup

### Create Database and User

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE moneroexchange CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'moneroexchange'@'localhost' IDENTIFIED BY 'Walnutdesk88?';
GRANT ALL PRIVILEGES ON moneroexchange.* TO 'moneroexchange'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 5. Application Setup

### Clone and Install

```bash
cd /var/www
sudo git clone https://github.com/NoctraNetwork/moneroexchange.git moneroexchange
cd moneroexchange
sudo chown -R www-data:www-data /var/www/moneroexchange
sudo -u www-data composer install --no-dev --optimize-autoloader
```

### Environment Configuration

```bash
sudo -u www-data cp env.example .env
sudo -u www-data nano .env
```

Update the following values in `.env`:

```env
APP_NAME="Monero Exchange"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_DATABASE=moneroexchange
DB_USERNAME=moneroexchange
DB_PASSWORD=Walnutdesk88?

MONEROD_URL=http://127.0.0.1:18081
MONERO_WALLET_RPC_URL=http://127.0.0.1:18083
MONERO_WALLET_RPC_USER=your_wallet_rpc_user
MONERO_WALLET_RPC_PASS=your_wallet_rpc_pass
MONERO_WALLET_NAME=escrow_wallet

CSP_ENABLE=true
HSTS_ENABLE=true
```

### Generate Application Key

```bash
sudo -u www-data php artisan key:generate
```

### Run Migrations

```bash
sudo -u www-data php artisan migrate
```

### Seed Database

```bash
sudo -u www-data php artisan db:seed
```

### Build Assets

```bash
sudo -u www-data npm install
sudo -u www-data npm run build
```

## 6. Monero Wallet Setup

### Create Escrow Wallet

```bash
sudo -u www-data monero-wallet-rpc --daemon-address 127.0.0.1:18081 --rpc-bind-ip 127.0.0.1 --rpc-bind-port 18083 --wallet-file /var/www/moneroexchange/storage/wallets/escrow_wallet --password "your_secure_password" --daemon-login "your_daemon_user:your_daemon_pass"
```

**Important**: Store the wallet seed phrase securely offline!

## 7. Nginx Configuration

### Create Site Configuration

```bash
sudo nano /etc/nginx/sites-available/moneroexchange
```

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/moneroexchange/public;
    index index.php;

    # Security headers
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # CSP headers (handled by middleware)
    add_header Content-Security-Policy "default-src 'none'; script-src 'none'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'; upgrade-insecure-requests; block-all-mixed-content;" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    # File upload size
    client_max_body_size 10M;
}
```

### Enable Site

```bash
sudo ln -s /etc/nginx/sites-available/moneroexchange /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 8. Systemd Services

### Create Monero Daemon Service

```bash
sudo nano /etc/systemd/system/monerod.service
```

```ini
[Unit]
Description=Monero Daemon
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
ExecStart=/usr/bin/monerod --rpc-bind-ip 127.0.0.1 --rpc-bind-port 18081 --confirm-external-bind
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

### Create Monero Wallet RPC Service

```bash
sudo nano /etc/systemd/system/monero-wallet-rpc.service
```

```ini
[Unit]
Description=Monero Wallet RPC
After=network.target monerod.service
Requires=monerod.service

[Service]
Type=simple
User=www-data
Group=www-data
ExecStart=/usr/bin/monero-wallet-rpc --daemon-address 127.0.0.1:18081 --rpc-bind-ip 127.0.0.1 --rpc-bind-port 18083 --wallet-file /var/www/moneroexchange/storage/wallets/escrow_wallet --password "your_secure_password" --daemon-login "your_daemon_user:your_daemon_pass"
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

### Start Services

```bash
sudo systemctl daemon-reload
sudo systemctl enable monerod monero-wallet-rpc
sudo systemctl start monerod monero-wallet-rpc
```

## 9. Cron Jobs

### Add Cron Jobs

```bash
sudo crontab -e
```

Add these lines:

```cron
# Monero transaction scanning
*/5 * * * * cd /var/www/moneroexchange && sudo -u www-data php artisan xmr:scan

# Health checks
*/10 * * * * cd /var/www/moneroexchange && sudo -u www-data php artisan xmr:health

# Reprice floating offers
*/15 * * * * cd /var/www/moneroexchange && sudo -u www-data php artisan offers:reprice
```

## 10. SSL Certificate (Production)

### Install Certbot

```bash
sudo apt install -y certbot python3-certbot-nginx
```

### Obtain Certificate

```bash
sudo certbot --nginx -d yourdomain.com
```

## 11. Firewall Configuration

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

## 12. Verification

### Check Services

```bash
sudo systemctl status monerod monero-wallet-rpc nginx php8.1-fpm
```

### Test Application

```bash
curl -I https://yourdomain.com
```

### Check Logs

```bash
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/www/moneroexchange/storage/logs/laravel.log
```

## 13. Security Hardening

### Secure MySQL

```bash
sudo mysql_secure_installation
```

### Configure Fail2Ban

```bash
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
```

### Regular Backups

Create a backup script:

```bash
sudo nano /usr/local/bin/backup-moneroexchange.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/moneroexchange"
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u moneroexchange -p'Walnutdesk88?' moneroexchange > $BACKUP_DIR/moneroexchange_$DATE.sql

# Application backup
tar -czf $BACKUP_DIR/moneroexchange_app_$DATE.tar.gz /var/www/moneroexchange

# Cleanup old backups (keep 30 days)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

```bash
sudo chmod +x /usr/local/bin/backup-moneroexchange.sh
```

Add to crontab:

```cron
# Daily backup at 2 AM
0 2 * * * /usr/local/bin/backup-moneroexchange.sh
```

## Troubleshooting

### Common Issues

1. **Monero daemon not syncing**: Check if port 18080 is open
2. **Wallet RPC connection failed**: Verify credentials and wallet file path
3. **Database connection failed**: Check MySQL service and credentials
4. **Permission denied**: Ensure www-data owns the application directory

### Log Locations

- Application: `/var/www/moneroexchange/storage/logs/laravel.log`
- Nginx: `/var/log/nginx/error.log`
- Monero: Check with `journalctl -u monerod` and `journalctl -u monero-wallet-rpc`

### Support

For additional support, check the documentation in the `/docs` directory or create an issue in the repository.

