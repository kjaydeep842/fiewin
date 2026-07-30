<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_id',
        'payment_method',
        'amount',
        'bonus_amount',
        'utr_number',
        'proof_image',
        'status',
        'gateway_response',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
