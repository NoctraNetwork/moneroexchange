# Monero Exchange - Complete Installation Guide

## 🚀 Quick Start (One Command)

For a complete installation from start to finish, run this single command:

```bash
# Download and run the complete installation script
wget https://raw.githubusercontent.com/NoctraNetwork/moneroexchange/main/install-complete.sh
chmod +x install-complete.sh
sudo ./install-complete.sh
```

**That's it!** The script will install everything automatically.

---

## 📋 What Gets Installed

### **Core Services:**
- ✅ **MySQL 8.0** - Database server
- ✅ **Nginx** - Web server with security headers
- ✅ **Redis** - Caching and session storage
- ✅ **PHP 8.2** - PHP processor with all required extensions
- ✅ **Monero Daemon** - Monero blockchain daemon
- ✅ **Monero Wallet RPC** - Wallet service

### **Security Features:**
- ✅ **No JavaScript** - CSP policy blocks all scripts (Tor-friendly)
- ✅ **Rate Limiting** - Protection against brute force attacks
- ✅ **Security Headers** - X-Frame-Options, X-XSS-Protection, etc.
- ✅ **File Access Control** - Sensitive files protected
- ✅ **PHP Restrictions** - Dangerous functions disabled

### **Laravel Application:**
- ✅ **Complete Laravel 12** setup
- ✅ **Laravel Breeze** authentication
- ✅ **Database migrations** run automatically
- ✅ **Environment configuration** set up
- ✅ **File permissions** configured correctly

---

## 📖 Manual Installation (Step by Step)

If you prefer to install manually or need to troubleshoot, follow the complete guide:

**[📖 Complete Installation Guide](COMPLETE_INSTALLATION_GUIDE.md)**

This guide includes:
- Detailed step-by-step instructions
- Configuration explanations
- Troubleshooting tips
- Verification steps

---

## 🔧 Alternative Installation Methods

### **For Existing Issues (Quick Fix):**
If you already have some services running but need to fix specific issues:

```bash
# Fix Monero download/extraction
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

### **For AWS Server Setup:**
Use the comprehensive AWS setup script:

```bash
wget https://raw.githubusercontent.com/NoctraNetwork/moneroexchange/main/aws-server-setup.sh
chmod +x aws-server-setup.sh
sudo ./aws-server-setup.sh
```

---

## ✅ Verification

After installation, verify everything is working:

```bash
# Run the verification script
verify-monero-exchange

# Or check services manually
sudo systemctl status mysql nginx redis-server php8.2-fpm monerod monero-wallet-rpc

# Test web interface
curl -I http://127.0.0.1

# Test database
mysql -u moneroexchange -p'Walnutdesk88?' moneroexchange -e "SELECT 1;"

# Test Redis
redis-cli ping

# Test Monero RPC
curl -u monero:Walnutdesk88? http://127.0.0.1:18081/json_rpc -d '{"jsonrpc":"2.0","id":"0","method":"get_info"}'
```

---

## 🌐 Access Your Exchange

Once installed, access your Monero Exchange at:

- **Web Interface:** http://127.0.0.1
- **Monero RPC:** http://127.0.0.1:18081
- **Wallet RPC:** http://127.0.0.1:18082

### **Default Credentials:**
- **Database:** `moneroexchange` / `Walnutdesk88?`
- **Monero RPC:** `monero` / `Walnutdesk88?`

---

## 🛠️ Management Commands

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

---

## 🚨 Troubleshooting

### **Common Issues:**

1. **Services not starting:**
   ```bash
   sudo journalctl -xeu <service-name>
   ```

2. **Permission issues:**
   ```bash
   sudo chown -R www-data:www-data /var/www/moneroexchange
   sudo chown -R monero:monero /var/lib/monero
   ```

3. **Configuration errors:**
   ```bash
   sudo nginx -t
   sudo systemctl status <service-name>
   ```

### **For More Help:**
- Check the [Complete Installation Guide](COMPLETE_INSTALLATION_GUIDE.md)
- Review [Troubleshooting Guide](TROUBLESHOOTING.md)
- Check [Final Checklist](FINAL_CHECKLIST.md)

---

## 📁 File Structure

After installation, your files will be organized as:

```
/var/www/moneroexchange/          # Laravel application
/etc/nginx/sites-available/moneroexchange  # Nginx config
/etc/nginx/conf.d/ratelimit.conf  # Rate limiting
/etc/mysql/mysql.conf.d/mysqld.cnf # MySQL config
/etc/redis/redis.conf             # Redis config
/etc/monero/monerod.conf          # Monero config
/var/lib/monero/                  # Monero data
/var/log/monero/                  # Monero logs
```

---

## 🎉 Success!

Your Monero Exchange is now ready to use! 

**Next steps:**
1. Visit http://127.0.0.1 in your browser
2. Create your first user account
3. Configure your Monero wallet
4. Set up your exchange settings

**Everything has been tested and verified to work correctly!** 🚀
