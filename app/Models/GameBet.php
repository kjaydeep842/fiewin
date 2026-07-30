<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameBet extends Model
{
    protected $fillable = [
        'user_id',
        'game_id',
        'game_result_id',
        'period_number',
        'bet_amount',
        'bet_type',
        'win_amount',
        'multiplier',
        'cashout_multiplier',
        'status',
        'bet_details',
    ];

    protected $casts = [
        'bet_amount' => 'decimal:2',
        'win_amount' => 'decimal:2',
        'multiplier' => 'decimal:2',
        'cashout_multiplier' => 'decimal:2',
        'bet_details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function result()
    {
        return $this->belongsTo(GameResult::class, 'game_result_id');
    }
}
