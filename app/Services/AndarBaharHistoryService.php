<?php

namespace App\Services;

use App\Models\AndarBaharBet;
use App\Models\AndarBaharResult;
use App\Models\AndarBaharRound;

class AndarBaharHistoryService
{
    /**
     * Get recent 30 round results for the Record Panel
     */
    public function getRecentResults(int $limit = 30)
    {
        return AndarBaharResult::orderBy('id', 'desc')->take($limit)->get();
    }

    /**
     * Get paginated complete round history
     */
    public function getPaginatedHistory(int $perPage = 20)
    {
        return AndarBaharResult::orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * Get "My Order" bets for a specific user
     */
    public function getUserOrders(int $userId, int $limit = 20)
    {
        return AndarBaharBet::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get "Everyone's Order" (live bets in current/recent period)
     */
    public function getEveryonesOrders(string $periodNumber, int $limit = 30)
    {
        return AndarBaharBet::with('user')
            ->where('period_number', $periodNumber)
            ->orderBy('id', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($bet) {
                $mobile = $bet->user->mobile ?? $bet->user->phone ?? $bet->user->name ?? 'User';
                $maskedMobile = (strlen($mobile) >= 10) 
                    ? substr($mobile, 0, 3) . '***' . substr($mobile, -2) 
                    : 'User***' . substr($bet->id, -2);

                return [
                    'id' => $bet->id,
                    'period' => $bet->period_number,
                    'user' => $maskedMobile,
                    'selection' => strtoupper($bet->bet_option),
                    'amount' => number_format($bet->bet_amount, 2),
                    'status' => ucfirst($bet->status),
                ];
            });
    }
}
