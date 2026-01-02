<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Scenario model
 *
 * Represents an uploaded scenario configuration file used for executing
 * flash‑loan strategies. Status reflects whether the scenario has been
 * executed and can be pending, running, completed or failed.
 */
class Scenario extends Model
{
    protected $fillable = [
        'file_name',
        'status',
    ];
}