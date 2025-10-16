# Monero Exchange Wallet & Escrow System Setup Guide

This guide explains how to set up the Monero wallet and escrow system for the Monero Exchange platform.

## 🔧 Environment Configuration

### Required Environment Variables

Copy the following variables to your `.env` file:

```env
# Database Configuration (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=moneroexchange
DB_USERNAME=moneroexchange
DB_PASSWORD=Walnutdesk88?

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY_COOKIES=true
SESSION_SAME_SITE=strict

# Monero Configuration
MONEROD_URL=http://127.0.0.1:18081
MONEROD_USER=
MONEROD_PASS=
MONERO_WALLET_RPC_URL=http://127.0.0.1:18083
MONERO_WALLET_RPC_USER=
MONERO_WALLET_RPC_PASS=
MONERO_WALLET_NAME=escrow_wallet
MONERO_WALLET_PASSWORD=your_escrow_wallet_password
XMR_CONFIRMATIONS=10
XMR_MIN_WITHDRAWAL_ATOMIC=1000000000000
XMR_ATOMIC_UNITS=1000000000000

# Trade Configuration
MIN_TRADE_AMOUNT_ATOMIC=1000000000000
MAX_TRADE_AMOUNT_ATOMIC=100000000000000
TRADE_TIMEOUT_MINUTES=1440
ESCROW_TIMEOUT_MINUTES=2880

# Fee Configuration
TRADE_FEE_BPS=25
WITHDRAWAL_FEE_BPS=25
ADMIN_FEE_BPS=25

# Security Configuration
CSRF_PROTECTION=true
RATE_LIMIT_LOGIN=5
RATE_LIMIT_LOGIN_LOCKOUT=15
ORDER_VALIDATION_ENABLED=true
ORDER_DUPLICATE_CHECK_MINUTES=5
```

## 🏦 Monero Wallet Setup

### 1. Install Monero Daemon and Wallet RPC

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install monero-wallet-rpc monerod

# Or build from source
git clone https://github.com/monero-project/monero.git
cd monero
make
```

### 2. Start Monero Daemon

```bash
# Mainnet
monerod --rpc-bind-ip=127.0.0.1 --rpc-bind-port=18081 --confirm-external-bind

# Testnet (for testing)
monerod --testnet --rpc-bind-ip=127.0.0.1 --rpc-bind-port=18081 --confirm-external-bind
```

### 3. Create Escrow Wallet

```bash
# Create the escrow wallet
monero-wallet-cli --generate-new-wallet /path/to/escrow_wallet

# Or restore from seed
monero-wallet-cli --restore-deterministic-wallet /path/to/escrow_wallet
```

### 4. Start Wallet RPC

```bash
monero-wallet-rpc \
  --rpc-bind-ip=127.0.0.1 \
  --rpc-bind-port=18083 \
  --wallet-file=/path/to/escrow_wallet \
  --password=your_escrow_wallet_password \
  --rpc-login=username:password \
  --confirm-external-bind \
  --daemon-address=127.0.0.1:18081
```

## 🗄️ Database Setup

### 1. Run Migrations

```bash
php artisan migrate
```

This will create the following tables:
- `users` (with `is_admin` column)
- `sessions` (for secure session storage)
- `trades` (for trade management)
- `escrow_movements` (for escrow tracking)
- `trade_events` (for trade history)

### 2. Create Admin User

```bash
php artisan tinker
```

```php
$user = \App\Models\User::create([
    'username' => 'admin',
    'password' => 'your_secure_password',
    'pin' => '1234',
    'is_admin' => true,
    'status' => 'active'
]);
```

## 🔄 Escrow System Flow

### 1. Trade Creation Flow

1. **Buyer creates trade** → Trade state: `draft`
2. **Escrow subaddress created** → Trade state: `await_deposit`
3. **Buyer sends XMR to escrow** → Trade state: `escrowed`
4. **Seller confirms payment** → Trade state: `await_payment`
5. **Buyer releases escrow** → Trade state: `completed`

### 2. Escrow States

- `draft` - Trade created, awaiting escrow setup
- `await_deposit` - Waiting for buyer to send XMR
- `escrowed` - XMR received, waiting for payment confirmation
- `await_payment` - Seller confirmed payment, waiting for release
- `release_pending` - Payment confirmed, waiting for escrow release
- `completed` - Trade completed successfully
- `cancelled` - Trade cancelled
- `refunded` - Escrow refunded to seller

### 3. Automatic Processing

The system includes automatic processing for:
- **Deposit detection** - Monitors escrow addresses for incoming XMR
- **State transitions** - Automatically updates trade states
- **Fee calculation** - Calculates and deducts trading fees
- **Timeout handling** - Handles expired trades

## 🚀 Running the System

### 1. Start the Application

```bash
php artisan serve
```

### 2. Process Escrow Deposits (Cron Job)

Add to your crontab:

```bash
# Process escrow deposits every minute
* * * * * cd /path/to/moneroexchange && php artisan escrow:process-deposits

# Or run manually
php artisan escrow:process-deposits
```

### 3. Monitor Wallet Sync

```bash
# Check if wallet is synced
curl -X POST http://127.0.0.1:18083/json_rpc \
  -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","id":"0","method":"get_height"}'
```

## 🔒 Security Features

### 1. CSRF Protection
- All forms include CSRF tokens
- AJAX requests automatically include CSRF headers
- Token refresh every 30 minutes

### 2. Rate Limiting
- Login: 5 attempts per 15 minutes
- Trade creation: 10 attempts per 5 minutes
- Escrow operations: 5 attempts per 5 minutes

### 3. Order Validation
- Duplicate order detection (5-minute window)
- Amount validation (min/max limits)
- Monero address validation
- User permission checks

### 4. Session Security
- Database-stored sessions
- HTTP-only cookies
- Secure cookie settings
- Session regeneration on login

## 📊 Monitoring & Logging

### 1. Trade Events
All trade actions are logged with:
- Event type and timestamp
- User who performed the action
- Transaction hashes
- Amount details

### 2. Escrow Movements
Track all escrow transactions:
- Incoming deposits
- Outgoing releases
- Fee deductions
- Transaction confirmations

### 3. Security Logs
Monitor security events:
- Failed login attempts
- Suspicious activity
- Rate limit violations
- CSRF token mismatches

## 🛠️ Troubleshooting

### Common Issues

1. **Wallet not syncing**
   - Check daemon connection
   - Verify RPC credentials
   - Check network connectivity

2. **Escrow deposits not detected**
   - Run `php artisan escrow:process-deposits`
   - Check wallet RPC connection
   - Verify subaddress creation

3. **Trade state not updating**
   - Check escrow service logs
   - Verify database connections
   - Run manual deposit processing

### Debug Commands

```bash
# Check wallet balance
php artisan tinker
$escrowService = app(\App\Services\EscrowService::class);
$escrowService->getWalletBalance();

# Check sync status
$escrowService->getSyncStatus();

# Process deposits manually
$escrowService->processPendingDeposits();
```

## 🔧 Configuration Files

### Key Files Created/Modified

**Services:**
- `app/Services/EscrowService.php` - Main escrow logic
- `app/Services/MoneroRpcService.php` - Monero RPC integration

**Controllers:**
- `app/Http/Controllers/TradeController.php` - Trade management

**Models:**
- `app/Models/Trade.php` - Trade model (enhanced)
- `app/Models/EscrowMovement.php` - Escrow tracking

**Views:**
- `resources/views/trades/` - Trade interface

**Commands:**
- `app/Console/Commands/ProcessEscrowDeposits.php` - Deposit processing

**Configuration:**
- `config/monero.php` - Monero settings
- `config/session.php` - Session security
- `config/security.php` - Security settings

## ✅ Verification Checklist

- [ ] Monero daemon running and synced
- [ ] Wallet RPC running and accessible
- [ ] Database migrations completed
- [ ] Admin user created
- [ ] Environment variables configured
- [ ] Cron job set up for deposit processing
- [ ] CSRF protection working
- [ ] Rate limiting active
- [ ] Session security enabled
- [ ] Trade creation flow working
- [ ] Escrow deposit detection working
- [ ] Escrow release flow working

## 🚨 Important Security Notes

1. **Wallet Security**
   - Use strong passwords for wallet
   - Keep wallet files secure
   - Regular backups of wallet files

2. **RPC Security**
   - Use authentication for RPC connections
   - Bind to localhost only
   - Use HTTPS in production

3. **Database Security**
   - Use strong database passwords
   - Enable SSL connections
   - Regular backups

4. **Application Security**
   - Keep Laravel updated
   - Monitor security logs
   - Regular security audits

This setup provides a complete, secure Monero trading platform with proper escrow functionality and comprehensive security measures.
