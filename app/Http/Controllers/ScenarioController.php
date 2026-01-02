<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use App\Models\Scenario;
use App\Jobs\RunScenarioJob;

/**
 * Controller handling scenario uploads, execution and reporting.
 *
 * Scenarios are JSON files that configure governance parameters and a set
 * of assets for flash‑loan execution. They are stored in
 * storage/app/configs and consumed by the Node scenario runner. After
 * execution the runner generates a report (scenarioReport.json) and
 * Python script produces charts for the dashboard. Jobs are dispatched
 * asynchronously to avoid blocking the request.
 */
class ScenarioController extends Controller
{
    /**
     * Display a list of scenarios and the current report summary.
     */
    public function index()
    {
        $scenarios = Scenario::all();
        $report = [];
        if (Storage::exists('scenarioReport.json')) {
            $report = json_decode(Storage::get('scenarioReport.json'), true) ?? [];
        }
        return view('scenarios.index', [
            'scenarios' => $scenarios,
            'report' => $report,
        ]);
    }

    /**
     * Handle an uploaded scenario JSON file.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'scenario' => 'required|file|mimes:json|max:10240', // Max 10MB
        ]);

        $file = $request->file('scenario');
        $name = $file->getClientOriginalName();

        try {
            // Validate JSON structure
            $content = file_get_contents($file->getRealPath());
            $scenarioData = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()->withErrors([
                    'scenario' => 'Invalid JSON file: ' . json_last_error_msg()
                ]);
            }

            // Validate required fields
            $requiredFields = ['contractAddress', 'primaryWallet', 'overlayTier', 'expectedRate', 'maxOracleDelay', 'assets'];
            $missingFields = [];

            foreach ($requiredFields as $field) {
                if (!isset($scenarioData[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                return redirect()->back()->withErrors([
                    'scenario' => 'Missing required fields: ' . implode(', ', $missingFields)
                ]);
            }

            // Validate overlay tier value
            $validOverlayTiers = [0, 1, 2]; // None, ChaoticTier13, SovereignTierX
            if (!in_array($scenarioData['overlayTier'], $validOverlayTiers, true)) {
                return redirect()->back()->withErrors([
                    'scenario' => 'Invalid overlayTier. Must be 0 (None), 1 (ChaoticTier13), or 2 (SovereignTierX)'
                ]);
            }

            // Validate assets array
            if (!is_array($scenarioData['assets']) || empty($scenarioData['assets'])) {
                return redirect()->back()->withErrors([
                    'scenario' => 'Assets must be a non-empty array'
                ]);
            }

            // Validate wallet addresses (basic format check)
            if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $scenarioData['contractAddress'])) {
                return redirect()->back()->withErrors([
                    'scenario' => 'Invalid contract address format'
                ]);
            }

            if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $scenarioData['primaryWallet'])) {
                return redirect()->back()->withErrors([
                    'scenario' => 'Invalid primary wallet address format'
                ]);
            }

            // Store the file
            Storage::putFileAs('configs', $file, $name);

            // Create database record
            Scenario::create(['file_name' => $name, 'status' => 'pending']);

            \Illuminate\Support\Facades\Log::info('Scenario uploaded successfully', [
                'file_name' => $name,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('status', 'Scenario uploaded and validated successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Scenario upload failed', [
                'file_name' => $name,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors([
                'scenario' => 'Failed to upload scenario: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Run all scenarios using the Node runner and generate charts.
     */
    public function run(Request $request)
    {
        // Check if there are any pending scenarios
        $pendingScenarios = Scenario::where('status', 'pending')->count();
        $runningScenarios = Scenario::where('status', 'running')->count();

        if ($pendingScenarios === 0 && $runningScenarios === 0) {
            return redirect()->back()->withErrors([
                'scenarios' => 'No scenarios available to run. Please upload a scenario first.'
            ]);
        }

        if ($runningScenarios > 0) {
            return redirect()->back()->withErrors([
                'scenarios' => 'Scenarios are already running. Please wait for them to complete.'
            ]);
        }

        try {
            // Dispatch asynchronous job to run scenarios
            RunScenarioJob::dispatch();

            \Illuminate\Support\Facades\Log::info('Scenario execution started', [
                'pending_count' => $pendingScenarios,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('status', "Started running {$pendingScenarios} scenario(s) in the background. Refresh later for results.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to start scenario execution', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors([
                'scenarios' => 'Failed to start scenario execution: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete a scenario file and its database record.
     */
    public function destroy(Scenario $scenario)
    {
        // Remove the file from storage if it exists
        $path = 'configs/'.$scenario->file_name;
        if (Storage::exists($path)) {
            Storage::delete($path);
        }
        $scenario->delete();
        return redirect()->back()->with('status', 'Scenario deleted successfully.');
    }
}