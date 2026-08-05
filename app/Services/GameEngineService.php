<?php

namespace App\Services;

use App\Helpers\GameHelper;
use App\Models\Game;
use App\Models\GameBet;
use App\Models\GameResult;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GameEngineService
{
    protected WalletService $walletService;
    protected ReferralCommissionService $commissionService;

    public function __construct(WalletService $walletService, ReferralCommissionService $commissionService)
    {
        $this->walletService = $walletService;
        $this->commissionService = $commissionService;
    }

    /**
     * Settle Fast Parity Period Game
     */
    public function settleFastParityPeriod(Game $game, string $periodNumber, ?int $manualOverrideNumber = null, ?\Carbon\Carbon $settledAt = null): GameResult
    {
        return DB::transaction(function () use ($game, $periodNumber, $manualOverrideNumber, $settledAt) {
            $existingResult = GameResult::where('game_id', $game->id)
                ->where('period_number', $periodNumber)
                ->first();

            if ($existingResult && $existingResult->status === 'settled') {
                return $existingResult;
            }

            // Check cache for manual admin override if not explicitly passed
            if ($manualOverrideNumber === null) {
                $cacheKey = ($game->code === 'fast_parity') ? 'override_fast_parity' : 'override_parity';
                if (Cache::has($cacheKey)) {
                    $manualOverrideNumber = (int)Cache::get($cacheKey);
                }
            }

            $pendingBets = GameBet::where('game_id', $game->id)
                ->where('period_number', $periodNumber)
                ->where('status', 'pending')
                ->get();

            $winningNumber = 0;

            if ($manualOverrideNumber !== null && $manualOverrideNumber >= 0 && $manualOverrideNumber <= 9) {
                $winningNumber = $manualOverrideNumber;
                $isOverride = true;
            } else {
                $isOverride = false;
                // RTP (Return to Player) algorithm: calculate payout per possible number (0-9) and choose the one that respects house profit RTP
                $payouts = array_fill(0, 10, 0.0);
                $totalStakes = $pendingBets->sum('bet_amount');

                foreach ($pendingBets as $bet) {
                    for ($num = 0; $num <= 9; $num++) {
                        $payouts[$num] += $this->calculatePotentialPayout($bet->bet_type, $bet->bet_amount, $num);
                    }
                }

                // Filter numbers where house payout <= totalStakes * (RTP % / 100)
                $maxAllowedPayout = $totalStakes * ($game->rtp_percentage / 100.0);
                $eligibleNumbers = [];

                foreach ($payouts as $num => $payout) {
                    if ($payout <= $maxAllowedPayout) {
                        $eligibleNumbers[] = $num;
                    }
                }

                if (!empty($eligibleNumbers)) {
                    $winningNumber = $eligibleNumbers[array_rand($eligibleNumbers)];
                } else {
                    // Pick the number with minimum payout to house
                    asort($payouts);
                    $winningNumber = array_key_first($payouts);
                }
            }

            $resultDetails = GameHelper::getParityColorResult($winningNumber);
            $seed = Str::random(16);
            $hash = hash('sha256', $periodNumber . '-' . $winningNumber . '-' . $seed);
            $timestamp = $settledAt ?? now();

            $gameResult = GameResult::updateOrCreate(
                ['game_id' => $game->id, 'period_number' => $periodNumber],
                [
                    'result_data' => array_merge($resultDetails, ['number' => $winningNumber]),
                    'provably_fair_hash' => $hash,
                    'seed' => $seed,
                    'status' => 'settled',
                    'manual_override' => $isOverride,
                    'settled_at' => $timestamp,
                    'created_at' => $timestamp,
                ]
            );

            // Settle all pending bets
            foreach ($pendingBets as $bet) {
                $winAmount = $this->calculatePotentialPayout($bet->bet_type, $bet->bet_amount, $winningNumber);

                if ($winAmount > 0) {
                    $bet->update([
                        'game_result_id' => $gameResult->id,
                        'win_amount' => $winAmount,
                        'status' => 'won',
                        'multiplier' => $winAmount / $bet->bet_amount,
                    ]);

                    // Credit user wallet
                    $this->walletService->credit(
                        $bet->user_id,
                        $winAmount,
                        'main',
                        'win',
                        "PARITY_WIN_{$bet->id}",
                        "Won Rs {$winAmount} on Parity Period #{$periodNumber}"
                    );
                } else {
                    $bet->update([
                        'game_result_id' => $gameResult->id,
                        'win_amount' => 0.00,
                        'status' => 'lost',
                    ]);
                }

                // Process multi-tier referral commissions for this bet
                $this->commissionService->processBetCommission($bet);
            }

            return $gameResult;
        });
    }

    /**
     * Calculate Fast Parity payout
     */
    protected function calculatePotentialPayout(string $betType, float $amount, int $winningNumber): float
    {
        $res = GameHelper::getParityColorResult($winningNumber);

        if (is_numeric($betType)) {
            $num = (int)$betType;
            return ($num === $winningNumber) ? ($amount * 9.0) : 0.0;
        }

        if ($betType === 'green') {
            if (in_array('green', $res['colors'])) {
                return ($winningNumber === 5) ? ($amount * 1.5) : ($amount * 2.0);
            }
        } elseif ($betType === 'red') {
            if (in_array('red', $res['colors'])) {
                return ($winningNumber === 0) ? ($amount * 1.5) : ($amount * 2.0);
            }
        } elseif ($betType === 'violet') {
            if (in_array('violet', $res['colors'])) {
                return $amount * 4.5;
            }
        }

        return 0.0;
    }

    /**
     * Mines Game: Cash out or reveal tile
     */
    public function processMinesCashout(GameBet $bet, float $multiplier): float
    {
        return DB::transaction(function () use ($bet, $multiplier) {
            $winAmount = round($bet->bet_amount * $multiplier, 2);

            $bet->update([
                'win_amount' => $winAmount,
                'multiplier' => $multiplier,
                'cashout_multiplier' => $multiplier,
                'status' => 'won',
            ]);

            $this->walletService->credit(
                $bet->user_id,
                $winAmount,
                'main',
                'win',
                "MINES_CASHOUT_{$bet->id}",
                "Mines Cash Out at {$multiplier}x multiplier"
            );

            return $winAmount;
        });
    }

    /**
     * Crash & Jet Game: Instant cash out validation
     */
    public function processCrashCashout(GameBet $bet, float $currentMultiplier): float
    {
        return DB::transaction(function () use ($bet, $currentMultiplier) {
            if ($bet->status !== 'pending') {
                return 0.00;
            }

            $winAmount = round($bet->bet_amount * $currentMultiplier, 2);

            $bet->update([
                'win_amount' => $winAmount,
                'multiplier' => $currentMultiplier,
                'cashout_multiplier' => $currentMultiplier,
                'status' => 'won',
            ]);

            $this->walletService->credit(
                $bet->user_id,
                $winAmount,
                'main',
                'win',
                "CRASH_CASHOUT_{$bet->id}",
                "Crash Cash Out at {$currentMultiplier}x"
            );

            return $winAmount;
        });
    }
}
