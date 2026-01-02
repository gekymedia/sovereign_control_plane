<?php

namespace App\Jobs;

use App\Models\GovernanceSetting;
use App\Models\GovernanceTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Job to apply governance settings to the blockchain asynchronously.
 * 
 * This job reads the latest governance settings from the database,
 * exports them to a JSON file, and executes the Node.js script to
 * apply them on-chain. The job runs in the background to avoid
 * blocking the web request.
 */
class ApplyGovernanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The governance setting ID to apply.
     *
     * @var int|null
     */
    public $governanceSettingId;

    /**
     * Create a new job instance.
     *
     * @param int|null $governanceSettingId
     */
    public function __construct(?int $governanceSettingId = null)
    {
        $this->governanceSettingId = $governanceSettingId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Get the latest governance setting or the specified one
            if ($this->governanceSettingId) {
                $setting = GovernanceSetting::find($this->governanceSettingId);
            } else {
                $setting = GovernanceSetting::latest()->first();
            }

            if (!$setting) {
                Log::error('ApplyGovernanceJob: No governance settings found');
                return;
            }

            // Create transaction record
            $transaction = GovernanceTransaction::create([
                'governance_setting_id' => $setting->id,
                'status' => GovernanceTransaction::STATUS_PROCESSING,
            ]);

            Log::info('ApplyGovernanceJob: Applying governance settings', [
                'setting_id' => $setting->id,
                'transaction_id' => $transaction->id,
                'overlay_tier' => $setting->overlay_tier,
            ]);

            // Export settings to JSON file for Node.js script
            $settingsData = [
                'id' => $setting->id,
                'overlay_tier' => $setting->overlay_tier,
                'expected_rate' => (float) $setting->expected_rate,
                'max_oracle_delay' => (int) $setting->max_oracle_delay,
                'use_curve' => (bool) $setting->use_curve,
                'uni_fee_tier' => (int) $setting->uni_fee_tier,
                'curve_index_in' => (int) $setting->curve_index_in,
                'curve_index_out' => (int) $setting->curve_index_out,
                'loan_asset' => $setting->loan_asset,
                'profit_wallet' => $setting->profit_wallet,
                'fallback_wallet' => $setting->fallback_wallet,
                'contract_address' => env('CONTRACT_ADDRESS'),
                'created_at' => $setting->created_at->toISOString(),
            ];

            // Save to storage for Node.js script to read
            Storage::put('governance-latest.json', json_encode($settingsData, JSON_PRETTY_PRINT));

            // Execute the Node.js script to apply settings on-chain
            $process = new Process(['node', 'hardhat/scripts/applyGovernance.js'], base_path());
            $process->setTimeout(300); // 5 minutes timeout
            $process->setEnv([
                'CONTRACT_ADDRESS' => env('CONTRACT_ADDRESS', ''),
                'SEPOLIA_RPC_URL' => env('SEPOLIA_RPC_URL', ''),
                'SEPOLIA_PRIVATE_KEY' => env('SEPOLIA_PRIVATE_KEY', ''),
            ]);

            $process->run(function ($type, $buffer) {
                Log::info('ApplyGovernanceJob: ' . trim($buffer));
            });

            $exitCode = $process->getExitCode();

            // Try to read transaction status from the status file
            $statusFile = storage_path('app/governance-apply-status.json');
            $statusData = null;
            if (file_exists($statusFile)) {
                $statusData = json_decode(file_get_contents($statusFile), true);
            }

            if ($exitCode === 0 && $statusData && $statusData['status'] === 'success') {
                $transaction->update([
                    'status' => GovernanceTransaction::STATUS_SUCCESS,
                    'applied_by' => $statusData['applied_by'] ?? null,
                    // Note: transaction_hash and block_number would need to be extracted
                    // from the Node.js script output or by querying the blockchain
                ]);

                Log::info('ApplyGovernanceJob: Successfully applied governance settings', [
                    'setting_id' => $setting->id,
                    'transaction_id' => $transaction->id,
                ]);
            } else {
                $errorMessage = $statusData['error'] ?? $process->getErrorOutput() ?? 'Unknown error';
                
                $transaction->update([
                    'status' => GovernanceTransaction::STATUS_FAILED,
                    'error_message' => $errorMessage,
                ]);

                Log::error('ApplyGovernanceJob: Failed to apply governance settings', [
                    'setting_id' => $setting->id,
                    'transaction_id' => $transaction->id,
                    'exit_code' => $exitCode,
                    'output' => $process->getOutput(),
                    'error_output' => $process->getErrorOutput(),
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('ApplyGovernanceJob: Exception occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ApplyGovernanceJob: Job failed permanently', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
