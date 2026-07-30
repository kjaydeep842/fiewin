<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = [
        'user_id',
        'source_user_id',
        'bet_id',
        'level',
        'amount',
        'rate_percentage',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'rate_percentage' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sourceUser()
    {
        return $this->belongsTo(User::class, 'source_user_id');
    }

    public function bet()
    {
        return $this->belongsTo(GameBet::class, 'bet_id');
    }
}
