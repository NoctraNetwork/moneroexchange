# Monero Exchange Installation Guide

## Quick Installation

### Single Command Installation

```bash
# Download and run the complete installation
wget https://raw.githubusercontent.com/NoctraNetwork/moneroexchange/main/install-monero-exchange.sh
chmod +x install-monero-exchange.sh
sudo ./install-monero-exchange.sh
```

That's it! The script will install everything automatically.

## What Gets Installed

- ✅ **Ubuntu 24.04** - System updates
- ✅ **MySQL 8.0** - Database server
- ✅ **Nginx** - Web server (with your exact configuration)
- ✅ **Redis** - Caching server
- ✅ **PHP 8.2** - For Laravel 11
- ✅ **Laravel 11** - Complete application
- ✅ **Monero** - Daemon and wallet RPC
- ✅ **All Dependencies** - Composer packages

## Features

- 🚀 **No Rate Limiting** - Uses your nginx configuration
- 🔒 **Security Headers** - CSP, XSS protection, etc.
- 💰 **Monero Integration** - Full escrow system
- 🎨 **No JavaScript** - Security-focused design
- 🔐 **Laravel Breeze** - Authentication system
- 📊 **Admin Panel** - Complete management interface

## Access Points

After installation, access your Monero Exchange at:

- **Main Site**: http://127.0.0.1
- **Admin Panel**: http://127.0.0.1/admin
- **Login**: http://127.0.0.1/login
- **Register**: http://127.0.0.1/register

## Troubleshooting

If you encounter any issues:

1. **Check services**: `sudo systemctl status nginx mysql redis-server php8.2-fpm`
2. **Check logs**: `sudo tail -f /var/log/nginx/error.log`
3. **Restart services**: `sudo systemctl restart nginx php8.2-fpm`

## Manual Commands

If you need to run commands manually:

```bash
# Go to application directory
cd /var/www/moneroexchange

# Run as www-data user
sudo -u www-data php artisan [command]

# Examples:
sudo -u www-data php artisan migrate
sudo -u www-data php artisan key:generate
sudo -u www-data php artisan config:cache
```

## Support

For issues or questions, check the logs or create an issue on GitHub.

---

**Note**: This installation is designed for localhost (127.0.0.1) deployment with no rate limiting as requested.