<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AndarBaharResult extends Model
{
    protected $table = 'andar_bahar_results';

    protected $fillable = [
        'period_number',
        'open_card',
        'winner',
        'deal_count',
        'winning_card',
        'result_data',
        'provably_fair_hash',
        'seed',
        'manual_override',
        'settled_at',
    ];

    protected $casts = [
        'deal_count' => 'integer',
        'result_data' => 'array',
        'manual_override' => 'boolean',
        'settled_at' => 'datetime',
    ];
}
