<?php

namespace App\Services;

use App\Models\CrashBet;
use App\Models\CrashRound;
use App\Models\CrashSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CrashGameService
{
    protected CrashRoundService $roundService;
    protected CrashHistoryService $historyService;
    protected WalletService $walletService;

    public function __construct(
        CrashRoundService $roundService,
        CrashHistoryService $historyService,
        WalletService $walletService
    ) {
        $this->roundService = $roundService;
        $this->historyService = $historyService;
        $this->walletService = $walletService;
    }

    /**
     * Get synchronized Crash Rocket state.
     */
    public function getSynchronizedState(?User $user): array
    {
        // Cache round tick for 1 second to reduce DB load from rapid polling
        $round = Cache::remember('crash_active_round', 1, function () {
            return $this->roundService->getOrSyncActiveRound();
        });

        $now = Carbon::now();
        $nowTs = time();

        $status = $round->status;
        $secondsRemaining = 0;
        $currentMultiplier = 1.00;

        if ($status === 'BETTING_OPEN') {
            $startedTs = $round->started_at ? $round->started_at->timestamp : $nowTs;
            $elapsed = max(0, $nowTs - $startedTs);
            $secondsRemaining = max(0, CrashRoundService::COUNTDOWN_SECONDS - $elapsed);
        } elseif ($status === 'CRASHED') {
            $endedTs = $round->ended_at ? $round->ended_at->timestamp : $nowTs;
            $elapsed = max(0, $nowTs - $endedTs);
            $secondsRemaining = max(0, 5 - $elapsed); // 5s post-crash screen before next round
            $currentMultiplier = round((float)$round->crash_multiplier, 2);
        } elseif ($status === 'FLYING') {
            $startedTs = $round->started_at ? $round->started_at->timestamp : $nowTs;
            $elapsed = max(0, $nowTs - $startedTs);
            $currentMultiplier = min((float)$round->crash_multiplier, 1.00 + ($elapsed * 0.40));
        }

        // Live real bets
        $dbBets = CrashBet::where('crash_round_id', $round->id)
            ->with('user')
            ->orderBy('id', 'desc')
            ->get();

        $liveBetsList = [];
        foreach ($dbBets as $b) {
            $liveBetsList[] = [
                'id' => $b->id,
                'username' => $b->user ? $b->user->name : 'User_' . $b->user_id,
                'bet_amount' => round((float)$b->bet_amount, 2),
                'auto_cashout' => $b->auto_cashout ? round((float)$b->auto_cashout, 2) : null,
                'cashout_multiplier' => $b->cashout_multiplier ? round((float)$b->cashout_multiplier, 2) : null,
                'profit' => round((float)$b->profit, 2),
                'status' => $b->status,
            ];
        }

        // Dedicated Crash Rocket Bots
        $rocketBots = [
            ['name' => 'Rahul_VIP', 'bet' => 500, 'cashout_target' => 1.45],
            ['name' => 'CryptoKing', 'bet' => 1000, 'cashout_target' => 2.10],
            ['name' => 'Alex_Pro', 'bet' => 200, 'cashout_target' => 1.80],
            ['name' => 'Winner99', 'bet' => 100, 'cashout_target' => 3.20],
            ['name' => 'FastRunner', 'bet' => 50, 'cashout_target' => 1.25],
        ];

        foreach ($rocketBots as $index => $bot) {
            $botStatus = 'flying';
            $cashoutMult = null;
            $profit = 0.00;

            if ($status === 'FLYING') {
                if ($currentMultiplier >= $bot['cashout_target']) {
                    $botStatus = 'cashed_out';
                    $cashoutMult = number_format($bot['cashout_target'], 2);
                    $profit = number_format($bot['bet'] * ($bot['cashout_target'] - 1), 2);
                }
            } elseif ($status === 'CRASHED') {
                $finalCrash = (float)$round->crash_multiplier;
                if ($finalCrash >= $bot['cashout_target']) {
                    $botStatus = 'cashed_out';
                    $cashoutMult = number_format($bot['cashout_target'], 2);
                    $profit = number_format($bot['bet'] * ($bot['cashout_target'] - 1), 2);
                } else {
                    $botStatus = 'lost';
                }
            }

            $liveBetsList[] = [
                'id' => 'BOT_ROCKET_' . ($index + 1),
                'username' => $bot['name'],
                'bet_amount' => number_format($bot['bet'], 2),
                'auto_cashout' => number_format($bot['cashout_target'], 2),
                'cashout_multiplier' => $cashoutMult,
                'profit' => $profit,
                'status' => $botStatus,
            ];
        }

        $userBet = null;
        $myOrders = [];

        if ($user) {
            // Find active flying bet for current round ONLY (exact match)
            $userBetModel = CrashBet::where('user_id', $user->id)
                ->where('status', 'flying')
                ->where('crash_round_id', $round->id)
                ->orderBy('id', 'desc')
                ->first();

            // If no flying bet for current round, check if user just cashed out this round
            if (!$userBetModel) {
                $userBetModel = CrashBet::where('user_id', $user->id)
                    ->where('status', 'cashed_out')
                    ->where('crash_round_id', $round->id)
                    ->orderBy('id', 'desc')
                    ->first();
                // Only show as active bet if it's cashed_out (recent, not yet cleared)
                if ($userBetModel && $round->status !== 'FLYING') {
                    $userBetModel = null; // Clear stale cashed_out bet when round over
                }
            }

            if ($userBetModel) {
                $liveProfit = round(($userBetModel->bet_amount * $currentMultiplier) - $userBetModel->bet_amount, 2);
                $potentialWin = round($userBetModel->bet_amount * $currentMultiplier, 2);

                $userBet = [
                    'id' => $userBetModel->id,
                    'round_id' => $userBetModel->round_id,
                    'bet_amount' => round((float)$userBetModel->bet_amount, 2),
                    'auto_cashout' => $userBetModel->auto_cashout ? round((float)$userBetModel->auto_cashout, 2) : null,
                    'cashout_multiplier' => $userBetModel->cashout_multiplier ? round((float)$userBetModel->cashout_multiplier, 2) : null,
                    'current_multiplier' => round($currentMultiplier, 2),
                    'live_profit' => $liveProfit,
                    'potential_payout' => $potentialWin,
                    'profit' => round((float)$userBetModel->profit, 2),
                    'status' => $userBetModel->status,
                ];
            }

            $myDbBets = CrashBet::where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->take(15)
                ->get();

            foreach ($myDbBets as $mb) {
                $myOrders[] = [
                    'id' => $mb->id,
                    'round_id' => $mb->round_id,
                    'bet_amount' => round((float)$mb->bet_amount, 2),
                    'auto_cashout' => $mb->auto_cashout ? round((float)$mb->auto_cashout, 2) : '-',
                    'cashout_multiplier' => $mb->cashout_multiplier ? round((float)$mb->cashout_multiplier, 2) : '-',
                    'profit' => round((float)$mb->profit, 2),
                    'status' => $mb->status,
                    'time' => $mb->created_at ? $mb->created_at->format('H:i:s') : '',
                ];
            }
        }

        $history = $this->historyService->getLatestRounds(20);
        $userWallet = $user && $user->wallet ? $user->wallet->fresh() : null;

        return [
            'success' => true,
            'game' => 'crash',
            'server_timestamp' => $now->timestamp,
            'round' => [
                'id' => $round->id,
                'round_id' => $round->round_id,
                'status' => $status,
                'crash_multiplier' => number_format($round->crash_multiplier, 2),
            ],
            'seconds_remaining' => (int)ceil($secondsRemaining),
            'current_multiplier' => number_format($currentMultiplier, 2),
            'live_bets' => $liveBetsList,
            'user_bet' => $userBet,
            'player' => [
                'has_active_bet' => $userBet ? true : false,
                'bet_id' => $userBet ? $userBet['id'] : null,
                'bet_amount' => $userBet ? $userBet['bet_amount'] : 0,
                'auto_cashout' => $userBet ? $userBet['auto_cashout'] : null,
                'cashout_available' => $userBet ? ($userBet['status'] === 'flying' && $status === 'FLYING') : false,
                'current_profit' => $userBet ? $userBet['live_profit'] : 0,
                'potential_payout' => $userBet ? $userBet['potential_payout'] : 0,
                'status' => $userBet ? $userBet['status'] : null,
            ],
            'my_orders' => $myOrders,
            'user_balance' => $userWallet ? number_format($userWallet->main_balance, 2) : '0.00',
            'history' => $history,
        ];
    }

    /**
     * Place bet on active Crash round with optional auto_cashout.
     */
    public function placeBet(User $user, float $amount, ?float $autoCashout = null): array
    {
        return DB::transaction(function () use ($user, $amount, $autoCashout) {
            $round = $this->roundService->getOrSyncActiveRound();
            $settings = CrashSetting::getSettings();

            $targetRound = $round;
            if ($round->status !== 'BETTING_OPEN') {
                $targetRound = CrashRound::where('status', 'BETTING_OPEN')
                    ->where('id', '>', $round->id)
                    ->orderBy('id', 'asc')
                    ->first();
                if (!$targetRound) {
                    $targetRound = $this->roundService->createNewRound();
                }
            }

            // Prevent duplicate active bets
            $existingActive = CrashBet::where('user_id', $user->id)
                ->where('status', 'flying')
                ->first();

            if ($existingActive) {
                throw new \Exception("You already have an active bet placed for Round #{$existingActive->round_id}.");
            }

            if ($amount < $settings->min_bet || $amount > $settings->max_bet) {
                throw new \Exception("Bet amount must be between ₹{$settings->min_bet} and ₹{$settings->max_bet}.");
            }

            if ($autoCashout !== null && ($autoCashout < 1.01 || $autoCashout > 500.00)) {
                throw new \Exception('Auto cashout multiplier must be between 1.01x and 500.00x.');
            }

            // Debit via WalletService
            $this->walletService->debit(
                $user->id,
                $amount,
                'main',
                'bet',
                "CRASH_BET_{$targetRound->round_id}",
                "CRASH_BET on Round #{$targetRound->round_id}"
            );

            $bet = CrashBet::create([
                'crash_round_id' => $targetRound->id,
                'round_id' => $targetRound->round_id,
                'user_id' => $user->id,
                'bet_amount' => $amount,
                'auto_cashout' => $autoCashout,
                'status' => 'flying',
            ]);

            $newBalance = number_format($user->wallet->fresh()->main_balance, 2);

            Cache::forget('crash_active_round');

            $isNextRound = ($targetRound->id !== $round->id);
            $msg = $isNextRound 
                ? "Bet placed for NEXT round #{$targetRound->round_id}!" 
                : 'Crash bet placed successfully!';

            return [
                'success' => true,
                'message' => $msg,
                'new_balance' => $newBalance,
                'bet' => [
                    'id' => $bet->id,
                    'round_id' => $bet->round_id,
                    'bet_amount' => round((float)$bet->bet_amount, 2),
                    'auto_cashout' => $bet->auto_cashout ? round((float)$bet->auto_cashout, 2) : null,
                    'status' => $bet->status,
                ],
            ];
        });
    }

    /**
     * Cash out active Crash bet.
     */
    public function processCashout(User $user, int $betId, float $clientMult): array
    {
        return DB::transaction(function () use ($user, $betId, $clientMult) {
            // First sync active round to ensure status and started_at timestamps are 100% current
            $activeRound = $this->roundService->getOrSyncActiveRound();

            // Fetch the bet
            $bet = CrashBet::where('user_id', $user->id)
                ->where('id', $betId)
                ->firstOrFail();

            if ($bet->status !== 'flying') {
                if ($bet->status === 'cashed_out') {
                    // Bet was already cashed out (auto-cashout or race condition) — return success
                    $effectiveMult = (float)($bet->cashout_multiplier ?: 1.00);
                    $winAmount = round($bet->bet_amount * $effectiveMult, 2);
                    $newBalance = number_format($user->wallet->fresh()->main_balance, 2);
                    return [
                        'success' => true,
                        'already_cashed' => true,
                        'message' => 'Cashed out +₹' . number_format($winAmount, 2) . ' (' . number_format($effectiveMult, 2) . 'x) successfully!',
                        'win_amount' => number_format($winAmount, 2),
                        'multiplier' => number_format($effectiveMult, 2),
                        'new_balance' => $newBalance,
                    ];
                }
                if ($bet->status === 'lost') {
                    throw new \Exception('The rocket already crashed — bet was lost.');
                }
                throw new \Exception('Bet is already settled (status: ' . $bet->status . ').');
            }

            // Load the round directly from the bet
            $round = CrashRound::find($bet->crash_round_id);
            if (!$round) {
                throw new \Exception('Round not found.');
            }

            if ($round->status !== 'FLYING') {
                if ($round->status === 'CRASHED') {
                    // Round just crashed — mark bet as lost if still flying
                    $bet->update(['status' => 'lost', 'profit' => 0.00]);
                    throw new \Exception('The rocket has crashed. Better luck next round!');
                }
                throw new \Exception('Flight has not launched yet — please wait for launch.');
            }

            // Re-fetch round with a fresh DB query to get latest started_at
            $round->refresh();

            $flightStartedTs = $round->started_at ? $round->started_at->timestamp : time();
            $elapsedSec = max(0, time() - $flightStartedTs);
            $serverMultiplier = max(1.00, min((float)$round->crash_multiplier, 1.00 + ($elapsedSec * 0.40)));

            // If server multiplier has reached/exceeded crash point, the round just ended
            if ($serverMultiplier >= (float)$round->crash_multiplier) {
                // Double-check round status from DB
                $round->refresh();
                if ($round->status === 'CRASHED') {
                    $bet->update(['status' => 'lost', 'profit' => 0.00]);
                    throw new \Exception('The rocket crashed just before your cashout!');
                }
                // Still flying — use crash_multiplier as effective payout
                $serverMultiplier = (float)$round->crash_multiplier * 0.995; // 0.5% safety margin
            }

            $effectiveMult = max(1.00, min($serverMultiplier, max(1.00, $clientMult)));
            $winAmount = round($bet->bet_amount * $effectiveMult, 2);
            $profit = round($winAmount - $bet->bet_amount, 2);

            $bet->update([
                'cashout_multiplier' => $effectiveMult,
                'profit' => $profit,
                'status' => 'cashed_out',
            ]);

            // Credit wallet via WalletService
            $this->walletService->credit(
                $user->id,
                $winAmount,
                'main',
                'win',
                "CRASH_CASHOUT_{$bet->id}",
                "CRASH_CASHOUT @ {$effectiveMult}x on Round #{$round->round_id}"
            );

            $newBalance = number_format($user->wallet->fresh()->main_balance, 2);

            Cache::forget('crash_active_round');

            return [
                'success' => true,
                'message' => 'Cashed out +₹' . number_format($winAmount, 2) . ' (' . number_format($effectiveMult, 2) . 'x) successfully!',
                'win_amount' => number_format($winAmount, 2),
                'multiplier' => number_format($effectiveMult, 2),
                'new_balance' => $newBalance,
            ];
        });
    }
}
