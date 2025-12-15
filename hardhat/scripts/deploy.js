async function main() {
  const SovereignFlashLoanExecutor = await ethers.getContractFactory('SovereignFlashLoanExecutor');
  const executor = await SovereignFlashLoanExecutor.deploy();
  await executor.deployed();
  console.log('✅ SovereignFlashLoanExecutor deployed to:', executor.address);
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});