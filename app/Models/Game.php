<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'code',
        'image',
        'icon',
        'min_entry_fee',
        'max_entry_fee',
        'win_multiplier',
        'rtp_percentage',
        'is_active',
        'rules',
        'instruction',
        'config',
    ];

    protected $casts = [
        'min_entry_fee' => 'decimal:2',
        'max_entry_fee' => 'decimal:2',
        'win_multiplier' => 'decimal:2',
        'rtp_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(GameCategory::class, 'category_id');
    }

    public function results()
    {
        return $this->hasMany(GameResult::class);
    }

    public function bets()
    {
        return $this->hasMany(GameBet::class);
    }
}
