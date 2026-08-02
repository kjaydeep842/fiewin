<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AndarBaharBet extends Model
{
    protected $table = 'andar_bahar_bets';

    protected $fillable = [
        'user_id',
        'andar_bahar_round_id',
        'period_number',
        'bet_option',
        'bet_amount',
        'win_amount',
        'multiplier',
        'status',
        'transaction_id',
    ];

    protected $casts = [
        'bet_amount' => 'float',
        'win_amount' => 'float',
        'multiplier' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function round()
    {
        return $this->belongsTo(AndarBaharRound::class, 'andar_bahar_round_id');
    }
}
