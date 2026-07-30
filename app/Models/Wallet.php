<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'main_balance',
        'bonus_balance',
        'commission_balance',
        'total_deposited',
        'total_withdrawn',
        'total_winnings',
        'status',
    ];

    protected $casts = [
        'main_balance' => 'decimal:4',
        'bonus_balance' => 'decimal:4',
        'commission_balance' => 'decimal:4',
        'total_deposited' => 'decimal:4',
        'total_withdrawn' => 'decimal:4',
        'total_winnings' => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function getTotalBalanceAttribute()
    {
        return $this->main_balance + $this->bonus_balance;
    }
}
