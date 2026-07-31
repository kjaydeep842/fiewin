<?php

namespace App\Services;

use App\Models\CrashBet;
use App\Models\CrashRound;
use App\Models\User;
use Carbon\Carbon;
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
     * Get synchronized state for all connected clients.
     */
    public function getSynchronizedState(?User $user): array
    {
        $round = $this->roundService->getOrSyncActiveRound();
        $now = Carbon::now();

        $nowTs = time();
        $status = $round->status; // BETTING_OPEN, FLYING, CRASHED
        $secondsRemaining = 0;
        $currentMultiplier = 1.00;

        if ($status === 'BETTING_OPEN') {
            $startedTs = $round->started_at ? $round->started_at->timestamp : $nowTs;
            $elapsed = max(0, $nowTs - $startedTs);
            $secondsRemaining = max(0, CrashRoundService::COUNTDOWN_SECONDS - $elapsed);
        } elseif ($status === 'CRASHED') {
            $endedTs = $round->ended_at ? $round->ended_at->timestamp : $nowTs;
            $elapsed = max(0, $nowTs - $endedTs);
            $secondsRemaining = max(0, CrashRoundService::COUNTDOWN_SECONDS - $elapsed);
        } elseif ($status === 'FLYING') {
            $startedTs = $round->started_at ? $round->started_at->timestamp : $nowTs;
            $elapsed = max(0, $nowTs - $startedTs);
            $currentMultiplier = min((float)$round->crash_multiplier, 1.00 + ($elapsed * 0.40));
        }

        // Real user bets in active round
        $dbBets = CrashBet::where('crash_round_id', $round->id)
            ->with('user')
            ->orderBy('id', 'desc')
            ->get();

        $liveBetsList = [];

        foreach ($dbBets as $b) {
            $liveBetsList[] = [
                'id' => $b->id,
                'username' => $b->user ? $b->user->name : 'User_' . $b->user_id,
                'bet_amount' => number_format($b->bet_amount, 2),
                'cashout_multiplier' => $b->cashout_multiplier ? number_format($b->cashout_multiplier, 2) : null,
                'profit' => number_format($b->profit, 2),
                'status' => $b->status, // flying, cashed_out, lost
            ];
        }

        // Add simulated live multiplayer opponents for high-engagement dynamic UI
        $bots = [
            ['name' => 'Rahul_VIP', 'bet' => 500, 'cashout_target' => 1.45],
            ['name' => 'CryptoKing', 'bet' => 1000, 'cashout_target' => 2.10],
            ['name' => 'Alex_Pro', 'bet' => 200, 'cashout_target' => 1.80],
            ['name' => 'Winner99', 'bet' => 100, 'cashout_target' => 3.20],
            ['name' => 'FastRunner', 'bet' => 50, 'cashout_target' => 1.25],
            ['name' => 'Sonia_K', 'bet' => 300, 'cashout_target' => 2.50],
        ];

        foreach ($bots as $index => $bot) {
            $botStatus = 'flying';
            $cashoutMult = null;
            $profit = 0.00;

            if ($status === 'BETTING_OPEN') {
                $botStatus = 'flying';
            } elseif ($status === 'FLYING') {
                if ($currentMultiplier >= $bot['cashout_target']) {
                    $botStatus = 'cashed_out';
                    $cashoutMult = number_format($bot['cashout_target'], 2);
                    $profit = number_format($bot['bet'] * ($bot['cashout_target'] - 1), 2);
                } else {
                    $botStatus = 'flying';
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
                'id' => 'BOT_' . ($index + 1),
                'username' => $bot['name'],
                'bet_amount' => number_format($bot['bet'], 2),
                'cashout_multiplier' => $cashoutMult,
                'profit' => $profit,
                'status' => $botStatus,
            ];
        }

        // User specific bet in active round
        $userBet = null;
        if ($user) {
            $userBetModel = CrashBet::where('crash_round_id', $round->id)
                ->where('user_id', $user->id)
                ->first();

            if ($userBetModel) {
                $userBet = [
                    'id' => $userBetModel->id,
                    'bet_amount' => number_format($userBetModel->bet_amount, 2),
                    'cashout_multiplier' => $userBetModel->cashout_multiplier ? number_format($userBetModel->cashout_multiplier, 2) : null,
                    'profit' => number_format($userBetModel->profit, 2),
                    'status' => $userBetModel->status,
                ];
            }
        }

        $history = $this->historyService->getLatestRounds(20);
        $userWallet = $user && $user->wallet ? $user->wallet->fresh() : null;

        return [
            'success' => true,
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
            'user_balance' => $userWallet ? number_format($userWallet->main_balance, 2) : '0.00',
            'history' => $history,
        ];
    }

    /**
     * Place a bet on the current active round.
     */
    public function placeBet(User $user, float $amount): array
    {
        return DB::transaction(function () use ($user, $amount) {
            $round = $this->roundService->getOrSyncActiveRound();

            if ($round->status !== 'BETTING_OPEN') {
                throw new \Exception('Betting is closed for current round. Please wait for next round.');
            }

            // Check if user already placed bet in current round
            $existing = CrashBet::where('crash_round_id', $round->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                throw new \Exception('You have already placed a bet in this round.');
            }

            // Debit user wallet
            $this->walletService->debit(
                $user->id,
                $amount,
                'main',
                'bet',
                "CRASH_{$round->round_id}",
                "Bet on Crash Round #{$round->round_id}"
            );

            // Record Crash Bet
            $bet = CrashBet::create([
                'crash_round_id' => $round->id,
                'round_id' => $round->round_id,
                'user_id' => $user->id,
                'bet_amount' => $amount,
                'status' => 'flying',
            ]);

            $newBalance = number_format($user->wallet->fresh()->main_balance, 2);

            return [
                'success' => true,
                'message' => 'Bet placed successfully!',
                'new_balance' => $newBalance,
                'bet' => [
                    'id' => $bet->id,
                    'bet_amount' => number_format($bet->bet_amount, 2),
                    'status' => $bet->status,
                ],
            ];
        });
    }

    /**
     * Cash out an active user bet.
     */
    public function processCashout(User $user, int $betId, float $clientMult): array
    {
        return DB::transaction(function () use ($user, $betId, $clientMult) {
            $round = $this->roundService->getOrSyncActiveRound();

            if ($round->status !== 'FLYING') {
                throw new \Exception('Cannot cash out: Round is not flying.');
            }

            $bet = CrashBet::where('user_id', $user->id)
                ->where('id', $betId)
                ->firstOrFail();

            if ($bet->status !== 'flying') {
                throw new \Exception('Bet is already settled.');
            }

            // Calculate current server multiplier based on flight time
            $elapsedMs = Carbon::now()->diffInMilliseconds($round->started_at);
            $serverMultiplier = min((float)$round->crash_multiplier, 1.00 + ($elapsedMs / 1000) * 0.40);

            // Verify server multiplier didn't already crash
            if ($serverMultiplier >= (float)$round->crash_multiplier) {
                $bet->update(['status' => 'lost', 'profit' => 0.00]);
                throw new \Exception('Round crashed before cash out could be processed!');
            }

            $effectiveMult = min($serverMultiplier, $clientMult);
            $winAmount = round($bet->bet_amount * $effectiveMult, 2);
            $profit = round($winAmount - $bet->bet_amount, 2);

            $bet->update([
                'cashout_multiplier' => $effectiveMult,
                'profit' => $profit,
                'status' => 'cashed_out',
            ]);

            // Credit wallet
            $this->walletService->credit(
                $user->id,
                $winAmount,
                'main',
                'win',
                "CRASH_WIN_{$bet->id}",
                "Crash Win @ {$effectiveMult}x on Round #{$round->round_id}"
            );

            $newBalance = number_format($user->wallet->fresh()->main_balance, 2);

            return [
                'success' => true,
                'message' => "Cashed out +₹{$winAmount} successfully!",
                'win_amount' => number_format($winAmount, 2),
                'multiplier' => number_format($effectiveMult, 2),
                'new_balance' => $newBalance,
            ];
        });
    }
}
