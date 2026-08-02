<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_request_id',
        'file_path',
        'file_type',
        'original_name',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function depositRequest()
    {
        return $this->belongsTo(DepositRequest::class);
    }
}
