<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JetSetting extends Model
{
    protected $table = 'jet_settings';

    protected $fillable = [
        'round_seconds',
        'betting_seconds',
        'animation_speed',
        'min_bet',
        'max_bet',
        'rtp_percentage',
        'house_edge',
        'is_active',
        'manual_override_multiplier',
    ];

    protected $casts = [
        'round_seconds' => 'integer',
        'betting_seconds' => 'integer',
        'animation_speed' => 'float',
        'min_bet' => 'float',
        'max_bet' => 'float',
        'rtp_percentage' => 'float',
        'house_edge' => 'float',
        'is_active' => 'boolean',
        'manual_override_multiplier' => 'float',
    ];

    public static function getSettings(): self
    {
        return self::firstOrCreate([], [
            'round_seconds' => 60,
            'betting_seconds' => 45,
            'animation_speed' => 1.10,
            'min_bet' => 10.00,
            'max_bet' => 50000.00,
            'rtp_percentage' => 96.00,
            'house_edge' => 4.00,
            'is_active' => true,
            'manual_override_multiplier' => null,
        ]);
    }
}
