<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Controller for the governance control plane.
 *
 * Provides UI controls for overlay tiers, oracle settings, curve stabilisation,
 * fee tier selection, loan asset selection and profit routing. All settings
 * are persisted to a JSON file in storage to avoid the complexity of
 * database migrations for configuration. In a production system these
 * would likely be stored in a dedicated table or via a settings service.
 */
class GovernanceController extends Controller
{
    /**
     * Show the governance panel.
     */
    public function index()
    {
        $settings = [];
        if (Storage::exists('governance.json')) {
            $settings = json_decode(Storage::get('governance.json'), true) ?? [];
        }

        return view('governance.index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Persist governance settings and optionally push them on-chain.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'overlay_tier' => 'required|in:none,ChaoticTier13,SovereignTierX',
            'expected_rate' => 'required|numeric',
            'max_oracle_delay' => 'required|integer',
            'use_curve' => 'nullable|boolean',
            'uni_fee_tier' => 'required|integer',
            'curve_index_in' => 'required|integer',
            'curve_index_out' => 'required|integer',
            'profit_wallet' => 'required|string',
            'fallback_wallet' => 'nullable|string',
            'loan_asset' => 'required|string',
        ]);

        // Persist settings to JSON file
        Storage::put('governance.json', json_encode($data, JSON_PRETTY_PRINT));

        // In a real system this is where contract calls would be executed. We
        // provide a placeholder using Symfony\Process to call a Node script
        // (e.g. hardhat tasks) asynchronously. The script should read
        // governance.json and apply settings via the deployed contract.
        //
        // Example:
        // $process = new Process(['node', 'hardhat/scripts/applyGovernance.js']);
        // $process->start();

        return redirect()->back()->with('status', 'Governance settings updated.');
    }
}