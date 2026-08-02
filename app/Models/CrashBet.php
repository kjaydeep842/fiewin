<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrashBet extends Model
{
    protected $table = 'crash_bets';

    protected $fillable = [
        'crash_round_id',
        'round_id',
        'user_id',
        'bet_amount',
        'auto_cashout',
        'cashout_multiplier',
        'profit',
        'status',
    ];

    protected $casts = [
        'bet_amount' => 'float',
        'auto_cashout' => 'float',
        'cashout_multiplier' => 'float',
        'profit' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function round()
    {
        return $this->belongsTo(CrashRound::class, 'crash_round_id');
    }
}
