<?php

namespace App\Services;

use App\Models\JetBet;
use App\Models\JetRound;
use Carbon\Carbon;

class JetHistoryService
{
    /**
     * Get latest settled Jet rounds for history display.
     */
    public function getLatestRounds(int $limit = 20): array
    {
        $rounds = JetRound::where('status', 'CRASHED')
            ->orderBy('id', 'desc')
            ->take($limit)
            ->get();

        if ($rounds->isEmpty()) {
            $defaults = [2.48, 3.01, 1.24, 99.01, 2.95, 1.15, 4.30, 1.88, 12.40, 2.05];
            return array_map(function ($mult, $i) {
                $color = 'red';
                if ($mult >= 2.0) $color = 'green';
                else if ($mult >= 1.5) $color = 'orange';

                return [
                    'id' => $i + 1,
                    'round_id' => 'JET_DEMO_' . ($i + 1),
                    'crash_multiplier' => number_format($mult, 2),
                    'color' => $color,
                    'ended_at' => Carbon::now()->subMinutes(($i + 1) * 2)->format('H:i:s'),
                ];
            }, $defaults, array_keys($defaults));
        }

        return $rounds->map(function ($r) {
            $mult = (float)$r->crash_multiplier;
            $color = 'red';
            if ($mult >= 2.0) $color = 'green';
            else if ($mult >= 1.5) $color = 'orange';

            return [
                'id' => $r->id,
                'round_id' => $r->round_id,
                'crash_multiplier' => number_format($mult, 2),
                'color' => $color,
                'ended_at' => $r->ended_at ? $r->ended_at->format('H:i:s') : '',
            ];
        })->toArray();
    }

    /**
     * Get user Jet orders.
     */
    public function getUserHistory(int $userId, ?string $dateFilter = 'all', ?string $statusFilter = 'all')
    {
        $query = JetBet::where('user_id', $userId)->orderBy('id', 'desc');

        if ($dateFilter === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($dateFilter === 'yesterday') {
            $query->whereDate('created_at', Carbon::yesterday());
        } elseif ($dateFilter === 'last_7_days') {
            $query->where('created_at', '>=', Carbon::now()->subDays(7));
        }

        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        return $query->paginate(15);
    }
}
