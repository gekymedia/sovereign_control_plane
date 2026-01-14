<?php

namespace App\Jobs;

use App\Models\Scenario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;

/**
 * Job to execute scenario files using the Node runner and generate charts.
 *
 * The job will mark scenarios as running, execute the Node script
 * (processing all scenario files), then mark the scenarios as
 * completed or failed depending on exit status. Finally it runs
 * the Python plotter to generate dashboard images.
 */
class RunScenarioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2; // Lower tries for scenario execution as it's more expensive

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 120; // 2 minutes for scenario execution

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Illuminate\Support\Facades\Log::info('RunScenarioJob: Starting scenario execution');

        // Mark all pending scenarios as running
        $pendingCount = Scenario::where('status', 'pending')->count();
        Scenario::where('status', 'pending')->update(['status' => 'running']);
        \Illuminate\Support\Facades\Log::info('RunScenarioJob: Marked scenarios as running', ['count' => $pendingCount]);

        // Execute Node scenario runner
        $process = new Process(['node', 'hardhat/scripts/finalScenarioRunner.js'], base_path());
        $process->setTimeout(600); // 10 minutes timeout
        $process->setEnv([
            'CONTRACT_ADDRESS' => env('CONTRACT_ADDRESS', ''),
            'SEPOLIA_RPC_URL' => env('SEPOLIA_RPC_URL', ''),
            'SEPOLIA_PRIVATE_KEY' => env('SEPOLIA_PRIVATE_KEY', ''),
        ]);

        $exitCode = 0;
        $output = '';
        $errorOutput = '';

        try {
            $process->run(function ($type, $buffer) use (&$output, &$errorOutput) {
                if ($type === Process::ERR) {
                    $errorOutput .= $buffer;
                    \Illuminate\Support\Facades\Log::warning('RunScenarioJob: ' . trim($buffer));
                } else {
                    $output .= $buffer;
                    \Illuminate\Support\Facades\Log::info('RunScenarioJob: ' . trim($buffer));
                }
            });
            $exitCode = $process->getExitCode();
        } catch (\Throwable $e) {
            $exitCode = 1;
            \Illuminate\Support\Facades\Log::error('RunScenarioJob: Exception during scenario execution', [
                'message' => $e->getMessage(),
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries,
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry if attempts remain
            if ($this->attempts() < $this->tries) {
                // Reset scenarios back to pending for retry
                Scenario::where('status', 'running')->update(['status' => 'pending']);
                throw $e;
            }
        }

        // Update scenario statuses
        $newStatus = $exitCode === 0 ? 'completed' : 'failed';
        $updatedCount = Scenario::where('status', 'running')->update(['status' => $newStatus]);
        \Illuminate\Support\Facades\Log::info('RunScenarioJob: Updated scenario statuses', [
            'status' => $newStatus,
            'count' => $updatedCount,
        ]);

        if ($exitCode !== 0) {
            \Illuminate\Support\Facades\Log::error('RunScenarioJob: Scenario execution failed', [
                'exit_code' => $exitCode,
                'output' => $output,
                'error_output' => $errorOutput,
            ]);
        }

        // Generate dashboard plots
        try {
            \Illuminate\Support\Facades\Log::info('RunScenarioJob: Generating dashboard plots');
            $plotProcess = new Process(['python3', 'hardhat/scripts/dashboardPlot.py'], base_path());
            $plotProcess->setTimeout(300); // 5 minutes timeout
            $plotProcess->run(function ($type, $buffer) {
                \Illuminate\Support\Facades\Log::info('RunScenarioJob: Plot ' . trim($buffer));
            });

            if ($plotProcess->getExitCode() !== 0) {
                \Illuminate\Support\Facades\Log::warning('RunScenarioJob: Plot generation may have failed', [
                    'exit_code' => $plotProcess->getExitCode(),
                    'output' => $plotProcess->getOutput(),
                    'error' => $plotProcess->getErrorOutput(),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('RunScenarioJob: Exception during plot generation', [
                'message' => $e->getMessage(),
            ]);
            // Continue even if plot generation fails
        }

        // After charts are generated into storage/app, copy them into the
        // public disk so they can be served via the web.  The Python
        // script writes files directly into storage/app.  Using the
        // Storage facade allows for portability across filesystems.
        $chartFiles = ['overlaytierdistribution.png', 'asset_outcomes.png', 'governance_status.png'];
        $copiedCount = 0;
        foreach ($chartFiles as $chart) {
            $source = storage_path('app'.DIRECTORY_SEPARATOR.$chart);
            if (file_exists($source)) {
                try {
                    // Read from local disk and write to public disk
                    $contents = file_get_contents($source);
                    \Illuminate\Support\Facades\Storage::disk('public')->put($chart, $contents);
                    $copiedCount++;
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('RunScenarioJob: Failed to copy chart', [
                        'chart' => $chart,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        \Illuminate\Support\Facades\Log::info('RunScenarioJob: Completed', [
            'scenarios_status' => $newStatus,
            'charts_copied' => $copiedCount,
        ]);
    }

    /**
     * Handle a job failure after all retry attempts are exhausted.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error('RunScenarioJob: Job failed permanently after all retries', [
            'message' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'max_tries' => $this->tries,
            'trace' => $exception->getTraceAsString(),
        ]);

        // Mark running scenarios as failed
        Scenario::where('status', 'running')->update(['status' => 'failed']);
        
        // Also mark any pending scenarios that might have been reset
        Scenario::where('status', 'pending')->update(['status' => 'failed']);
    }
}