<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'sort_order',
        'is_active',
    ];

    public function games()
    {
        return $this->hasMany(Game::class, 'category_id');
    }
}
