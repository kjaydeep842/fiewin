<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReward extends Model
{
    protected $fillable = [
        'user_id',
        'day_number',
        'reward_amount',
        'claimed_date',
    ];

    protected $casts = [
        'reward_amount' => 'decimal:2',
        'claimed_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
