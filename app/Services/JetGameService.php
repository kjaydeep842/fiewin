<?php

namespace App\Services;

use App\Models\JetBet;
use App\Models\JetRound;
use App\Models\JetSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class JetGameService
{
    protected JetRoundService $roundService;
    protected JetHistoryService $historyService;
    protected WalletService $walletService;

    public function __construct(
        JetRoundService $roundService,
        JetHistoryService $historyService,
        WalletService $walletService
    ) {
        $this->roundService = $roundService;
        $this->historyService = $historyService;
        $this->walletService = $walletService;
    }

    /**
     * Get synchronized Jet Game State.
     */
    public function getSynchronizedState(?User $user): array
    {
        // Cache round tick for 1 second to reduce DB load from rapid polling
        $round = Cache::remember('jet_active_round', 1, function () {
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
            $secondsRemaining = max(0, JetRoundService::COUNTDOWN_SECONDS - $elapsed);
        } elseif ($status === 'CRASHED') {
            $endedTs = $round->ended_at ? $round->ended_at->timestamp : $nowTs;
            $elapsed = max(0, $nowTs - $endedTs);
            $secondsRemaining = max(0, 5 - $elapsed); // 5s post-crash screen before next round
            $currentMultiplier = round((float)$round->crash_multiplier, 2);
        } elseif ($status === 'FLYING') {
            $startedTs = $round->started_at ? $round->started_at->timestamp : $nowTs;
            $elapsed = max(0, $nowTs - $startedTs);
            $currentMultiplier = min((float)$round->crash_multiplier, 1.00 + ($elapsed * 0.35));
        }

        // Live real bets
        $dbBets = JetBet::where('jet_round_id', $round->id)
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

        // Dedicated Jet Bots
        $jetBots = [
            ['name' => 'Captain_Ace', 'bet' => 450, 'cashout_target' => 1.55],
            ['name' => 'SkyWalker', 'bet' => 800, 'cashout_target' => 2.30],
            ['name' => 'JetPro_99', 'bet' => 250, 'cashout_target' => 1.75],
            ['name' => 'Wingman_X', 'bet' => 120, 'cashout_target' => 3.10],
            ['name' => 'Supersonic', 'bet' => 60, 'cashout_target' => 1.30],
        ];

        foreach ($jetBots as $index => $bot) {
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
                'id' => 'BOT_JET_' . ($index + 1),
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
            $userBetModel = JetBet::where('user_id', $user->id)
                ->where('status', 'flying')
                ->where('jet_round_id', '>=', $round->id)
                ->orderBy('id', 'desc')
                ->first();

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

            $myDbBets = JetBet::where('user_id', $user->id)
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
            'game' => 'jet',
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
     * Place bet on active Jet round with optional auto_cashout.
     */
    public function placeBet(User $user, float $amount, ?float $autoCashout = null): array
    {
        return DB::transaction(function () use ($user, $amount, $autoCashout) {
            $round = $this->roundService->getOrSyncActiveRound();
            $settings = JetSetting::getSettings();

            $targetRound = $round;
            if ($round->status !== 'BETTING_OPEN') {
                $targetRound = JetRound::where('status', 'BETTING_OPEN')
                    ->where('id', '>', $round->id)
                    ->orderBy('id', 'asc')
                    ->first();
                if (!$targetRound) {
                    $targetRound = $this->roundService->createNewRound();
                }
            }

            // Prevent duplicate active bets
            $existingActive = JetBet::where('user_id', $user->id)
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
                "JET_BET_{$targetRound->round_id}",
                "JET_BET on Round #{$targetRound->round_id}"
            );

            $bet = JetBet::create([
                'jet_round_id' => $targetRound->id,
                'round_id' => $targetRound->round_id,
                'user_id' => $user->id,
                'bet_amount' => $amount,
                'auto_cashout' => $autoCashout,
                'status' => 'flying',
            ]);

            $newBalance = number_format($user->wallet->fresh()->main_balance, 2);

            Cache::forget('jet_active_round');

            $isNextRound = ($targetRound->id !== $round->id);
            $msg = $isNextRound 
                ? "Bet placed for NEXT round #{$targetRound->round_id}!" 
                : 'Jet bet placed successfully!';

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
     * Cash out active Jet bet.
     */
    public function processCashout(User $user, int $betId, float $clientMult): array
    {
        return DB::transaction(function () use ($user, $betId, $clientMult) {
            // First sync active round to ensure status and started_at timestamps are 100% current
            $activeRound = $this->roundService->getOrSyncActiveRound();

            // Fetch the bet
            $bet = JetBet::where('user_id', $user->id)
                ->where('id', $betId)
                ->firstOrFail();

            if ($bet->status !== 'flying') {
                if ($bet->status === 'cashed_out') {
                    $effectiveMult = (float)($bet->cashout_multiplier ?: 1.00);
                    $winAmount = round($bet->bet_amount * $effectiveMult, 2);
                    $newBalance = number_format($user->wallet->fresh()->main_balance, 2);
                    return [
                        'success' => true,
                        'message' => 'Cashed out +₹' . number_format($winAmount, 2) . ' (' . number_format($effectiveMult, 2) . 'x) successfully!',
                        'win_amount' => number_format($winAmount, 2),
                        'multiplier' => number_format($effectiveMult, 2),
                        'new_balance' => $newBalance,
                    ];
                }
                throw new \Exception('Jet bet is already settled.');
            }

            // Load the round directly
            $round = JetRound::find($bet->jet_round_id);
            if (!$round) {
                throw new \Exception('Round not found.');
            }

            if ($round->status !== 'FLYING') {
                if ($round->status === 'CRASHED') {
                    throw new \Exception('Cannot cash out: the jet has already crashed.');
                }
                throw new \Exception('Flight has not launched yet.');
            }

            $flightStartedTs = $round->started_at ? $round->started_at->timestamp : time();
            $elapsedSec = max(0, time() - $flightStartedTs);
            $serverMultiplier = max(1.00, min((float)$round->crash_multiplier, 1.00 + ($elapsedSec * 0.35)));

            if ($serverMultiplier >= (float)$round->crash_multiplier) {
                $bet->update(['status' => 'lost', 'profit' => 0.00]);
                throw new \Exception('Jet crashed before cash out could be processed!');
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
                "JET_CASHOUT_{$bet->id}",
                "JET_CASHOUT @ {$effectiveMult}x on Round #{$round->round_id}"
            );

            $newBalance = number_format($user->wallet->fresh()->main_balance, 2);

            Cache::forget('jet_active_round');

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
