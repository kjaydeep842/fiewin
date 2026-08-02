<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AndarBaharRound extends Model
{
    protected $table = 'andar_bahar_rounds';

    protected $fillable = [
        'period_number',
        'open_card',
        'winner',
        'deal_sequence',
        'matching_card',
        'deal_count',
        'status',
        'manual_override',
        'started_at',
        'settled_at',
    ];

    protected $casts = [
        'deal_sequence' => 'array',
        'deal_count' => 'integer',
        'manual_override' => 'boolean',
        'started_at' => 'datetime',
        'settled_at' => 'datetime',
    ];

    public function bets()
    {
        return $this->hasMany(AndarBaharBet::class, 'andar_bahar_round_id');
    }
}
