<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'code',
        'qr_image',
        'upi_id',
        'min_amount',
        'max_amount',
        'bonus_percentage',
        'instructions',
        'is_active',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'bonus_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
