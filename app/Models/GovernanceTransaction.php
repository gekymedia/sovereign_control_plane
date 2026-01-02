<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * GovernanceTransaction model
 *
 * Tracks blockchain transactions for governance setting updates.
 * This provides an audit trail and allows tracking of transaction status.
 */
class GovernanceTransaction extends Model
{
    protected $fillable = [
        'governance_setting_id',
        'transaction_hash',
        'status',
        'block_number',
        'gas_used',
        'error_message',
        'applied_by',
    ];

    protected $casts = [
        'governance_setting_id' => 'integer',
        'block_number' => 'integer',
        'gas_used' => 'integer',
    ];

    /**
     * Get the governance setting that this transaction applies.
     */
    public function governanceSetting()
    {
        return $this->belongsTo(GovernanceSetting::class);
    }

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
}
