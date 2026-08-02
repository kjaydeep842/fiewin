<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AndarBaharSetting extends Model
{
    protected $table = 'andar_bahar_settings';

    protected $fillable = [
        'round_seconds',
        'betting_seconds',
        'animation_seconds',
        'min_bet',
        'max_bet',
        'rtp_percentage',
        'andar_odds',
        'bahar_odds',
        'tie_odds',
        'is_active',
        'manual_override_winner',
    ];

    protected $casts = [
        'round_seconds' => 'integer',
        'betting_seconds' => 'integer',
        'animation_seconds' => 'integer',
        'min_bet' => 'float',
        'max_bet' => 'float',
        'rtp_percentage' => 'float',
        'andar_odds' => 'float',
        'bahar_odds' => 'float',
        'tie_odds' => 'float',
        'is_active' => 'boolean',
    ];

    public static function getSettings(): self
    {
        return self::firstOrCreate([], [
            'round_seconds' => 60,
            'betting_seconds' => 45,
            'animation_seconds' => 15,
            'min_bet' => 10.00,
            'max_bet' => 50000.00,
            'rtp_percentage' => 96.00,
            'andar_odds' => 2.00,
            'bahar_odds' => 2.00,
            'tie_odds' => 9.00,
            'is_active' => true,
            'manual_override_winner' => null,
        ]);
    }
}
