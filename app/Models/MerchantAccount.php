<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'account_holder',
        'upi_id',
        'qr_image',
        'bank_name',
        'account_number',
        'ifsc',
        'status',
        'daily_limit',
        'current_daily_total',
        'priority',
        'supported_payment_types',
        'region',
        'currency',
    ];

    protected $casts = [
        'daily_limit' => 'decimal:2',
        'current_daily_total' => 'decimal:2',
        'priority' => 'integer',
        'supported_payment_types' => 'array',
    ];

    public function depositRequests()
    {
        return $this->hasMany(DepositRequest::class);
    }

    public function assignmentLogs()
    {
        return $this->hasMany(MerchantAssignmentLog::class);
    }

    /**
     * Remaining capacity for today.
     */
    public function getRemainingCapacityAttribute(): float
    {
        return max(0, (float)$this->daily_limit - (float)$this->current_daily_total);
    }

    /**
     * Load ratio percentage.
     */
    public function getLoadRatioAttribute(): float
    {
        if ((float)$this->daily_limit <= 0) return 1.0;
        return (float)$this->current_daily_total / (float)$this->daily_limit;
    }
}
