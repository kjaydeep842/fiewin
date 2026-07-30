<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameMove extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_bet_id',
        'tile_index',
        'is_mine',
        'multiplier',
        'profit',
        'clicked_at',
    ];

    protected $casts = [
        'is_mine' => 'boolean',
        'multiplier' => 'decimal:2',
        'profit' => 'decimal:2',
        'clicked_at' => 'datetime',
    ];

    public function bet()
    {
        return $this->belongsTo(GameBet::class, 'game_bet_id');
    }
}
