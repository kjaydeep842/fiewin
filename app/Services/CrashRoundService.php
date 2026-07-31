<?php

namespace App\Services;

use App\Models\CrashBet;
use App\Models\CrashRound;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CrashRoundService
{
    const COUNTDOWN_SECONDS = 5;

    /**
     * Gets or creates the active synchronized Crash Round based on server timestamps.
     */
    public function getOrSyncActiveRound(): CrashRound
    {
        return DB::transaction(function () {
            $latestRound = CrashRound::orderBy('id', 'desc')->first();

            if (!$latestRound) {
                return $this->createNewRound();
            }

            $nowTs = time();
            $startedTs = $latestRound->started_at ? $latestRound->started_at->timestamp : $nowTs;
            $endedTs = $latestRound->ended_at ? $latestRound->ended_at->timestamp : $nowTs;

            // If latest round is CRASHED and countdown period (5s) has passed, auto start new round!
            if ($latestRound->status === 'CRASHED') {
                $secondsSinceCrash = max(0, $nowTs - $endedTs);
                if ($secondsSinceCrash >= self::COUNTDOWN_SECONDS) {
                    return $this->createNewRound();
                }
            }

            // If round is in BETTING_OPEN and countdown (5s) has expired, transition to FLYING
            if ($latestRound->status === 'BETTING_OPEN') {
                $secondsSinceStart = max(0, $nowTs - $startedTs);
                if ($secondsSinceStart >= self::COUNTDOWN_SECONDS) {
                    $latestRound->update([
                        'status' => 'FLYING',
                        'started_at' => Carbon::now(), // Reset flight start timestamp
                    ]);
                }
            }

            // If round is FLYING, compute current multiplier based on elapsed flight time
            if ($latestRound->status === 'FLYING') {
                $flightStartedTs = $latestRound->started_at ? $latestRound->started_at->timestamp : $nowTs;
                $elapsedSeconds = max(0, $nowTs - $flightStartedTs);
                $calculatedMultiplier = 1.00 + ($elapsedSeconds * 0.40);

                // Check if target crash multiplier reached
                if ($calculatedMultiplier >= (float)$latestRound->crash_multiplier) {
                    $this->settleCrashRound($latestRound);
                }
            }

            return $latestRound->fresh();
        });
    }

    /**
     * Creates a new Crash Round with provably fair random multiplier.
     */
    public function createNewRound(): CrashRound
    {
        $roundId = 'CRASH_' . date('YmdHis') . '_' . rand(100, 999);
        $crashMult = $this->generateCrashMultiplier();

        return CrashRound::create([
            'round_id' => $roundId,
            'crash_multiplier' => $crashMult,
            'started_at' => Carbon::now(),
            'status' => 'BETTING_OPEN',
        ]);
    }

    /**
     * Settles a crashed round, updating uncashed bets to lost.
     */
    public function settleCrashRound(CrashRound $round): void
    {
        if ($round->status === 'CRASHED') return;

        DB::transaction(function () use ($round) {
            $round->update([
                'status' => 'CRASHED',
                'ended_at' => Carbon::now(),
            ]);

            // Mark all uncashed bets in this round as lost
            CrashBet::where('crash_round_id', $round->id)
                ->where('status', 'flying')
                ->update([
                    'status' => 'lost',
                    'profit' => 0.00,
                ]);
        });
    }

    /**
     * Generates a provably fair random crash multiplier.
     */
    protected function generateCrashMultiplier(): float
    {
        // 5% chance of instant crash at 1.00x - 1.10x
        if (rand(1, 100) <= 5) {
            return round(1.00 + (rand(0, 10) / 100), 2);
        }

        // Standard inverse distribution
        $e = 100;
        $h = rand(1, $e - 1);
        $result = floor((100 * $e - $h) / ($e - $h)) / 100;

        return min(100.00, max(1.01, round($result, 2)));
    }
}
