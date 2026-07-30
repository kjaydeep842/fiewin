<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameResult extends Model
{
    protected $fillable = [
        'game_id',
        'period_number',
        'result_data',
        'provably_fair_hash',
        'seed',
        'status',
        'manual_override',
        'settled_at',
    ];

    protected $casts = [
        'result_data' => 'array',
        'manual_override' => 'boolean',
        'settled_at' => 'datetime',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function bets()
    {
        return $this->hasMany(GameBet::class);
    }
}
