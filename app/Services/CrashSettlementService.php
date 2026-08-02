<?php

namespace App\Services;

use App\Models\CrashBet;
use App\Models\CrashResult;
use App\Models\CrashRound;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CrashSettlementService
{
    protected ReferralCommissionService $commissionService;

    public function __construct(ReferralCommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Settle a crashed Crash Round and record result entry.
     */
    public function settleRound(CrashRound $round): CrashResult
    {
        return DB::transaction(function () use ($round) {
            $existing = CrashResult::where('round_id', $round->round_id)->first();
            if ($existing) {
                return $existing;
            }

            $seed = Str::random(16);
            $hash = hash('sha256', $round->round_id . '-' . $round->crash_multiplier . '-' . $seed);

            $result = CrashResult::create([
                'round_id' => $round->round_id,
                'crash_multiplier' => $round->crash_multiplier,
                'provably_fair_hash' => $hash,
                'seed' => $seed,
                'settled_at' => now(),
            ]);

            // Process commissions for bets placed on this round
            $bets = CrashBet::where('crash_round_id', $round->id)->get();
            foreach ($bets as $b) {
                $this->commissionService->processBetCommission($b);
            }

            return $result;
        });
    }
}
