<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JetRound extends Model
{
    protected $table = 'jet_rounds';

    protected $fillable = [
        'round_id',
        'crash_multiplier',
        'started_at',
        'ended_at',
        'status',
        'manual_override',
    ];

    protected $casts = [
        'crash_multiplier' => 'float',
        'manual_override' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function bets()
    {
        return $this->hasMany(JetBet::class, 'jet_round_id');
    }
}
