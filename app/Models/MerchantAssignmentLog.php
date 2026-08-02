<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantAssignmentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_request_id',
        'merchant_account_id',
        'user_id',
        'amount',
        'assignment_reason',
        'assigned_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'assigned_at' => 'datetime',
    ];

    public function depositRequest()
    {
        return $this->belongsTo(DepositRequest::class);
    }

    public function merchantAccount()
    {
        return $this->belongsTo(MerchantAccount::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
