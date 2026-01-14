<?php

namespace Tests\Unit\Models;

use App\Models\GovernanceSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GovernanceSettingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that governance setting can be created.
     */
    public function test_governance_setting_can_be_created(): void
    {
        $setting = GovernanceSetting::create([
            'overlay_tier' => 'none',
            'expected_rate' => 3000.00,
            'max_oracle_delay' => 300,
            'use_curve' => false,
            'uni_fee_tier' => 3000,
            'curve_index_in' => 1,
            'curve_index_out' => 0,
            'loan_asset' => '0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606EB48',
            'profit_wallet' => '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb',
        ]);

        $this->assertDatabaseHas('governance_settings', [
            'id' => $setting->id,
            'overlay_tier' => 'none',
            'expected_rate' => 3000.00,
        ]);
    }

    /**
     * Test that latest governance setting can be retrieved.
     */
    public function test_latest_governance_setting_can_be_retrieved(): void
    {
        GovernanceSetting::create([
            'overlay_tier' => 'none',
            'expected_rate' => 3000.00,
            'max_oracle_delay' => 300,
            'use_curve' => false,
            'uni_fee_tier' => 3000,
            'curve_index_in' => 1,
            'curve_index_out' => 0,
            'loan_asset' => '0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606EB48',
            'profit_wallet' => '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb',
        ]);

        // Create a newer setting
        $latest = GovernanceSetting::create([
            'overlay_tier' => 'ChaoticTier13',
            'expected_rate' => 3500.00,
            'max_oracle_delay' => 300,
            'use_curve' => false,
            'uni_fee_tier' => 3000,
            'curve_index_in' => 1,
            'curve_index_out' => 0,
            'loan_asset' => '0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606EB48',
            'profit_wallet' => '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb',
        ]);

        $retrieved = GovernanceSetting::latest()->first();

        $this->assertEquals($latest->id, $retrieved->id);
        $this->assertEquals('ChaoticTier13', $retrieved->overlay_tier);
    }
}


