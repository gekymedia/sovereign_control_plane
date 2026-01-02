<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * GovernanceSetting model
 *
 * Stores configuration for overlay tier, oracle expectations and loan
 * parameters. Only one record is used in the system; if multiple
 * records exist the most recently created will be considered active.
 */
class GovernanceSetting extends Model
{
    protected $fillable = [
        'overlay_tier',
        'expected_rate',
        'max_oracle_delay',
        'use_curve',
        'uni_fee_tier',
        'curve_index_in',
        'curve_index_out',
        'loan_asset',
        'profit_wallet',
        'fallback_wallet',
    ];
}