require('@nomicfoundation/hardhat-toolbox');

/**
 * Hardhat configuration for the SovereignFlashLoanExecutor contract.
 *
 * This file wires in the Solidity version, Sepolia testnet and optional
 * Etherscan configuration. Populate the environment variables in your
 * .env file or replace the placeholders below to deploy. The project
 * structure mirrors the example provided in the project briefing.
 */
module.exports = {
  solidity: '0.8.17',
  networks: {
    sepolia: {
      url: process.env.SEPOLIA_RPC_URL || 'https://sepolia.infura.io/v3/YOUR_INFURA_PROJECT_ID',
      accounts: process.env.SEPOLIA_PRIVATE_KEY ? [process.env.SEPOLIA_PRIVATE_KEY] : ['0xYOURPRIVATEKEY']
    }
  },
  etherscan: {
    apiKey: process.env.ETHERSCAN_API_KEY || 'YOUR_ETHERSCAN_API_KEY'
  }
};