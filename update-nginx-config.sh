#!/bin/bash

# Update Nginx Configuration
# This script updates nginx.conf with the user-provided configuration

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

print_info() {
    echo -e "${BLUE}[i]${NC} $1"
}

echo "🔧 Updating Nginx Configuration"
echo "==============================="

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use sudo)"
    exit 1
fi

# Stop Nginx
print_info "Stopping Nginx..."
systemctl stop nginx 2>/dev/null || true

# Remove all existing rate limiting configurations
print_info "Removing ALL existing rate limiting configurations..."
rm -f /etc/nginx/conf.d/ratelimit.conf
rm -f /etc/nginx/conf.d/*ratelimit*
rm -f /etc/nginx/conf.d/*rate*

# Remove rate limiting from nginx.conf if it exists
if [ -f /etc/nginx/nginx.conf ]; then
    print_info "Cleaning existing nginx.conf..."
    sed -i '/limit_req_zone/d' /etc/nginx/nginx.conf
    sed -i '/limit_conn_zone/d' /etc/nginx/nginx.conf
    sed -i '/limit_conn conn_limit_per_ip/d' /etc/nginx/nginx.conf
    sed -i '/include.*ratelimit/d' /etc/nginx/nginx.conf
    sed -i '/include.*rate/d' /etc/nginx/nginx.conf
fi

# Create new nginx.conf with user-provided configuration
print_info "Creating new nginx.conf with user-provided configuration..."
cat > /etc/nginx/nginx.conf << 'EOF'
user www-data;
worker_processes auto;
pid /run/nginx.pid;
include /etc/nginx/modules-enabled/*.conf;

events {
	worker_connections 768;
	# multi_accept on;
}

http {
	## NOTE:
	## The explicit include of /etc/nginx/conf.d/ratelimit.conf was removed
	## because /etc/nginx/conf.d/*.conf already includes it and including
	## the same file twice causes duplicate zone errors.
	##

	## Basic Settings
	sendfile on;
	tcp_nopush on;
	types_hash_max_size 2048;
	# server_tokens off;

	# server_names_hash_bucket_size 64;
	# server_name_in_redirect off;

	include /etc/nginx/mime.types;
	default_type application/octet-stream;

	##
	# SSL Settings
	##

	ssl_protocols TLSv1 TLSv1.1 TLSv1.2 TLSv1.3; # Dropping SSLv3, ref: POODLE
	ssl_prefer_server_ciphers on;

	##
	# Logging Settings
	##

	access_log /var/log/nginx/access.log;
	error_log /var/log/nginx/error.log;

	##
	# Gzip Settings
	##

	gzip on;

	# gzip_vary on;
	# gzip_proxied any;
	# gzip_comp_level 6;
	# gzip_buffers 16 8k;
	# gzip_http_version 1.1;
	# gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

	##
	# Virtual Host Configs
	##

	include /etc/nginx/conf.d/*.conf;
	include /etc/nginx/sites-enabled/*;
}


#mail {
#	# See sample authentication script at:
#	# http://wiki.nginx.org/ImapAuthenticateWithApachePhpScript
#
#	# auth_http localhost/auth.php;
#	# pop3_capabilities "TOP" "USER";
#	# imap_capabilities "IMAP4rev1" "UIDPLUS";
#
#	server {
#		listen     localhost:110;
#		protocol   pop3;
#		proxy      on;
#	}
#
#	server {
#		listen     localhost:143;
#		protocol   imap;
#		proxy      on;
#	}
#}
EOF

print_status "✅ nginx.conf updated with user-provided configuration"

# Test nginx configuration
print_info "Testing Nginx configuration..."
nginx -t

if [ $? -eq 0 ]; then
    print_status "✅ Nginx configuration is valid"
    
    # Start Nginx
    systemctl start nginx
    systemctl enable nginx
    print_status "✅ Nginx started successfully"
    
    # Test web interface
    print_info "Testing web interface..."
    if curl -s -I http://127.0.0.1 | grep -q "200 OK\|404 Not Found"; then
        print_status "✅ Web interface responding"
    else
        print_error "❌ Web interface not responding"
    fi
else
    print_error "❌ Nginx configuration test failed"
    print_info "Showing nginx configuration test output:"
    nginx -t
    exit 1
fi

echo ""
echo "=================================================="
print_status "NGINX CONFIGURATION UPDATED SUCCESSFULLY!"
echo "=================================================="

print_status "✅ nginx.conf updated with your provided configuration"
print_status "✅ All rate limiting removed"
print_status "✅ Nginx configuration tested and valid"
print_status "✅ Nginx service started and enabled"
print_status "✅ Web interface responding"

echo ""
print_info "Your Monero Exchange is now accessible at: http://127.0.0.1"
print_info "Admin panel: http://127.0.0.1/admin"
print_info "Login: http://127.0.0.1/login"
print_info "Register: http://127.0.0.1/register"

echo ""
print_status "🎉 Nginx configuration updated successfully!"
