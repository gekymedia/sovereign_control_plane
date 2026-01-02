# Infura Setup - Step by Step

## Step 1: Select Ethereum Network

On your Infura dashboard, you should see a list of networks. Look for:

**Ethereum** (with options: Mainnet | Sepolia)

Click on **"Ethereum"**

## Step 2: Select Sepolia Testnet

After clicking Ethereum, you'll see:
- Mainnet
- Sepolia ← **Choose this one!**

Click on **"Sepolia"**

## Step 3: Copy the HTTPS Endpoint

You'll see an endpoint URL that looks like:

```
https://sepolia.infura.io/v3/aae639a7775449608a253607408f009a
```

**Copy this entire URL** (including the `https://` part)

## Step 4: Add to Your .env File

Open your `.env` file in the project and add:

```env
SEPOLIA_RPC_URL=https://sepolia.infura.io/v3/aae639a7775449608a253607408f009a
```

Replace the URL with your actual copied URL.

---

## Visual Guide

```
Infura Dashboard
├── Ethereum ← Click here
│   ├── Mainnet
│   └── Sepolia ← Then click here
│       └── HTTPS: https://sepolia.infura.io/v3/YOUR_KEY ← Copy this
```

---

## Why Sepolia?

- ✅ It's a **testnet** (free test ETH, no real money)
- ✅ Perfect for development and testing
- ✅ Your contract will be deployed here
- ✅ Safe to experiment without risk

**DO NOT use Mainnet** - that uses real ETH and costs real money!

---

## Next Steps

After adding the RPC URL to your `.env`:

1. ✅ Get your private key from MetaMask
2. ✅ Get test ETH from a faucet
3. ✅ Deploy your contract
4. ✅ Start using the application!

---

**Your Project ID**: `aae639a7775449608a253607408f009a`

Keep this safe, but it's okay to use it in your `.env` file (just don't commit `.env` to Git).
