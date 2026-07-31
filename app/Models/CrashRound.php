<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrashRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'round_id',
        'crash_multiplier',
        'started_at',
        'ended_at',
        'status',
    ];

    protected $casts = [
        'crash_multiplier' => 'float',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function bets()
    {
        return $this->hasMany(CrashBet::class, 'crash_round_id');
    }
}
