# Sovereign Control Plane - Setup Guide

This guide will help you set up and run the Sovereign Control Plane (SCP) project.

## Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js 16+ and npm
- MySQL or PostgreSQL database
- Python 3 (for chart generation)
- Git

## Step 1: Clone and Install Dependencies

```bash
cd sovereign_control_plane

# Install PHP dependencies
composer install

# Install Node.js dependencies (for Laravel frontend)
npm install

# Install Hardhat dependencies (for blockchain integration)
cd hardhat
npm install
cd ..
```

## Step 2: Environment Configuration

Create a `.env` file by copying the template:

```bash
cp .env.example .env
```

Or manually create `.env` with these required variables:

```env
# Application
APP_NAME="Sovereign Control Plane"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sovereign_control_plane
DB_USERNAME=root
DB_PASSWORD=your_password

# Blockchain (REQUIRED for on-chain operations)
CONTRACT_ADDRESS=0xYourDeployedContract
SEPOLIA_RPC_URL=https://sepolia.infura.io/v3/YOUR_INFURA_PROJECT_ID
SEPOLIA_PRIVATE_KEY=your_private_key_here
ETHERSCAN_API_KEY=your_etherscan_api_key

# Queue
QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_DRIVER=database
```

Generate application key:

```bash
php artisan key:generate
```

## Step 3: Database Setup

Create your database:

```sql
CREATE DATABASE sovereign_control_plane;
```

Run migrations and seeders:

```bash
php artisan migrate --seed
```

This will:
- Create all database tables
- Create default roles (admin, oracle, viewer)
- Create default users:
  - **Admin**: admin@example.com / password
  - **Oracle**: oracle@example.com / password
  - **Viewer**: viewer@example.com / password
- Create initial governance settings

Create storage symlink:

```bash
php artisan storage:link
```

## Step 4: Build Frontend Assets

```bash
npm run build
```

For development with hot reload:

```bash
npm run dev
```

## Step 5: Configure Blockchain Settings

Before deploying, you need to get your blockchain configuration values.

**See `BLOCKCHAIN_CONFIGURATION_GUIDE.md` for detailed instructions on where to get:**
- SEPOLIA_RPC_URL (from Infura or Alchemy)
- SEPOLIA_PRIVATE_KEY (from MetaMask or wallet)
- CONTRACT_ADDRESS (after deployment)
- ETHERSCAN_API_KEY (optional, from Etherscan)

**Quick links:**
- Get RPC URL: https://infura.io (free account)
- Get Private Key: Export from MetaMask
- Get Test ETH: https://sepoliafaucet.com
- Get Etherscan Key: https://etherscan.io/myapikey

## Step 6: Deploy Smart Contract (Optional but Recommended)

If you haven't deployed the contract yet:

```bash
cd hardhat

# Configure your network in hardhat.config.js
# Set SEPOLIA_RPC_URL and SEPOLIA_PRIVATE_KEY in .env

# Deploy to Sepolia testnet
npx hardhat run scripts/deploy.js --network sepolia

# Copy the deployed contract address to your .env file
# CONTRACT_ADDRESS=0x...
```

## Step 7: Configure Contract Roles

After deploying, you need to set up roles:

1. **Set Oracle Address**: The account that will update governance settings
2. **Set Admin Address**: The account that can manage admin functions

You can do this via the Hardhat console or create a setup script.

## Step 8: Start the Application

### Development Server

```bash
# Start Laravel development server
php artisan serve

# In another terminal, start queue worker (for background jobs)
php artisan queue:work

# Access the application
# http://localhost:8000
```

### Production Setup

For production, use a proper web server (Nginx/Apache) and process manager (Supervisor) for queue workers.

## Step 9: First Login

1. Navigate to http://localhost:8000
2. Login with:
   - **Email**: admin@example.com
   - **Password**: password
3. You'll be redirected to the dashboard

## Step 10: Configure Governance Settings

1. Navigate to **Governance** panel (admin only)
2. Configure:
   - Overlay Tier
   - Expected Rate
   - Max Oracle Delay
   - Curve Stabilization
   - Fee Tiers
   - Profit Wallets
   - Loan Asset
3. Click **Save** - settings will be applied to blockchain in background

## Step 11: Upload and Run Scenarios

1. Navigate to **Scenarios** panel (oracle role)
2. Upload a scenario JSON file (see `hardhat/configs/scenario-sample.json` for format)
3. Click **Run Scenarios** to execute
4. View results on the Dashboard

## Troubleshooting

### Queue Jobs Not Running

Make sure the queue worker is running:

```bash
php artisan queue:work
```

Check queue status:

```bash
php artisan queue:failed
```

### Blockchain Transactions Failing

1. Check that `CONTRACT_ADDRESS` is correct
2. Verify `SEPOLIA_PRIVATE_KEY` has sufficient ETH for gas
3. Ensure the account has the required role (oracle/admin) on the contract
4. Check RPC URL is accessible

### Charts Not Generating

1. Ensure Python 3 is installed: `python3 --version`
2. Install required Python packages:
   ```bash
   pip install matplotlib pandas
   ```
3. Check logs: `storage/logs/laravel.log`

### Permission Errors

If you get permission errors on storage:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## File Structure

```
sovereign_control_plane/
├── app/                    # Laravel application
│   ├── Http/Controllers/   # Controllers
│   ├── Jobs/              # Background jobs
│   └── Models/            # Database models
├── database/
│   ├── migrations/        # Database migrations
│   └── seeders/          # Database seeders
├── hardhat/              # Smart contract project
│   ├── contracts/        # Solidity contracts
│   ├── scripts/          # Deployment & interaction scripts
│   └── configs/          # Scenario configs
├── resources/
│   ├── views/            # Blade templates
│   ├── js/               # JavaScript
│   └── css/              # Styles
└── routes/               # Route definitions
```

## Environment Variables Reference

### Required

- `APP_KEY` - Laravel application key
- `DB_*` - Database configuration
- `CONTRACT_ADDRESS` - Deployed contract address
- `SEPOLIA_RPC_URL` - Blockchain RPC endpoint
- `SEPOLIA_PRIVATE_KEY` - Account private key (keep secret!)

### Optional

- `ETHERSCAN_API_KEY` - For contract verification
- `LOG_LEVEL` - Logging level (debug, info, warning, error)
- `QUEUE_CONNECTION` - Queue driver (database, redis, etc.)

## Security Notes

1. **Never commit `.env` file** - It contains sensitive keys
2. **Keep private keys secure** - Use environment variables, not hardcoded values
3. **Use strong passwords** - Change default user passwords in production
4. **Enable HTTPS** - In production, always use SSL/TLS
5. **Restrict access** - Use firewall rules to limit access to admin panels

## Next Steps

- Review the [ANALYSIS_REPORT.md](./ANALYSIS_REPORT.md) for architecture details
- Customize governance settings for your use case
- Add more scenarios for testing
- Set up monitoring and alerting
- Configure backups

## Support

For issues or questions:
1. Check the logs: `storage/logs/laravel.log`
2. Review error messages in the application
3. Check blockchain transaction status on Etherscan

---

**Happy Governing! 🚀**
