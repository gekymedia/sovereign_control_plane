const { ethers } = require('hardhat');
const fs = require('fs');
const path = require('path');

/**
 * Script to apply governance settings from the database to the blockchain.
 * 
 * This script reads the latest governance settings from a JSON file (generated
 * by Laravel) and applies them to the deployed SovereignFlashLoanExecutor contract.
 * 
 * Usage:
 *   node hardhat/scripts/applyGovernance.js
 * 
 * Environment variables:
 *   CONTRACT_ADDRESS - The deployed contract address (required)
 *   PRIVATE_KEY - Private key for the oracle/admin account (required)
 *   NETWORK - Network name (default: sepolia)
 */

async function main() {
  // Load governance settings from Laravel storage
  const settingsPath = path.join(__dirname, '../../storage/app/governance-latest.json');
  
  if (!fs.existsSync(settingsPath)) {
    console.error('❌ Governance settings file not found:', settingsPath);
    console.log('💡 Make sure the governance settings have been saved from the Laravel application.');
    process.exitCode = 1;
    return;
  }

  const settings = JSON.parse(fs.readFileSync(settingsPath, 'utf8'));
  console.log('📋 Loaded governance settings:', JSON.stringify(settings, null, 2));

  // Get contract address from environment or settings
  const contractAddress = process.env.CONTRACT_ADDRESS || settings.contract_address;
  if (!contractAddress || contractAddress === '0xYourDeployedContract') {
    console.error('❌ CONTRACT_ADDRESS not set. Please set it in your .env file or settings.');
    process.exitCode = 1;
    return;
  }

  // Get the contract instance
  const executor = await ethers.getContractAt('SovereignFlashLoanExecutor', contractAddress);
  console.log('✅ Connected to contract at:', contractAddress);

  // Get the signer (should be oracle or admin)
  const [signer] = await ethers.getSigners();
  console.log('👤 Using account:', signer.address);
  
  // Track all transaction hashes and block numbers
  const transactions = [];

  try {
    // Map overlay tier string to enum value
    // OverlayTier enum: None = 0, ChaoticTier13 = 1, SovereignTierX = 2
    const overlayTierMap = {
      'none': 0,
      'ChaoticTier13': 1,
      'SovereignTierX': 2
    };
    const overlayTierValue = overlayTierMap[settings.overlay_tier] ?? 0;

    // Apply settings that require oracle role
    console.log('\n🔧 Applying oracle-controlled settings...');
    
    if (settings.overlay_tier !== undefined) {
      try {
        const tx = await executor.setOverlayTier(overlayTierValue);
        console.log('  ⏳ Setting overlay tier...');
        const receipt = await tx.wait();
        transactions.push({
          function: 'setOverlayTier',
          hash: receipt.hash,
          blockNumber: receipt.blockNumber,
          gasUsed: receipt.gasUsed.toString()
        });
        console.log('  ✅ Overlay tier set to:', settings.overlay_tier);
        console.log('  📝 Transaction:', receipt.hash);
      } catch (e) {
        console.error('  ❌ Failed to set overlay tier:', e.message);
      }
    }

    if (settings.expected_rate !== undefined) {
      try {
        // Convert expected rate to wei (assuming it's in ETH/USD format)
        const expectedRate = ethers.parseUnits(settings.expected_rate.toString(), 8); // Chainlink uses 8 decimals
        const tx = await executor.setExpectedRate(expectedRate);
        console.log('  ⏳ Setting expected rate...');
        const receipt = await tx.wait();
        transactions.push({
          function: 'setExpectedRate',
          hash: receipt.hash,
          blockNumber: receipt.blockNumber,
          gasUsed: receipt.gasUsed.toString()
        });
        console.log('  ✅ Expected rate set to:', settings.expected_rate);
        console.log('  📝 Transaction:', receipt.hash);
      } catch (e) {
        console.error('  ❌ Failed to set expected rate:', e.message);
      }
    }

    if (settings.max_oracle_delay !== undefined) {
      try {
        const tx = await executor.setMaxOracleDelay(settings.max_oracle_delay);
        console.log('  ⏳ Setting max oracle delay...');
        const receipt = await tx.wait();
        transactions.push({
          function: 'setMaxOracleDelay',
          hash: receipt.hash,
          blockNumber: receipt.blockNumber,
          gasUsed: receipt.gasUsed.toString()
        });
        console.log('  ✅ Max oracle delay set to:', settings.max_oracle_delay, 'seconds');
        console.log('  📝 Transaction:', receipt.hash);
      } catch (e) {
        console.error('  ❌ Failed to set max oracle delay:', e.message);
      }
    }

    // Apply settings that require admin role
    console.log('\n🔧 Applying admin-controlled settings...');

    if (settings.profit_wallet) {
      try {
        const tx = await executor.updateProfitWallet(settings.profit_wallet);
        console.log('  ⏳ Updating profit wallet...');
        const receipt = await tx.wait();
        transactions.push({
          function: 'updateProfitWallet',
          hash: receipt.hash,
          blockNumber: receipt.blockNumber,
          gasUsed: receipt.gasUsed.toString()
        });
        console.log('  ✅ Profit wallet set to:', settings.profit_wallet);
        console.log('  📝 Transaction:', receipt.hash);
      } catch (e) {
        console.error('  ❌ Failed to set profit wallet:', e.message);
      }
    }

    if (settings.fallback_wallet) {
      try {
        const tx = await executor.setFallbackWallet(settings.fallback_wallet);
        console.log('  ⏳ Setting fallback wallet...');
        const receipt = await tx.wait();
        transactions.push({
          function: 'setFallbackWallet',
          hash: receipt.hash,
          blockNumber: receipt.blockNumber,
          gasUsed: receipt.gasUsed.toString()
        });
        console.log('  ✅ Fallback wallet set to:', settings.fallback_wallet);
        console.log('  📝 Transaction:', receipt.hash);
      } catch (e) {
        console.error('  ❌ Failed to set fallback wallet:', e.message);
      }
    }

    if (settings.loan_asset) {
      try {
        const tx = await executor.setLoanAsset(settings.loan_asset);
        console.log('  ⏳ Setting loan asset...');
        const receipt = await tx.wait();
        transactions.push({
          function: 'setLoanAsset',
          hash: receipt.hash,
          blockNumber: receipt.blockNumber,
          gasUsed: receipt.gasUsed.toString()
        });
        console.log('  ✅ Loan asset set to:', settings.loan_asset);
        console.log('  📝 Transaction:', receipt.hash);
      } catch (e) {
        console.error('  ❌ Failed to set loan asset:', e.message);
      }
    }

    if (settings.uni_fee_tier !== undefined) {
      try {
        const tx = await executor.setUniFeeTier(settings.uni_fee_tier);
        console.log('  ⏳ Setting Uniswap fee tier...');
        const receipt = await tx.wait();
        transactions.push({
          function: 'setUniFeeTier',
          hash: receipt.hash,
          blockNumber: receipt.blockNumber,
          gasUsed: receipt.gasUsed.toString()
        });
        console.log('  ✅ Uniswap fee tier set to:', settings.uni_fee_tier);
        console.log('  📝 Transaction:', receipt.hash);
      } catch (e) {
        console.error('  ❌ Failed to set Uniswap fee tier:', e.message);
      }
    }

    if (settings.use_curve !== undefined && settings.curve_index_in !== undefined && settings.curve_index_out !== undefined) {
      try {
        const tx1 = await executor.setCurveIndices(settings.curve_index_in, settings.curve_index_out);
        console.log('  ⏳ Setting Curve indices...');
        const receipt1 = await tx1.wait();
        transactions.push({
          function: 'setCurveIndices',
          hash: receipt1.hash,
          blockNumber: receipt1.blockNumber,
          gasUsed: receipt1.gasUsed.toString()
        });
        console.log('  ✅ Curve indices set:', settings.curve_index_in, '->', settings.curve_index_out);
        console.log('  📝 Transaction:', receipt1.hash);

        const tx2 = await executor.setUseCurveStabilization(settings.use_curve);
        console.log('  ⏳ Setting Curve stabilization...');
        const receipt2 = await tx2.wait();
        transactions.push({
          function: 'setUseCurveStabilization',
          hash: receipt2.hash,
          blockNumber: receipt2.blockNumber,
          gasUsed: receipt2.gasUsed.toString()
        });
        console.log('  ✅ Curve stabilization:', settings.use_curve ? 'enabled' : 'disabled');
        console.log('  📝 Transaction:', receipt2.hash);
      } catch (e) {
        console.error('  ❌ Failed to set Curve settings:', e.message);
      }
    }

    console.log('\n✅ Governance settings applied successfully!');
    
    // Get the first transaction hash and block number (most recent)
    const lastTransaction = transactions.length > 0 ? transactions[transactions.length - 1] : null;
    const totalGasUsed = transactions.reduce((sum, tx) => sum + BigInt(tx.gasUsed || '0'), BigInt(0));
    
    // Save transaction status with all transaction details
    const statusPath = path.join(__dirname, '../../storage/app/governance-apply-status.json');
    fs.writeFileSync(statusPath, JSON.stringify({
      status: 'success',
      timestamp: new Date().toISOString(),
      applied_by: signer.address,
      contract_address: contractAddress,
      transaction_hash: lastTransaction?.hash || null,
      block_number: lastTransaction?.blockNumber || null,
      gas_used: totalGasUsed.toString(),
      transactions: transactions
    }, null, 2));

  } catch (error) {
    console.error('\n❌ Error applying governance settings:', error);
    
    // Save error status
    const statusPath = path.join(__dirname, '../../storage/app/governance-apply-status.json');
    fs.writeFileSync(statusPath, JSON.stringify({
      status: 'failed',
      error: error.message,
      timestamp: new Date().toISOString(),
      applied_by: signer.address,
      contract_address: contractAddress
    }, null, 2));
    
    process.exitCode = 1;
  }
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
