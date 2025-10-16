# Monero Exchange

A production-ready, no-JavaScript peer-to-peer Monero (XMR) exchange platform with escrow protection, built with Laravel 11 and Blade templating.

## Features

### 🔐 Security & Privacy
- **No JavaScript**: Strict Content Security Policy (CSP) blocks all scripts
- **Username + Password + PIN**: Multi-factor authentication with PIN lockouts
- **PGP Integration**: Optional PGP key verification for enhanced security
- **Tor Support**: Full Tor hidden service configuration
- **Argon2id Hashing**: Secure password and PIN hashing
- **Rate Limiting**: Protection against brute force attacks

### 💰 Monero Integration
- **Escrow System**: Per-trade subaddresses for secure fund holding
- **Wallet RPC**: Integration with monero-wallet-rpc for transactions
- **Confirmation Tracking**: Configurable confirmation requirements
- **Fee Management**: Platform fees with atomic precision

### 🛡️ Escrow Protection
- **Subaddress Generation**: Unique subaddress for each trade
- **Fund Locking**: Automatic fund detection and escrow
- **Release/Refund**: Secure fund release with PIN verification
- **Dispute Resolution**: Built-in dispute system with moderator support

### 📊 Trading Features
- **Buy/Sell Offers**: Create and manage trading offers
- **Price Types**: Fixed prices or floating index-based pricing
- **Payment Methods**: Support for multiple payment methods
- **Reputation System**: User feedback and completion rates
- **Trade Chat**: Encrypted messaging between traders

### 🏛️ Compliance & Admin
- **KYC Tiers**: Optional verification levels
- **AML Policies**: Automated compliance screening
- **Admin Dashboard**: Complete platform management
- **Audit Logging**: Comprehensive activity tracking

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.1+)
- **Database**: MySQL 8.0
- **Frontend**: Blade templates with Tailwind CSS
- **Caching**: Redis
- **Queue**: Redis (with fallback to sync)
- **Monero**: monerod + monero-wallet-rpc
- **Web Server**: Nginx
- **OS**: Ubuntu 22.04 LTS

## Installation

### Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/NoctraNetwork/moneroexchange.git moneroexchange
   cd moneroexchange
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment**
   ```bash
   cp env.example .env
   # Edit .env with your configuration
   ```

4. **Setup database**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build assets**
   ```bash
   npm run build
   ```

6. **Start development server**
   ```bash
   php artisan serve
   ```

### Production Installation

For production deployment, see the comprehensive [Installation Guide](INSTALLATION_GUIDE.md).

## Configuration

### Environment Variables

Key configuration options in `.env`:

```env
# Monero Configuration
MONEROD_URL=http://127.0.0.1:18081
MONERO_WALLET_RPC_URL=http://127.0.0.1:18083
MONERO_WALLET_RPC_USER=your_user
MONERO_WALLET_RPC_PASS=your_pass
MONERO_WALLET_NAME=escrow_wallet
XMR_CONFIRMATIONS=10

# Security
AUTH_REQUIRE_PIN_ON_LOGIN=true
CSP_ENABLE=true
HSTS_ENABLE=true

# Fees
TRADE_FEE_BPS=25
WITHDRAWAL_FEE_BPS=25
```

### Monero Setup

1. **Start monerod**
   ```bash
   monerod --rpc-bind-ip 127.0.0.1 --rpc-bind-port 18081
   ```

2. **Create escrow wallet**
   ```bash
   monero-wallet-rpc --daemon-address 127.0.0.1:18081 \
     --rpc-bind-ip 127.0.0.1 --rpc-bind-port 18083 \
     --wallet-file escrow_wallet --password "secure_password"
   ```

## Usage

### User Registration

1. Visit the registration page
2. Choose a username (3-255 characters, alphanumeric + underscore/hyphen)
3. Set a strong password (8+ characters with mixed case, numbers, symbols)
4. Create a PIN (4-8 digits, not common patterns)
5. Optionally set country and enable PGP

### Creating Offers

1. Login to your account
2. Navigate to "Post Offer"
3. Choose Buy or Sell
4. Set price type (Fixed or Floating with margin)
5. Specify amount range in XMR
6. Select payment method and location
7. Add terms and conditions

### Trading Process

1. **Browse Offers**: Find suitable offers by filters
2. **Start Trade**: Click on an offer to initiate trade
3. **Deposit Funds**: Seller deposits exact amount to escrow subaddress
4. **Escrow Confirmation**: System detects deposit and locks funds
5. **Payment**: Buyer sends payment to seller
6. **Release**: Seller confirms payment, funds released to buyer
7. **Feedback**: Both parties leave feedback

### Admin Functions

- **User Management**: View, suspend, or verify users
- **Trade Monitoring**: Track all trades and disputes
- **System Health**: Monitor Monero daemon and wallet status
- **Compliance**: Review KYC applications and flagged trades
- **Settings**: Configure fees, limits, and system parameters

## Security Features

### Content Security Policy

The application enforces a strict CSP that blocks all JavaScript:

```
default-src 'none';
script-src 'none';
style-src 'self' 'unsafe-inline';
img-src 'self' data:;
font-src 'self';
connect-src 'self';
frame-ancestors 'none';
base-uri 'none';
form-action 'self';
upgrade-insecure-requests;
block-all-mixed-content;
```

### PIN Security

- Argon2id hashing with separate salt
- Exponential backoff on failed attempts
- Temporary lockouts after max attempts
- Rate limiting per IP and user
- Common PIN prevention

### PGP Integration

- Public key verification with signature challenge
- Encrypted trade messages
- Fingerprint storage and validation
- GnuPG CLI integration

## API

### Read-Only API

The platform provides a cached, read-only API:

- `GET /api/v1/offers` - List active offers
- `GET /api/v1/user/{id}/rep` - User reputation data
- `GET /api/v1/prices` - Current XMR prices

All API responses are cached for performance.

## Development

### Running Tests

```bash
php artisan test
```

### Code Style

```bash
./vendor/bin/pint
```

### Database Seeding

```bash
php artisan db:seed
```

### Monero Commands

```bash
# Scan for new transactions
php artisan xmr:scan

# Check system health
php artisan xmr:health

# Reprice floating offers
php artisan offers:reprice
```

## Deployment

### Production Checklist

- [ ] SSL certificate installed
- [ ] Firewall configured
- [ ] Database backups scheduled
- [ ] Monero wallet seed stored securely
- [ ] Log rotation configured
- [ ] Monitoring setup
- [ ] Fail2ban installed

### Docker Support

Docker configuration files are provided for easy deployment:

```bash
docker-compose up -d
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- **Documentation**: Check the `/docs` directory
- **Issues**: Create an issue in the repository
- **Security**: Report security issues privately

## Disclaimer

This software is provided for educational and research purposes. Users are responsible for compliance with local laws and regulations. The developers are not responsible for any financial losses or legal issues arising from the use of this software.

## Acknowledgments

- Monero Project for the excellent cryptocurrency
- Laravel team for the robust framework
- Tailwind CSS for the utility-first styling
- All contributors and testers

---

**Monero Exchange** - Secure, private, peer-to-peer Monero trading.

