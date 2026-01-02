# Blockchain Configuration Guide

This guide shows you exactly where to get each blockchain configuration value needed for the Sovereign Control Plane.

## Required Configuration Values

You need these 4 values in your `.env` file:

```env
CONTRACT_ADDRESS=0x...
SEPOLIA_RPC_URL=https://...
SEPOLIA_PRIVATE_KEY=...
ETHERSCAN_API_KEY=...
```

---

## 1. SEPOLIA_RPC_URL (Blockchain Connection)

You need an RPC endpoint to connect to the Sepolia testnet. Here are the best options:

### Option A: Infura (Recommended - Free)

1. **Go to**: https://infura.io
2. **Sign up** for a free account (or log in)
3. **Create a new project**:
   - Click "Create New Key"
   - Select "Web3 API" as the network
   - Give it a name (e.g., "Sovereign Control Plane")
4. **Copy your endpoint**:
   - Select "Sepolia" network
   - Copy the HTTPS URL
   - Format: `https://sepolia.infura.io/v3/YOUR_PROJECT_ID`
5. **Add to .env**:
   ```env
   SEPOLIA_RPC_URL=https://sepolia.infura.io/v3/YOUR_PROJECT_ID
   ```

### Option B: Alchemy (Alternative - Free)

1. **Go to**: https://www.alchemy.com
2. **Sign up** for a free account
3. **Create a new app**:
   - Click "Create App"
   - Name: "Sovereign Control Plane"
   - Network: Sepolia
   - Chain: Ethereum
4. **Copy your API key**:
   - Click on your app
   - Click "View Key"
   - Copy the HTTPS URL
   - Format: `https://eth-sepolia.g.alchemy.com/v2/YOUR_API_KEY`
5. **Add to .env**:
   ```env
   SEPOLIA_RPC_URL=https://eth-sepolia.g.alchemy.com/v2/YOUR_API_KEY
   ```

### Option C: Public RPC (Not Recommended for Production)

You can use public RPC endpoints, but they're rate-limited:
```env
SEPOLIA_RPC_URL=https://rpc.sepolia.org
```

---

## 2. SEPOLIA_PRIVATE_KEY (Your Wallet Key)

This is the private key of the wallet that will:
- Deploy the contract
- Update governance settings (needs oracle role)
- Execute admin functions

### ⚠️ SECURITY WARNING
**NEVER share your private key or commit it to version control!**

### How to Get Your Private Key

#### If you have MetaMask:

1. **Open MetaMask** browser extension
2. **Click the account icon** (top right)
3. **Go to "Account Details"**
4. **Click "Export Private Key"**
5. **Enter your password**
6. **Copy the private key** (it starts with `0x`)
7. **Add to .env**:
   ```env
   SEPOLIA_PRIVATE_KEY=your_private_key_here_without_0x_prefix
   ```
   Note: Remove the `0x` prefix if present

#### If you're creating a new wallet:

1. **Use a wallet generator** (for testing only):
   - Go to: https://vanity-eth.tk (or use any secure generator)
   - Generate a new wallet
   - **Save the private key securely**
   - **Save the address** (you'll need it)

2. **Or use Hardhat's built-in accounts**:
   ```bash
   cd hardhat
   npx hardhat node
   ```
   This will show you test accounts with private keys (for local testing only)

### Get Sepolia Test ETH

Your wallet needs Sepolia ETH for gas fees:

1. **Go to Sepolia Faucet**: https://sepoliafaucet.com
2. **Or Alchemy Faucet**: https://sepoliafaucet.com
3. **Or Infura Faucet**: https://www.infura.io/faucet/sepolia
4. **Enter your wallet address**
5. **Request test ETH** (usually 0.5-1 ETH per day)

---

## 3. CONTRACT_ADDRESS (Deploy the Contract)

You need to deploy the smart contract first, then use its address.

### Step 1: Deploy the Contract

1. **Make sure you have**:
   - Node.js installed
   - Hardhat dependencies installed: `cd hardhat && npm install`
   - Your `.env` configured with `SEPOLIA_RPC_URL` and `SEPOLIA_PRIVATE_KEY`

2. **Update Hardhat config** (if needed):
   - Edit `hardhat/hardhat.config.js`
   - Make sure it reads from environment variables

3. **Deploy to Sepolia**:
   ```bash
   cd hardhat
   npx hardhat run scripts/deploy.js --network sepolia
   ```

4. **Copy the contract address** from the output:
   ```
   ✅ SovereignFlashLoanExecutor deployed to: 0x1234567890abcdef...
   ```

5. **Add to .env**:
   ```env
   CONTRACT_ADDRESS=0x1234567890abcdef...
   ```

### Step 2: Set Contract Roles

After deploying, you need to set the oracle and admin roles:

1. **Set Oracle Address** (the account that can update governance):
   ```bash
   cd hardhat
   npx hardhat run scripts/setOracle.js --network sepolia
   ```
   (You may need to create this script)

2. **Or use Hardhat console**:
   ```bash
   npx hardhat console --network sepolia
   ```
   Then:
   ```javascript
   const contract = await ethers.getContractAt('SovereignFlashLoanExecutor', 'YOUR_CONTRACT_ADDRESS');
   await contract.setOracle('YOUR_ORACLE_WALLET_ADDRESS');
   ```

---

## 4. ETHERSCAN_API_KEY (Optional but Recommended)

This is used to verify your contract on Etherscan (makes it easier to view and interact with).

### How to Get Etherscan API Key:

1. **Go to**: https://etherscan.io
2. **Sign up** for a free account (or log in)
3. **Go to API-KEYs**: https://etherscan.io/myapikey
4. **Click "Add"** to create a new API key
5. **Give it a name** (e.g., "Sovereign Control Plane")
6. **Copy the API key**
7. **Add to .env**:
   ```env
   ETHERSCAN_API_KEY=your_etherscan_api_key_here
   ```

### Verify Your Contract (After Deployment):

```bash
cd hardhat
npx hardhat verify --network sepolia CONTRACT_ADDRESS
```

---

## Complete Example .env Configuration

Here's what your complete blockchain configuration should look like:

```env
# Blockchain Configuration
CONTRACT_ADDRESS=0x1234567890abcdef1234567890abcdef12345678
SEPOLIA_RPC_URL=https://sepolia.infura.io/v3/your_project_id_here
SEPOLIA_PRIVATE_KEY=abcdef1234567890abcdef1234567890abcdef1234567890abcdef1234567890
ETHERSCAN_API_KEY=your_etherscan_api_key_here
```

---

## Quick Setup Checklist

- [ ] Sign up for Infura or Alchemy
- [ ] Get your RPC URL
- [ ] Create or import a MetaMask wallet
- [ ] Export your private key (keep it secret!)
- [ ] Get Sepolia test ETH from a faucet
- [ ] Deploy the contract to Sepolia
- [ ] Copy the contract address
- [ ] (Optional) Sign up for Etherscan and get API key
- [ ] Add all values to your `.env` file
- [ ] Test the connection

---

## Security Best Practices

1. **Never commit `.env` to Git** - It's already in `.gitignore`
2. **Use different wallets** for testnet and mainnet
3. **Never share your private key** with anyone
4. **Use environment variables** in production, not hardcoded values
5. **Rotate keys** if they're ever exposed
6. **Use a hardware wallet** for mainnet (not needed for testnet)

---

## Troubleshooting

### "Insufficient funds for gas"
- Get more Sepolia ETH from a faucet
- Check your wallet address has ETH: https://sepolia.etherscan.io/address/YOUR_ADDRESS

### "Invalid RPC URL"
- Verify your RPC URL is correct
- Check if your Infura/Alchemy project is active
- Try a different RPC provider

### "Contract not found"
- Make sure you deployed the contract
- Verify the contract address is correct
- Check on Etherscan: https://sepolia.etherscan.io/address/YOUR_CONTRACT_ADDRESS

### "Unauthorized oracle"
- Make sure you set the oracle address on the contract
- Verify the private key account matches the oracle address
- Check contract roles: `await contract.oracle()`

---

## Need Help?

- **Infura Support**: https://infura.io/docs
- **Alchemy Docs**: https://docs.alchemy.com
- **Sepolia Faucet**: https://sepoliafaucet.com
- **Etherscan Sepolia**: https://sepolia.etherscan.io

---

**Remember**: These are testnet credentials. For mainnet, you'll need real ETH and should use a hardware wallet!
