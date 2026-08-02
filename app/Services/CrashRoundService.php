<?php

namespace App\Services;

use App\Events\GameStateUpdated;
use App\Events\HistoryUpdated;
use App\Events\WalletUpdated;
use App\Models\CrashBet;
use App\Models\CrashRound;
use App\Models\CrashSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CrashRoundService
{
    const COUNTDOWN_SECONDS = 15; // 15s betting window

    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Gets or creates the active synchronized Crash Rocket Round.
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

            if ($latestRound->status === 'CRASHED') {
                $secondsSinceCrash = max(0, $nowTs - $endedTs);
                if ($secondsSinceCrash >= 5) { // 5s post-crash screen before next round opens
                    return $this->createNewRound();
                }
            }

            if ($latestRound->status === 'BETTING_OPEN') {
                $secondsSinceStart = max(0, $nowTs - $startedTs);
                if ($secondsSinceStart >= self::COUNTDOWN_SECONDS) {
                    $latestRound->update([
                        'status' => 'FLYING',
                        'started_at' => Carbon::now(),
                    ]);
                    try {
                        broadcast(new GameStateUpdated('crash', ['status' => 'FLYING', 'round_id' => $latestRound->round_id]))->toOthers();
                    } catch (\Throwable $e) { /* Broadcasting driver not ready */ }
                }
            }

            if ($latestRound->status === 'FLYING') {
                $flightStartedTs = $latestRound->started_at ? $latestRound->started_at->timestamp : $nowTs;
                $elapsedSeconds = max(0, $nowTs - $flightStartedTs);
                $calculatedMultiplier = 1.00 + ($elapsedSeconds * 0.40);

                // Auto-Cashout Engine Evaluation
                $this->processAutoCashouts($latestRound, $calculatedMultiplier);

                if ($calculatedMultiplier >= (float)$latestRound->crash_multiplier) {
                    $this->settleCrashRound($latestRound);
                }
            }

            // Cleanup: ensure all flying bets for crashed or past rounds are settled
            $maxUnsettledId = ($latestRound->status === 'CRASHED') ? $latestRound->id : ($latestRound->id - 1);
            if ($maxUnsettledId > 0) {
                CrashBet::where('status', 'flying')
                    ->where('crash_round_id', '<=', $maxUnsettledId)
                    ->update([
                        'status' => 'lost',
                        'profit' => 0.00,
                    ]);
            }

            return $latestRound->fresh();
        });
    }

    /**
     * Process server-side auto cashouts for active bets.
     */
    public function processAutoCashouts(CrashRound $round, float $currentMultiplier): void
    {
        $autoBets = CrashBet::where('crash_round_id', $round->id)
            ->where('status', 'flying')
            ->whereNotNull('auto_cashout')
            ->where('auto_cashout', '<=', min($currentMultiplier, (float)$round->crash_multiplier))
            ->get();

        foreach ($autoBets as $bet) {
            $targetMult = (float)$bet->auto_cashout;
            $winAmount = round($bet->bet_amount * $targetMult, 2);
            $profit = round($winAmount - $bet->bet_amount, 2);

            $bet->update([
                'cashout_multiplier' => $targetMult,
                'profit' => $profit,
                'status' => 'cashed_out',
            ]);

            $this->walletService->credit(
                $bet->user_id,
                $winAmount,
                'main',
                'win',
                "CRASH_AUTO_CASHOUT_{$bet->id}",
                "CRASH_AUTO_CASHOUT @ {$targetMult}x on Round #{$round->round_id}"
            );

            // Broadcast wallet update to the player
            try {
                $newBalance = $this->walletService->getBalance($bet->user_id);
                broadcast(new WalletUpdated($bet->user_id, (string)$newBalance, 'auto_cashout'));
            } catch (\Throwable $e) { /* Broadcasting driver not ready */ }
        }
    }

    /**
     * Generate sequential round ID resetting hourly.
     * Format: CRASH_YYYYMMDDHH0001, CRASH_YYYYMMDDHH0002...
     */
    protected function generateSequentialRoundId(): string
    {
        $hourPrefix = date('YmdH');
        $prefix = "CRASH_{$hourPrefix}";

        $latest = CrashRound::where('round_id', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $seqStr = substr($latest->round_id, strlen($prefix));
            $seq = (int)$seqStr + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Creates a new Crash Round with provably fair random multiplier.
     */
    public function createNewRound(): CrashRound
    {
        $settings = CrashSetting::getSettings();
        $roundId = $this->generateSequentialRoundId();
        $crashMult = $settings->manual_override_multiplier ? (float)$settings->manual_override_multiplier : $this->generateCrashMultiplier();

        if ($settings->manual_override_multiplier) {
            $settings->update(['manual_override_multiplier' => null]);
        }

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
        DB::transaction(function () use ($round) {
            if ($round->status !== 'CRASHED') {
                $round->update([
                    'status' => 'CRASHED',
                    'ended_at' => Carbon::now(),
                ]);
            }

            CrashBet::where('status', 'flying')
                ->where('crash_round_id', '<=', $round->id)
                ->update([
                    'status' => 'lost',
                    'profit' => 0.00,
                ]);
        });

        // Broadcast crash event and history update
        try {
            broadcast(new GameStateUpdated('crash', [
                'status' => 'CRASHED',
                'crash_multiplier' => $round->crash_multiplier,
                'round_id' => $round->round_id,
            ]));
            broadcast(new HistoryUpdated('crash', [
                'crash_multiplier' => $round->crash_multiplier,
                'color' => (float)$round->crash_multiplier >= 2.0 ? 'green' : ((float)$round->crash_multiplier >= 1.5 ? 'orange' : 'red'),
            ]));
        } catch (\Throwable $e) { /* Broadcasting driver not ready */ }
    }

    /**
     * Generates a provably fair random crash multiplier.
     */
    protected function generateCrashMultiplier(): float
    {
        if (rand(1, 100) <= 5) {
            return round(1.00 + (rand(0, 10) / 100), 2);
        }

        $e = 100;
        $h = rand(1, $e - 1);
        $result = floor((100 * $e - $h) / ($e - $h)) / 100;

        return min(100.00, max(1.01, round($result, 2)));
    }
}
