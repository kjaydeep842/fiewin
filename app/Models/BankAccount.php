<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'user_id',
        'account_holder',
        'bank_name',
        'account_number',
        'ifsc_code',
        'upi_id',
        'is_primary',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
