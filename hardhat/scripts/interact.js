const { ethers } = require('hardhat');

async function main() {
  // Replace with your deployed contract address on Sepolia
  const contractAddress = process.env.CONTRACT_ADDRESS || '0xYOURDEPLOYEDCONTRACT';
  const executor = await ethers.getContractAt('SovereignFlashLoanExecutor', contractAddress);

  // --- Step 1: Set profit wallets ---
  const primary = process.env.PRIMARY_WALLET || '0xYourPrimaryWallet';
  const fallback = process.env.FALLBACK_WALLET || '0xYourFallbackWallet';
  let tx = await executor.updateProfitWallet(primary);
  await tx.wait();
  console.log('✅ Primary profit wallet set');
  tx = await executor.setFallbackWallet(fallback);
  await tx.wait();
  console.log('✅ Fallback wallet set');

  // --- Step 2: Trigger a flash loan ---
  const amount = ethers.parseUnits('1000', 6); // Borrow 1000 USDC
  tx = await executor.initiateFlashLoan(amount);
  await tx.wait();
  console.log('🚀 Flash loan initiated');
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});