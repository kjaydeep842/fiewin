<?php

namespace App\Repositories;

use App\Models\Game;
use App\Models\GameResult;

class GameRepository
{
    public function getActiveGames()
    {
        return Game::where('is_active', true)->with('category')->get();
    }

    public function findByCode(string $code): ?Game
    {
        return Game::where('code', $code)->first();
    }

    public function getLatestResults(int $gameId, int $limit = 20)
    {
        return GameResult::where('game_id', $gameId)
            ->where('status', 'settled')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }
}
