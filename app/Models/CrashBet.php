<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrashBet extends Model
{
    use HasFactory;

    protected $fillable = [
        'crash_round_id',
        'round_id',
        'user_id',
        'bet_amount',
        'cashout_multiplier',
        'profit',
        'status',
    ];

    protected $casts = [
        'bet_amount' => 'float',
        'cashout_multiplier' => 'float',
        'profit' => 'float',
    ];

    public function round()
    {
        return $this->belongsTo(CrashRound::class, 'crash_round_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
