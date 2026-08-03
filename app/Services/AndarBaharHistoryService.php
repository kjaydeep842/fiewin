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
        $realBets = AndarBaharBet::with('user')
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
                    'id' => 'real_' . $bet->id,
                    'period' => $bet->period_number,
                    'user' => $maskedMobile,
                    'selection' => strtoupper($bet->bet_option),
                    'amount' => number_format($bet->bet_amount, 2),
                    'status' => ucfirst($bet->status),
                ];
            })->toArray();

        // Generate deterministic realistic dummy bets for period display
        $dummyBets = [];
        $seed = (int)substr(preg_replace('/\D/', '', $periodNumber), -6);
        mt_srand($seed ?: 123456);

        $prefixes = ['98', '99', '97', '96', '91', '88', '89', '70', '79', '80'];
        $selections = ['ANDAR', 'ANDAR', 'BAHAR', 'BAHAR', 'ANDAR', 'BAHAR', 'TIE'];
        $amounts = [50, 100, 200, 500, 1000, 1500, 2000, 3000, 5000];

        $targetTotal = 15;
        $dummyCount = max(8, $targetTotal - count($realBets));

        for ($i = 0; $i < $dummyCount; $i++) {
            $prefix = $prefixes[mt_rand(0, count($prefixes) - 1)];
            $mid = str_pad((string)mt_rand(10, 99), 2, '0', STR_PAD_LEFT);
            $suffix = str_pad((string)mt_rand(10, 99), 2, '0', STR_PAD_LEFT);
            $userPhone = $prefix . $mid . '***' . $suffix;

            $sel = $selections[mt_rand(0, count($selections) - 1)];
            $amt = $amounts[mt_rand(0, count($amounts) - 1)];

            $dummyBets[] = [
                'id' => 'dummy_' . ($i + 1),
                'period' => $periodNumber,
                'user' => $userPhone,
                'selection' => $sel,
                'amount' => number_format($amt, 2),
                'status' => 'Success',
            ];
        }

        mt_srand(); // reset seed

        return array_merge($realBets, $dummyBets);
    }
}
