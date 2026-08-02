<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_request_id',
        'admin_id',
        'status_from',
        'status_to',
        'verification_notes',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function depositRequest()
    {
        return $this->belongsTo(DepositRequest::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
