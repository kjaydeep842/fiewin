<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JetResult extends Model
{
    protected $table = 'jet_results';

    protected $fillable = [
        'round_id',
        'crash_multiplier',
        'provably_fair_hash',
        'seed',
        'settled_at',
    ];

    protected $casts = [
        'crash_multiplier' => 'float',
        'settled_at' => 'datetime',
    ];
}
