<?php

namespace App\Services;

use App\Models\CrashBet;
use App\Models\CrashRound;
use Carbon\Carbon;

class CrashHistoryService
{
    /**
     * Get top 20 latest settled crash/jet rounds per gameType.
     */
    public function getLatestRounds(int $limit = 20, string $gameType = 'rocket'): array
    {
        $gameType = in_array($gameType, ['jet', 'rocket']) ? $gameType : 'rocket';

        $rounds = CrashRound::where('game_type', $gameType)
            ->where('status', 'CRASHED')
            ->orderBy('id', 'desc')
            ->take($limit)
            ->get();

        if ($rounds->isEmpty()) {
            // Seeded default fallback history per game mode for instant rich UI
            $defaults = ($gameType === 'jet') 
                ? [2.48, 3.01, 1.24, 99.01, 2.95, 1.15, 4.30, 1.88, 12.40, 2.05]
                : [14.50, 1.12, 1.85, 4.82, 1.05, 22.40, 3.15, 1.40, 8.90, 2.10];

            return array_map(function ($mult, $i) use ($gameType) {
                $color = 'red';
                if ($mult >= 2.0) $color = 'green';
                else if ($mult >= 1.5) $color = 'orange';

                return [
                    'id' => $i + 1,
                    'round_id' => strtoupper($gameType) . '_DEMO_' . ($i + 1),
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
     * Get user crash orders with date & status filters.
     */
    public function getUserHistory(int $userId, ?string $dateFilter = 'all', ?string $statusFilter = 'all')
    {
        $query = CrashBet::where('user_id', $userId)->orderBy('id', 'desc');

        // Apply Date Filter
        if ($dateFilter === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($dateFilter === 'yesterday') {
            $query->whereDate('created_at', Carbon::yesterday());
        } elseif ($dateFilter === 'last_7_days') {
            $query->where('created_at', '>=', Carbon::now()->subDays(7));
        } elseif ($dateFilter === 'this_month') {
            $query->whereMonth('created_at', Carbon::now()->month);
        }

        // Apply Status Filter
        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        return $query->paginate(15);
    }
}
