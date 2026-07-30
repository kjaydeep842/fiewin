<?php

namespace App\Repositories;

use App\Models\GameBet;

class BetRepository
{
    public function createBet(array $data): GameBet
    {
        return GameBet::create($data);
    }

    public function getUserBets(int $userId, int $limit = 20)
    {
        return GameBet::where('user_id', $userId)
            ->with(['game', 'result'])
            ->orderBy('id', 'desc')
            ->paginate($limit);
    }
}
