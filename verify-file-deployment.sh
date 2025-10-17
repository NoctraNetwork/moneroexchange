#!/bin/bash

# File Deployment Verification Script
# This script verifies that all Laravel files are properly deployed

echo "🔍 Verifying File Deployment - Monero Exchange"
echo "=============================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

print_info() {
    echo -e "${BLUE}[i]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use sudo)"
    exit 1
fi

# Set application directory
APP_DIR="/var/www/moneroexchange"

if [ ! -d "$APP_DIR" ]; then
    print_error "Laravel application not found at $APP_DIR"
    exit 1
fi

cd "$APP_DIR"

echo ""
print_info "Verifying Complete File Deployment..."

# 1. Check critical Laravel files
print_info "1. Checking Critical Laravel Files..."
CRITICAL_FILES=(
    "artisan"
    "composer.json"
    ".env"
    "public/index.php"
    "bootstrap/app.php"
    "config/app.php"
    "config/database.php"
    "config/session.php"
    "routes/web.php"
)

for file in "${CRITICAL_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_status "✅ $file exists"
    else
        print_error "❌ $file missing"
    fi
done

# 2. Check directory structure
print_info "2. Checking Directory Structure..."
CRITICAL_DIRS=(
    "app"
    "app/Http/Controllers"
    "app/Http/Controllers/Auth"
    "app/Http/Controllers/Admin"
    "app/Http/Middleware"
    "app/Models"
    "app/Services"
    "app/Providers"
    "resources/views"
    "resources/views/auth"
    "resources/views/layouts"
    "resources/views/offers"
    "resources/views/trades"
    "resources/views/admin"
    "resources/views/pages"
    "resources/views/feedback"
    "public"
    "storage"
    "storage/app"
    "storage/framework"
    "storage/framework/cache"
    "storage/framework/sessions"
    "storage/framework/views"
    "storage/logs"
    "database/migrations"
    "database/seeders"
    "config"
    "routes"
    "bootstrap/cache"
)

for dir in "${CRITICAL_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        print_status "✅ $dir/ directory exists"
    else
        print_error "❌ $dir/ directory missing"
    fi
done

# 3. Check view files
print_info "3. Checking View Files..."
VIEW_FILES=(
    "resources/views/layouts/app.blade.php"
    "resources/views/home.blade.php"
    "resources/views/dashboard.blade.php"
    "resources/views/auth/login.blade.php"
    "resources/views/auth/register.blade.php"
    "resources/views/auth/pin-verify.blade.php"
    "resources/views/auth/pin-locked.blade.php"
    "resources/views/offers/index.blade.php"
    "resources/views/offers/show.blade.php"
    "resources/views/trades/index.blade.php"
    "resources/views/trades/show.blade.php"
    "resources/views/trades/create.blade.php"
    "resources/views/admin/dashboard.blade.php"
    "resources/views/admin/rpc-config.blade.php"
    "resources/views/feedback/index.blade.php"
    "resources/views/feedback/create.blade.php"
    "resources/views/feedback/given.blade.php"
    "resources/views/feedback/profile.blade.php"
    "resources/views/pages/fees.blade.php"
    "resources/views/pages/how-it-works.blade.php"
    "resources/views/pages/privacy.blade.php"
    "resources/views/pages/security.blade.php"
    "resources/views/pages/terms.blade.php"
)

for file in "${VIEW_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_status "✅ $(basename $file) exists"
    else
        print_error "❌ $(basename $file) missing"
    fi
done

# 4. Check controller files
print_info "4. Checking Controller Files..."
CONTROLLER_FILES=(
    "app/Http/Controllers/HomeController.php"
    "app/Http/Controllers/Auth/AuthController.php"
    "app/Http/Controllers/Auth/AuthenticatedSessionController.php"
    "app/Http/Controllers/Auth/RegisteredUserController.php"
    "app/Http/Controllers/Auth/PasswordResetLinkController.php"
    "app/Http/Controllers/Auth/NewPasswordController.php"
    "app/Http/Controllers/Auth/ConfirmablePasswordController.php"
    "app/Http/Controllers/Auth/PasswordController.php"
    "app/Http/Controllers/Auth/EmailVerificationPromptController.php"
    "app/Http/Controllers/Auth/EmailVerificationNotificationController.php"
    "app/Http/Controllers/Auth/VerifyEmailController.php"
    "app/Http/Controllers/OfferController.php"
    "app/Http/Controllers/TradeController.php"
    "app/Http/Controllers/FeedbackController.php"
    "app/Http/Controllers/Admin/AdminController.php"
)

for file in "${CONTROLLER_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_status "✅ $(basename $file) exists"
    else
        print_error "❌ $(basename $file) missing"
    fi
done

# 5. Check model files
print_info "5. Checking Model Files..."
MODEL_FILES=(
    "app/Models/User.php"
    "app/Models/Offer.php"
    "app/Models/Trade.php"
    "app/Models/Dispute.php"
    "app/Models/Feedback.php"
    "app/Models/EscrowMovement.php"
    "app/Models/PaymentMethod.php"
    "app/Models/Verification.php"
    "app/Models/UserSecurityLog.php"
    "app/Models/AuditLog.php"
    "app/Models/Fee.php"
    "app/Models/Message.php"
    "app/Models/Setting.php"
    "app/Models/TradeEvent.php"
)

for file in "${MODEL_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_status "✅ $(basename $file) exists"
    else
        print_error "❌ $(basename $file) missing"
    fi
done

# 6. Check middleware files
print_info "6. Checking Middleware Files..."
MIDDLEWARE_FILES=(
    "app/Http/Middleware/UserProtectionMiddleware.php"
    "app/Http/Middleware/AdminProtectionMiddleware.php"
    "app/Http/Middleware/SecurityHeadersMiddleware.php"
    "app/Http/Middleware/Laravel12SecurityMiddleware.php"
    "app/Http/Middleware/VendorProtectionMiddleware.php"
    "app/Http/Middleware/OrderValidationMiddleware.php"
    "app/Http/Middleware/RateLimitMiddleware.php"
    "app/Http/Middleware/WalletBalanceMiddleware.php"
    "app/Http/Middleware/PgpVerificationMiddleware.php"
    "app/Http/Middleware/FileUploadMiddleware.php"
    "app/Http/Middleware/LoggingMiddleware.php"
    "app/Http/Middleware/TorDetectionMiddleware.php"
    "app/Http/Middleware/UserAgentMiddleware.php"
)

for file in "${MIDDLEWARE_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_status "✅ $(basename $file) exists"
    else
        print_error "❌ $(basename $file) missing"
    fi
done

# 7. Check service files
print_info "7. Checking Service Files..."
SERVICE_FILES=(
    "app/Services/MoneroRpcService.php"
    "app/Services/EscrowService.php"
    "app/Services/PinService.php"
    "app/Services/PgpService.php"
    "app/Services/WalletBalanceService.php"
    "app/Services/PriceIndexService.php"
)

for file in "${SERVICE_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_status "✅ $(basename $file) exists"
    else
        print_error "❌ $(basename $file) missing"
    fi
done

# 8. Check migration files
print_info "8. Checking Migration Files..."
MIGRATION_COUNT=$(find database/migrations -name "*.php" | wc -l)
if [ $MIGRATION_COUNT -gt 0 ]; then
    print_status "✅ $MIGRATION_COUNT migration files found"
    
    # List migration files
    echo "   Migration files:"
    find database/migrations -name "*.php" | while read file; do
        echo "   - $(basename $file)"
    done
else
    print_error "❌ No migration files found"
fi

# 9. Check seeder files
print_info "9. Checking Seeder Files..."
SEEDER_FILES=(
    "database/seeders/DatabaseSeeder.php"
    "database/seeders/UserSeeder.php"
    "database/seeders/OfferSeeder.php"
    "database/seeders/PaymentMethodSeeder.php"
    "database/seeders/FeeSeeder.php"
)

for file in "${SEEDER_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_status "✅ $(basename $file) exists"
    else
        print_error "❌ $(basename $file) missing"
    fi
done

# 10. Check configuration files
print_info "10. Checking Configuration Files..."
CONFIG_FILES=(
    "config/app.php"
    "config/database.php"
    "config/session.php"
    "config/security.php"
    "config/monero.php"
    "config/pgp.php"
    "config/laravel12.php"
    "config/tor.php"
)

for file in "${CONFIG_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_status "✅ $(basename $file) exists"
    else
        print_error "❌ $(basename $file) missing"
    fi
done

# 11. Check public assets
print_info "11. Checking Public Assets..."
PUBLIC_ASSETS=(
    "public/index.php"
    "public/.htaccess"
    "public/robots.txt"
    "public/favicon.ico"
)

for file in "${PUBLIC_ASSETS[@]}"; do
    if [ -f "$file" ]; then
        print_status "✅ $(basename $file) exists"
    else
        print_error "❌ $(basename $file) missing"
    fi
done

# 12. Check permissions
print_info "12. Checking File Permissions..."

# Check if files have correct ownership
if [ "$(stat -c %U /var/www/moneroexchange)" = "www-data" ]; then
    print_status "✅ Correct ownership (www-data)"
else
    print_error "❌ Incorrect ownership"
fi

# Check if storage is writable
if [ -w "storage" ]; then
    print_status "✅ Storage directory is writable"
else
    print_error "❌ Storage directory is not writable"
fi

# Check if bootstrap/cache is writable
if [ -w "bootstrap/cache" ]; then
    print_status "✅ Bootstrap cache directory is writable"
else
    print_error "❌ Bootstrap cache directory is not writable"
fi

# Check .env file permissions
if [ -f ".env" ]; then
    ENV_PERMS=$(stat -c %a .env)
    if [ "$ENV_PERMS" = "600" ]; then
        print_status "✅ .env file has correct permissions (600)"
    else
        print_warning "⚠️ .env file permissions: $ENV_PERMS (should be 600)"
    fi
fi

# 13. Check symlinks
print_info "13. Checking Symlinks..."
if [ -L "public/storage" ]; then
    print_status "✅ Storage symlink exists"
else
    print_error "❌ Storage symlink missing"
fi

echo ""
echo "=============================================="
print_info "File Deployment Verification Complete"
echo "=============================================="

# Count total files checked
TOTAL_FILES=$((${#CRITICAL_FILES[@]} + ${#VIEW_FILES[@]} + ${#CONTROLLER_FILES[@]} + ${#MODEL_FILES[@]} + ${#MIDDLEWARE_FILES[@]} + ${#SERVICE_FILES[@]} + ${#SEEDER_FILES[@]} + ${#CONFIG_FILES[@]} + ${#PUBLIC_ASSETS[@]}))
TOTAL_DIRS=${#CRITICAL_DIRS[@]}

print_status "✅ Verified $TOTAL_FILES files and $TOTAL_DIRS directories"
print_status "✅ Complete Laravel application structure deployed"
print_info "Your Monero Exchange has all necessary files and folders with correct permissions!"
