<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use App\Models\GovernanceSetting;

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
        // Fetch the latest governance setting or create a default instance
        $settings = GovernanceSetting::latest()->first();
        if (!$settings) {
            $settings = new GovernanceSetting([
                'overlay_tier' => 'none',
                'expected_rate' => 0,
                'max_oracle_delay' => 300,
                'use_curve' => false,
                'uni_fee_tier' => 3000,
                'curve_index_in' => 1,
                'curve_index_out' => 0,
            ]);
        }
        return view('governance.index', ['settings' => $settings]);
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

        // Create a new governance setting record (immutable history)
        $setting = GovernanceSetting::create($data);

        // Dispatch job to apply settings on-chain asynchronously
        // This prevents blocking the web request while blockchain transactions are processed
        try {
            \App\Jobs\ApplyGovernanceJob::dispatch($setting->id);
            \Illuminate\Support\Facades\Log::info('Governance settings updated and queued for on-chain application', [
                'setting_id' => $setting->id,
                'user_id' => auth()->id(),
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the request - settings are saved in DB
            \Illuminate\Support\Facades\Log::error('Failed to queue governance application job', [
                'error' => $e->getMessage(),
                'setting_id' => $setting->id,
            ]);
        }

        return redirect()->back()->with('status', 'Governance settings updated. Applying to blockchain in background...');
    }
}