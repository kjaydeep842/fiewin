<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'title',
        'banner',
        'description',
        'code',
        'bonus_type',
        'bonus_amount',
        'min_deposit',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'bonus_amount' => 'decimal:2',
        'min_deposit' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];
}
