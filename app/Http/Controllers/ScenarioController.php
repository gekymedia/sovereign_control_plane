<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

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
        $files = collect(Storage::files('configs'))
            ->filter(fn ($file) => Str::endsWith($file, '.json'))
            ->map(fn ($file) => basename($file));

        $report = [];
        if (Storage::exists('scenarioReport.json')) {
            $report = json_decode(Storage::get('scenarioReport.json'), true) ?? [];
        }

        return view('scenarios.index', [
            'files' => $files,
            'report' => $report,
        ]);
    }

    /**
     * Handle an uploaded scenario JSON file.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'scenario' => 'required|file|mimes:json',
        ]);
        $file = $request->file('scenario');
        $name = $file->getClientOriginalName();
        Storage::putFileAs('configs', $file, $name);
        return redirect()->back()->with('status', 'Scenario uploaded successfully.');
    }

    /**
     * Run all scenarios using the Node runner and generate charts.
     */
    public function run(Request $request)
    {
        // Build the command to run the Node scenario runner
        $process = new Process(['node', 'hardhat/scripts/finalScenarioRunner.js'], base_path());
        $process->setTimeout(null);
        $process->start();

        // After the Node runner finishes we generate charts via Python
        $process->wait();

        $chartProcess = new Process(['python3', 'hardhat/scripts/dashboardPlot.py'], base_path());
        $chartProcess->setTimeout(null);
        $chartProcess->run();

        return redirect()->back()->with('status', 'Scenarios executed. Reports generated.');
    }
}