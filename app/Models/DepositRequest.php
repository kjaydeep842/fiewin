<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DepositRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_id',
        'user_id',
        'merchant_account_id',
        'amount',
        'payment_method',
        'utr_number',
        'status',
        'user_remarks',
        'admin_notes',
        'approved_by',
        'rejected_by',
        'approved_at',
        'rejected_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function merchantAccount()
    {
        return $this->belongsTo(MerchantAccount::class, 'merchant_account_id');
    }

    public function approver()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(Admin::class, 'rejected_by');
    }

    public function proofs()
    {
        return $this->hasMany(DepositProof::class);
    }

    public function verifications()
    {
        return $this->hasMany(DepositVerification::class);
    }

    public function assignmentLogs()
    {
        return $this->hasMany(MerchantAssignmentLog::class);
    }

    public function getIsExpiredAttribute(): bool
    {
        if ($this->status !== 'pending') return false;
        return $this->expires_at && Carbon::now()->greaterThan($this->expires_at);
    }

    public function getSecondsRemainingAttribute(): int
    {
        if (!$this->expires_at || $this->status !== 'pending') return 0;
        return max(0, Carbon::now()->diffInSeconds($this->expires_at, false));
    }
}
