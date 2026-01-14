<?php

namespace Tests\Feature;

use App\Models\GovernanceSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GovernanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that governance panel requires admin role.
     */
    public function test_governance_panel_requires_admin_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $oracle = User::factory()->create(['role' => 'oracle']);
        $oracle->assignRole('oracle');

        // Admin can access
        $response = $this->actingAs($admin)->get('/governance');
        $response->assertStatus(200);

        // Oracle cannot access
        $response = $this->actingAs($oracle)->get('/governance');
        $response->assertStatus(403);
    }

    /**
     * Test that admin can update governance settings.
     */
    public function test_admin_can_update_governance_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post('/governance', [
            'overlay_tier' => 'none',
            'expected_rate' => 3000.00,
            'max_oracle_delay' => 300,
            'use_curve' => false,
            'uni_fee_tier' => 3000,
            'curve_index_in' => 1,
            'curve_index_out' => 0,
            'loan_asset' => '0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606EB48',
            'profit_wallet' => '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb',
            'fallback_wallet' => null,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('governance_settings', [
            'overlay_tier' => 'none',
            'expected_rate' => 3000.00,
        ]);
    }

    /**
     * Test that governance settings validation works.
     */
    public function test_governance_settings_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post('/governance', [
            'overlay_tier' => 'invalid',
            'expected_rate' => 'not-a-number',
        ]);

        $response->assertSessionHasErrors(['overlay_tier', 'expected_rate']);
    }
}


