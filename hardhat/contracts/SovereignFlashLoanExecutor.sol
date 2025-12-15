// SPDX-License-Identifier: MIT
pragma solidity ^0.8.17;

import "@aave/core-v3/contracts/flashloan/interfaces/IFlashLoanReceiver.sol";
import "@aave/core-v3/contracts/flashloan/interfaces/IPoolAddressesProvider.sol";
import "@aave/core-v3/contracts/flashloan/interfaces/IPool.sol";
import "@openzeppelin/contracts/token/ERC20/IERC20.sol";
import "@chainlink/contracts/src/v0.8/interfaces/AggregatorV3Interface.sol";
import "@uniswap/v3-periphery/contracts/interfaces/ISwapRouter.sol";

// Minimal Curve 3pool interface (DAI/USDC/USDT). Indices: DAI=0, USDC=1, USDT=2
interface ICurvePool {
    function exchange(int128 i, int128 j, uint256 dx, uint256 min_dy) external returns (uint256);
}

contract SovereignFlashLoanExecutor is IFlashLoanReceiver {
    // --- Network constants (Ethereum mainnet) ---
    address public constant WETH = 0xC02aaA39b223FE8D0A0e5C4F27eAD9083C756Cc2;
    address public constant USDC = 0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606EB48;
    address public constant UNISWAP_V3_ROUTER = 0xE592427A0AEce92De3Edee1F18E0157C05861564;
    address public constant AAVE_V3_PROVIDER = 0xA97684ead0e402dC232d5A977953DF7ECBaB3CDb;
    address public constant CURVE_3POOL = 0xbEbc44782C7dB0a1A60Cb6fe97d0b483032FF1C7;
    address public constant CHAINLINK_ETH_USD = 0x5f4eC3Df9cbd43714FE2740f5E3616155c5b8419;

    // --- Aave core ---
    IPoolAddressesProvider public immutable ADDRESSES_PROVIDER;
    IPool public immutable POOL;

    // --- Routers & Oracles ---
    ISwapRouter public immutable uniV3Router;
    ICurvePool public immutable curvePool;
    AggregatorV3Interface public priceFeed;

    // --- Roles ---
    address public admin;
    address public oracle;

    // --- Profit routing ---
    address public currentProfitWallet;
    address public fallbackProfitWallet;

    // --- Overlay state ---
    bool public launchDetected;
    bool public arbitrageDetected;
    uint256 public expectedRate;            // governance-set min acceptable oracle rate (ETH/USD)
    uint256 public maxOracleDelaySeconds;   // freshness threshold (e.g., 300)

    enum OverlayTier { None, ChaoticTier13, SovereignTierX }
    OverlayTier public activeOverlay;

    // --- Execution prefs (governance-tunable) ---
    uint24 public uniFeeTier;               // e.g., 3000 for 0.3% pools
    int128 public curveIndexIn;             // index in Curve 3pool (DAI=0, USDC=1, USDT=2)
    int128 public curveIndexOut;            // index out in Curve 3pool
    bool public useCurveStabilization;      // optional USDC stabilization via 3pool

    // --- Loan asset (default USDC; can be governed) ---
    address public loanAsset;

    // --- Events ---
    event ProfitRouted(address indexed to, uint256 amount, address asset);
    event FallbackWalletActivated(address indexed fallback);
    event OverrideBurstExecuted(address asset, uint256 amount);
    event LaunchStatusUpdated(bool status);
    event ArbitrageStatusUpdated(bool status);
    event ExpectedRateUpdated(uint256 rate);
    event OverlayTierUpdated(OverlayTier tier);
    event MaxOracleDelayUpdated(uint256 secondsDelay);
    event UniFeeTierUpdated(uint24 fee);
    event CurveIndicesUpdated(int128 i, int128 j);
    event UseCurveStabilizationUpdated(bool enabled);
    event LoanAssetUpdated(address asset);

    constructor() {
        // Wire core contracts
        ADDRESSES_PROVIDER = IPoolAddressesProvider(AAVE_V3_PROVIDER);
        POOL = IPool(ADDRESSES_PROVIDER.getPool());

        uniV3Router = ISwapRouter(UNISWAP_V3_ROUTER);
        curvePool = ICurvePool(CURVE_3POOL);
        priceFeed = AggregatorV3Interface(CHAINLINK_ETH_USD);

        // Roles & wallets
        admin = msg.sender;
        currentProfitWallet = msg.sender;

        // Defaults
        loanAsset = USDC;
        maxOracleDelaySeconds = 300;   // 5 minutes
        uniFeeTier = 3000;             // 0.3%
        curveIndexIn = 1;              // USDC
        curveIndexOut = 0;             // DAI
        useCurveStabilization = false; // off by default
    }

    // --- Modifiers ---
    modifier onlyAdmin() {
        require(msg.sender == admin, "Not authorized");
        _;
    }

    modifier onlyOracle() {
        require(msg.sender == oracle, "Not authorized oracle");
        _;
    }

    // --- Admin & Oracle wiring ---
    function setOracle(address _oracle) external onlyAdmin {
        require(_oracle != address(0), "Invalid oracle");
        oracle = _oracle;
    }

    function transferAdmin(address newAdmin) external onlyAdmin {
        require(newAdmin != address(0), "Invalid admin");
        admin = newAdmin;
    }

    // --- Governance: state signals ---
    function fulfillLaunchStatus(bool status) external onlyOracle {
        launchDetected = status;
        emit LaunchStatusUpdated(status);
    }

    function fulfillArbitrageStatus(bool status) external onlyOracle {
        arbitrageDetected = status;
        emit ArbitrageStatusUpdated(status);
    }

    function setOverlayTier(OverlayTier tier) external onlyOracle {
        activeOverlay = tier;
        emit OverlayTierUpdated(tier);
    }

    function isSovereignTierXActive() public view returns (bool) {
        return launchDetected && arbitrageDetected && activeOverlay == OverlayTier.SovereignTierX;
    }

    // --- Oracle controls ---
    function setExpectedRate(uint256 rate) external onlyOracle {
        expectedRate = rate;
        emit ExpectedRateUpdated(rate);
    }

    function setMaxOracleDelay(uint256 secondsDelay) external onlyOracle {
        maxOracleDelaySeconds = secondsDelay;
        emit MaxOracleDelayUpdated(secondsDelay);
    }

    function _isPriceFresh(uint256 maxDelay) internal view returns (bool) {
        (, , , uint256 updatedAt, ) = priceFeed.latestRoundData();
        return block.timestamp - updatedAt <= maxDelay;
    }

    // --- Execution tuning ---
    function setUniFeeTier(uint24 fee) external onlyAdmin {
        uniFeeTier = fee;
        emit UniFeeTierUpdated(fee);
    }

    function setCurveIndices(int128 i, int128 j) external onlyAdmin {
        curveIndexIn = i;
        curveIndexOut = j;
        emit CurveIndicesUpdated(i, j);
    }

    function setUseCurveStabilization(bool enabled) external onlyAdmin {
        useCurveStabilization = enabled;
        emit UseCurveStabilizationUpdated(enabled);
    }

    // --- Loan asset controls ---
    function setLoanAsset(address asset) external onlyAdmin {
        require(asset != address(0), "Invalid asset");
        loanAsset = asset;
        emit LoanAssetUpdated(asset);
    }

    // --- Profit routing controls ---
    function updateProfitWallet(address newWallet) external onlyAdmin {
        require(newWallet != address(0), "Invalid address");
        currentProfitWallet = newWallet;
    }

    function setFallbackWallet(address _fallback) external onlyAdmin {
        require(_fallback != address(0), "Invalid fallback");
        fallbackProfitWallet = _fallback;
    }

    // --- Flash loan initiation (single-asset demo; multi-asset extendable) ---
    function initiateFlashLoan(uint256 amount) external onlyAdmin {
        address[] memory assets = new address[](1);
        assets[0] = loanAsset;

        uint256[] memory amounts = new uint256[](1);
        amounts[0] = amount;

        uint256[] memory modes = new uint256[](1);
        modes[0] = 0; // no debt, full repayment

        POOL.flashLoan(address(this), assets, amounts, modes, address(this), "", 0);
    }

    // --- Aave callback ---
    function executeOperation(
        address[] calldata assets,
        uint256[] calldata amounts,
        uint256[] calldata premiums,
        address initiator,
        bytes calldata /*params*/
    ) external override returns (bool) {
        require(msg.sender == address(POOL), "Invalid pool caller");
        require(initiator == address(this), "Invalid initiator");

        // Overlay gating
        if (activeOverlay == OverlayTier.SovereignTierX) {
            require(isSovereignTierXActive(), "SovereignTierX conditions not met");
        } else if (activeOverlay == OverlayTier.ChaoticTier13) {
            require(launchDetected, "Launch not detected");
        }

        // Chainlink freshness
        require(_isPriceFresh(maxOracleDelaySeconds), "Stale Chainlink price feed");
        (, int256 price,,,) = priceFeed.latestRoundData();
        require(price > 0, "Invalid price feed");
        require(uint256(price) >= expectedRate, "Rate deviation too high");

        // Execute burst(s) using Uniswap V3 + optional Curve stabilization
        for (uint256 i = 0; i < assets.length; i++) {
            _executeOverrideBurst(assets[i], amounts[i]);
        }

        // Final step: ensure balances are reconciled back to loanAsset (USDC by default)
        _finalizeToLoanAsset();

        // Calculate profit on loanAsset (surplus over borrowed amount)
        uint256 profit = _calculateProfit(loanAsset, amounts[0]);
        _routeProfitToVault(loanAsset, profit);

        // Repay flash loan(s)
        for (uint256 i = 0; i < assets.length; i++) {
            uint256 totalRepayment = amounts[i] + premiums[i];
            IERC20(assets[i]).approve(address(POOL), totalRepayment);
        }

        return true;
    }

    // --- Override burst execution with Uniswap V3 + optional Curve 3pool stabilization ---
    function _executeOverrideBurst(address tokenIn, uint256 amountIn) internal {
        // If tokenIn is not USDC, first convert to USDC via Uniswap V3
        if (tokenIn != USDC) {
            IERC20(tokenIn).approve(address(uniV3Router), amountIn);
            uint256 minOutUSDC = _mutationAwareMinOut(tokenIn, USDC, amountIn);

            ISwapRouter.ExactInputSingleParams memory toUsdc = ISwapRouter.ExactInputSingleParams({
                tokenIn: tokenIn,
                tokenOut: USDC,
                fee: uniFeeTier,
                recipient: address(this),
                deadline: block.timestamp,
                amountIn: amountIn,
                amountOutMinimum: minOutUSDC,
                sqrtPriceLimitX96: 0
            });
            uniV3Router.exactInputSingle(toUsdc);
        }

        // Now USDC -> WETH via Uniswap V3 (meme burst path start)
        uint256 usdcBal = IERC20(USDC).balanceOf(address(this));
        if (usdcBal > 0) {
            IERC20(USDC).approve(address(uniV3Router), usdcBal);
            uint256 minOutWETH = _mutationAwareMinOut(USDC, WETH, usdcBal);

            ISwapRouter.ExactInputSingleParams memory toWeth = ISwapRouter.ExactInputSingleParams({
                tokenIn: USDC,
                tokenOut: WETH,
                fee: uniFeeTier,
                recipient: address(this),
                deadline: block.timestamp,
                amountIn: usdcBal,
                amountOutMinimum: minOutWETH,
                sqrtPriceLimitX96: 0
            });
            uint256 wethReceived = uniV3Router.exactInputSingle(toWeth);

            // WETH -> USDC via Curve 3pool is not native (3pool is stablecoins only).
            // So we convert WETH -> USDC back via Uniswap V3 (efficient).
            if (wethReceived > 0) {
                IERC20(WETH).approve(address(uniV3Router), wethReceived);
                uint256 minOutUSDC2 = _mutationAwareMinOut(WETH, USDC, wethReceived);

                ISwapRouter.ExactInputSingleParams memory toUsdc2 = ISwapRouter.ExactInputSingleParams({
                    tokenIn: WETH,
                    tokenOut: USDC,
                    fee: uniFeeTier,
                    recipient: address(this),
                    deadline: block.timestamp,
                    amountIn: wethReceived,
                    amountOutMinimum: minOutUSDC2,
                    sqrtPriceLimitX96: 0
                });
                uniV3Router.exactInputSingle(toUsdc2);
            }
        }

        // Optional: USDC stabilization via Curve 3pool (USDC ↔ DAI/USDT) if enabled
        if (useCurveStabilization) {
            uint256 usdcNow = IERC20(USDC).balanceOf(address(this));
            if (usdcNow > 0 && curveIndexIn == int128(1)) {
                IERC20(USDC).approve(address(curvePool), usdcNow);
                // Min_dy set to 0 here; tighten via governance thresholds if needed.
                curvePool.exchange(curveIndexIn, curveIndexOut, usdcNow, 0);
                // Optionally swap back to USDC if curveIndexOut != USDC (kept simple for demo).
            }
        }

        emit OverrideBurstExecuted(tokenIn, amountIn);
    }

    // --- Mutation-aware slippage floor (heuristic minOut) ---
    function _mutationAwareMinOut(address /*tokenIn*/, address /*tokenOut*/, uint256 amountIn)
        internal
        view
        returns (uint256)
    {
        // Heuristic: tier-based buffer on amountIn (use Quoter off-chain for exact expectedOut if desired)
        if (activeOverlay == OverlayTier.SovereignTierX) {
            return (amountIn * 97) / 100; // 3% buffer
        } else if (activeOverlay == OverlayTier.ChaoticTier13) {
            return (amountIn * 95) / 100; // 5% buffer
        }
        return (amountIn * 90) / 100;     // 10% buffer
    }

    // --- Final reconciliation to loanAsset (USDC default; generic logic supports other assets) ---
    function _finalizeToLoanAsset() internal {
        // If current balances include non-loan assets, convert to loanAsset via Uniswap V3.
        if (loanAsset == USDC) {
            // Example: convert any residual WETH to USDC
            uint256 wethBal = IERC20(WETH).balanceOf(address(this));
            if (wethBal > 0) {
                IERC20(WETH).approve(address(uniV3Router), wethBal);
                uint256 minOutUSDC = _mutationAwareMinOut(WETH, USDC, wethBal);

                ISwapRouter.ExactInputSingleParams memory toUsdc = ISwapRouter.ExactInputSingleParams({
                    tokenIn: WETH,
                    tokenOut: USDC,
                    fee: uniFeeTier,
                    recipient: address(this),
                    deadline: block.timestamp,
                    amountIn: wethBal,
                    amountOutMinimum: minOutUSDC,
                    sqrtPriceLimitX96: 0
                });
                uniV3Router.exactInputSingle(toUsdc);
            }
        } else {
            // If loanAsset is different, convert USDC -> loanAsset
            uint256 usdcBal = IERC20(USDC).balanceOf(address(this));
            if (usdcBal > 0) {
                IERC20(USDC).approve(address(uniV3Router), usdcBal);
                uint256 minOutLoan = _mutationAwareMinOut(USDC, loanAsset, usdcBal);

                ISwapRouter.ExactInputSingleParams memory toLoan = ISwapRouter.ExactInputSingleParams({
                    tokenIn: USDC,
                    tokenOut: loanAsset,
                    fee: uniFeeTier,
                    recipient: address(this),
                    deadline: block.timestamp,
                    amountIn: usdcBal,
                    amountOutMinimum: minOutLoan,
                    sqrtPriceLimitX96: 0
                });
                uniV3Router.exactInputSingle(toLoan);
            }
        }
    }

    // --- Profit calculation (surplus on loanAsset over borrowed amount) ---
    function _calculateProfit(address asset, uint256 borrowedAmount) internal view returns (uint256) {
        uint256 balance = IERC20(asset).balanceOf(address(this));
        return balance > borrowedAmount ? balance - borrowedAmount : 0;
    }

    // --- Profit routing with fallback ---
    function _routeProfitToVault(address asset, uint256 profit) internal {
        if (profit == 0) return;

        address target = currentProfitWallet != address(0) ? currentProfitWallet : fallbackProfitWallet;
        require(target != address(0), "No valid profit wallet set");

        IERC20(asset).transfer(target, profit);
        emit ProfitRouted(target, profit, asset);

        if (currentProfitWallet == address(0)) {
            emit FallbackWalletActivated(fallbackProfitWallet);
        }
    }
}