<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'amount',
        'balance_type',
        'transaction_type',
        'reference_id',
        'description',
        'balance_after',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'balance_after' => 'decimal:4',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
