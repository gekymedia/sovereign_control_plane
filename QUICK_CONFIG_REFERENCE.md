# Quick Configuration Reference Card

## 🔑 Where to Get Each Key

### 1. SEPOLIA_RPC_URL
**Get from:**
- **Infura**: https://infura.io → Create Project → Copy Sepolia URL
- **Alchemy**: https://www.alchemy.com → Create App → Copy API URL
- **Format**: `https://sepolia.infura.io/v3/YOUR_PROJECT_ID`

### 2. SEPOLIA_PRIVATE_KEY
**Get from:**
- **MetaMask**: Extension → Account Details → Export Private Key
- **Or create new**: Use any wallet generator (for testing)
- **Format**: `abcdef1234567890...` (remove `0x` prefix if present)

### 3. CONTRACT_ADDRESS
**Get from:**
- **Deploy contract**: `cd hardhat && npx hardhat run scripts/deploy.js --network sepolia`
- **Copy address** from deployment output
- **Format**: `0x1234567890abcdef...`

### 4. ETHERSCAN_API_KEY (Optional)
**Get from:**
- **Etherscan**: https://etherscan.io/myapikey → Create API Key
- **Format**: `your_api_key_string`

---

## 📝 Quick Setup Steps

1. **Get RPC URL** → Sign up at https://infura.io (free)
2. **Get Private Key** → Export from MetaMask or create new wallet
3. **Get Test ETH** → https://sepoliafaucet.com (enter your wallet address)
4. **Deploy Contract** → `cd hardhat && npx hardhat run scripts/deploy.js --network sepolia`
5. **Copy Contract Address** → Add to `.env`
6. **Get Etherscan Key** → https://etherscan.io/myapikey (optional)

---

## 🔗 Important Links

| What | Where | Link |
|------|-------|------|
| RPC Provider | Infura | https://infura.io |
| RPC Provider | Alchemy | https://www.alchemy.com |
| Test ETH Faucet | Sepolia Faucet | https://sepoliafaucet.com |
| Block Explorer | Sepolia Etherscan | https://sepolia.etherscan.io |
| API Key | Etherscan | https://etherscan.io/myapikey |
| Wallet | MetaMask | https://metamask.io |

---

## ⚠️ Security Reminders

- ✅ Never commit `.env` file
- ✅ Never share private keys
- ✅ Use testnet for development
- ✅ Use hardware wallet for mainnet

---

**See `BLOCKCHAIN_CONFIGURATION_GUIDE.md` for detailed instructions!**
