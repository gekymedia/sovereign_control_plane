# Environment Keys Setup Guide

Quick reference guide with direct links to get all required `.env` keys.

---

## 🔗 Quick Links to Get Your Keys

### Blockchain Configuration (Required)

#### 1. **Sepolia RPC URL** (Choose one provider)

**Option A: Infura**
- Website: https://infura.io
- Steps:
  1. Sign up for free account: https://infura.io/register
  2. Create a new project
  3. Go to Project Settings
  4. Copy your **Sepolia Endpoint URL**
  5. Format: `https://sepolia.infura.io/v3/YOUR_PROJECT_ID`

**Option B: Alchemy**
- Website: https://www.alchemy.com
- Steps:
  1. Sign up: https://auth.alchemy.com/signup
  2. Create a new app (select Sepolia network)
  3. Copy your **HTTPS API Key**
  4. Format: `https://eth-sepolia.g.alchemy.com/v2/YOUR_API_KEY`

#### 2. **Private Key** (Wallet Account)

**MetaMask (Easiest)**
- Website: https://metamask.io
- Steps:
  1. Install MetaMask extension
  2. Create or import an account
  3. Switch to Sepolia testnet
  4. Go to Account Details → Export Private Key
  5. **⚠️ Never share this key publicly!**

**Hardware Wallet Alternative**
- Ledger: https://www.ledger.com
- Trezor: https://trezor.io

#### 3. **Sepolia Test ETH** (For Gas Fees)

**Faucets** (Free test ETH):
- https://sepoliafaucet.com
- https://faucet.quicknode.com/ethereum/sepolia
- https://www.alchemy.com/faucets/ethereum-sepolia
- https://infura.io/faucet/sepolia

#### 4. **Etherscan API Key** (Optional - for contract verification)

- Website: https://etherscan.io
- Steps:
  1. Sign up: https://etherscan.io/register
  2. Go to API-KEYs: https://etherscan.io/myapikey
  3. Click "Add" to create new API key
  4. Copy your API key

**For Sepolia Testnet:**
- Use the same key or create at: https://sepolia.etherscan.io/myapikey

---

### OAuth Configuration (Optional)

#### 5. **Google OAuth Credentials**

- Website: https://console.cloud.google.com
- Steps:
  1. Go to Google Cloud Console
  2. Create new project (or select existing)
  3. Enable Google+ API: https://console.cloud.google.com/apis/library/people.googleapis.com
  4. Create OAuth credentials: https://console.cloud.google.com/apis/credentials
  5. Application type: **Web application**
  6. Authorized redirect URI: `http://localhost:8000/auth/google/callback`
  7. Copy **Client ID** and **Client Secret**

📖 **Detailed Guide**: See `OAUTH_SETUP_GUIDE.md`

#### 6. **GitHub OAuth Credentials**

- Website: https://github.com
- Steps:
  1. Go to your GitHub profile → Settings
  2. Developer settings: https://github.com/settings/developers
  3. OAuth Apps → New OAuth App
  4. Homepage URL: `http://localhost:8000`
  5. Authorization callback URL: `http://localhost:8000/auth/github/callback`
  6. Copy **Client ID**
  7. Generate and copy **Client Secret**

📖 **Detailed Guide**: See `OAUTH_SETUP_GUIDE.md`

---

### Database Configuration

#### 7. **MySQL/PostgreSQL Database**

**Local MySQL:**
- Download: https://dev.mysql.com/downloads/mysql/
- Or use XAMPP: https://www.apachefriends.org/
- Or use Docker: https://www.docker.com/products/docker-desktop/

**Cloud Options:**
- **PlanetScale**: https://planetscale.com (MySQL)
- **Supabase**: https://supabase.com (PostgreSQL)
- **Railway**: https://railway.app (MySQL/PostgreSQL)

---

### Mail Configuration (Optional)

#### 8. **SMTP Server** (For email notifications)

**Free Options:**
- **Mailtrap** (Testing): https://mailtrap.io
  - Get credentials from: https://mailtrap.io/inboxes
- **Mailpit** (Local): https://github.com/axllent/mailpit

**Production Options:**
- **SendGrid**: https://sendgrid.com
- **Mailgun**: https://www.mailgun.com
- **AWS SES**: https://aws.amazon.com/ses/

---

### Cloud Storage (Optional - for file storage)

#### 9. **AWS S3 Credentials**

- Website: https://aws.amazon.com
- Steps:
  1. Sign up: https://portal.aws.amazon.com
  2. Go to IAM: https://console.aws.amazon.com/iam
  3. Create user with S3 permissions
  4. Create access key: https://console.aws.amazon.com/iam/home#/users
  5. Copy **Access Key ID** and **Secret Access Key**
  6. Create S3 bucket: https://console.aws.amazon.com/s3

**Alternative: DigitalOcean Spaces**
- Website: https://www.digitalocean.com/products/spaces
- Similar S3-compatible API

---

## 📋 Complete `.env` Template

Once you have all keys, copy `.env.example` to `.env` and fill in:

```env
# Application
APP_NAME="Sovereign Control Plane"
APP_ENV=local
APP_KEY=  # Run: php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sovereign_control_plane
DB_USERNAME=root
DB_PASSWORD=your_password

# Blockchain (REQUIRED)
CONTRACT_ADDRESS=0xYourDeployedContract
SEPOLIA_RPC_URL=https://sepolia.infura.io/v3/YOUR_INFURA_PROJECT_ID
SEPOLIA_PRIVATE_KEY=your_private_key_here
ETHERSCAN_API_KEY=your_etherscan_api_key

# OAuth (Optional)
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
GITHUB_REDIRECT_URI=http://localhost:8000/auth/github/callback

# Mail (Optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
```

---

## ✅ Verification Checklist

After setting up your `.env` file:

- [ ] Database connection works (`php artisan migrate`)
- [ ] Blockchain RPC URL is accessible
- [ ] Private key has test ETH on Sepolia
- [ ] Contract is deployed (if using governance features)
- [ ] OAuth redirect URIs match exactly (if using OAuth)
- [ ] All keys are valid (no typos)

---

## 🔐 Security Reminders

1. **Never commit `.env` file** to version control
2. **Keep private keys secure** - use environment variables, never hardcode
3. **Use different keys for development and production**
4. **Rotate keys regularly** in production
5. **Use `.env.example`** as a template (no real values)

---

## 📚 Additional Resources

- **Infura Setup**: See `INFURA_SETUP_STEPS.md`
- **Blockchain Configuration**: See `BLOCKCHAIN_CONFIGURATION_GUIDE.md`
- **OAuth Setup**: See `OAUTH_SETUP_GUIDE.md`
- **Complete Setup**: See `SETUP_GUIDE.md`

---

## 🆘 Troubleshooting

### "Invalid RPC URL"
- Check that your Infura/Alchemy project is active
- Verify you're using the Sepolia endpoint (not mainnet)
- Ensure no extra spaces in the URL

### "Insufficient funds"
- Get test ETH from faucets listed above
- Verify you're on Sepolia testnet (not mainnet)

### "Invalid credentials" (OAuth)
- Check redirect URIs match exactly (including http/https, port, trailing slashes)
- Verify client ID and secret are correct
- Check OAuth app is enabled in provider console

### "Connection refused" (Database)
- Verify MySQL/PostgreSQL is running
- Check database credentials are correct
- Ensure database exists: `CREATE DATABASE sovereign_control_plane;`

---

**Last Updated**: January 2025

