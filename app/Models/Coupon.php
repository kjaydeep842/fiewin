<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'amount',
        'min_deposit',
        'usage_limit',
        'times_used',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'min_deposit' => 'decimal:2',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];
}
