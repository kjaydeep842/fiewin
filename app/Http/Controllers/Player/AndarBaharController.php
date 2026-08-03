<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\AndarBaharBet;
use App\Models\AndarBaharRound;
use App\Models\AndarBaharSetting;
use App\Models\Game;
use App\Services\AndarBaharGameService;
use App\Services\AndarBaharHistoryService;
use App\Services\AndarBaharSettlementService;
use App\Services\ReferralCommissionService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AndarBaharController extends Controller
{
    protected AndarBaharGameService $gameService;
    protected AndarBaharSettlementService $settlementService;
    protected AndarBaharHistoryService $historyService;
    protected WalletService $walletService;
    protected ReferralCommissionService $commissionService;

    public function __construct(
        AndarBaharGameService $gameService,
        AndarBaharSettlementService $settlementService,
        AndarBaharHistoryService $historyService,
        WalletService $walletService,
        ReferralCommissionService $commissionService
    ) {
        $this->gameService = $gameService;
        $this->settlementService = $settlementService;
        $this->historyService = $historyService;
        $this->walletService = $walletService;
        $this->commissionService = $commissionService;
    }

    /**
     * Show Andar Bahar Game UI
     */
    public function show(Request $request)
    {
        $game = Game::where('code', 'andar_bahar')->firstOrFail();
        if (!$game->is_active) {
            return redirect()->route('home')->with('error', 'Andar Bahar game is currently undergoing maintenance.');
        }

        // Auto settle past pending rounds
        $this->settlementService->settlePendingRounds();

        $currentRound = $this->gameService->getCurrentRound();
        $settings = AndarBaharSetting::getSettings();
        $user = auth()->user();

        $recentResults = $this->historyService->getRecentResults(30);
        $myBets = $this->historyService->getUserOrders($user->id, 20);

        return view('player.games.andar_bahar', compact(
            'game',
            'currentRound',
            'settings',
            'recentResults',
            'myBets'
        ));
    }

    /**
     * Get Server-Synced Game State (Period, Countdown, Open Card, History, Orders, Wallet)
     */
    public function getGameState(Request $request)
    {
        $this->settlementService->settlePendingRounds();

        $currentRound = $this->gameService->getCurrentRound();
        $settings = AndarBaharSetting::getSettings();
        $user = auth()->user();

        $startedAt = $currentRound->started_at ? $currentRound->started_at->timestamp : time();
        $elapsed = max(0, time() - $startedAt);
        
        // 60-second display countdown (60 down to 0)
        $countdown = max(0, 60 - $elapsed);
        $bettingOpen = ($countdown > 10); // Betting open for first 50 seconds (60 down to 10)

        // Trigger settlement calculations on server when elapsed >= 50s
        $settledResult = null;
        if ($elapsed >= 50) {
            $settledResult = $this->gameService->settleRound($currentRound);
        }

        // Expose last_result to frontend ONLY when countdown reaches 0 (elapsed >= 60s)
        $lastResult = ($elapsed >= 60) ? $settledResult : null;

        $history = $this->historyService->getRecentResults(30);
        $myOrders = $this->historyService->getUserOrders($user->id, 20);
        $everyoneOrders = $this->historyService->getEveryonesOrders($currentRound->period_number, 30);

        return response()->json([
            'status' => true,
            'period' => $currentRound->period_number,
            'open_card' => $currentRound->open_card,
            'countdown' => $countdown,
            'betting_open' => $bettingOpen,
            'wallet' => number_format($user->wallet->main_balance ?? 0.00, 2, '.', ''),
            'settings' => [
                'andar_odds' => $settings->andar_odds,
                'bahar_odds' => $settings->bahar_odds,
                'tie_odds' => $settings->tie_odds,
                'min_bet' => $settings->min_bet,
                'max_bet' => $settings->max_bet,
            ],
            'history' => $history,
            'my_orders' => $myOrders,
            'everyone_orders' => $everyoneOrders,
            'last_result' => $lastResult ? [
                'winner' => strtoupper($lastResult->winner),
                'open_card' => $lastResult->open_card,
                'winning_card' => $lastResult->winning_card,
                'deal_count' => $lastResult->deal_count,
                'deal_sequence' => $lastResult->result_data['deal_sequence'] ?? [],
            ] : null,
        ]);
    }

    /**
     * Place Bet on Andar, Bahar, or Tie
     */
    public function placeBet(Request $request)
    {
        $request->validate([
            'bet_option' => 'required|in:andar,bahar,tie',
            'amount' => 'required|numeric|min:1',
        ]);

        $game = Game::where('code', 'andar_bahar')->firstOrFail();
        if (!$game->is_active) {
            return response()->json(['status' => false, 'message' => 'Andar Bahar game is disabled.'], 403);
        }

        $settings = AndarBaharSetting::getSettings();
        $amount = (float)$request->input('amount');

        if ($amount < $settings->min_bet || $amount > $settings->max_bet) {
            return response()->json([
                'status' => false,
                'message' => "Bet amount must be between ₹{$settings->min_bet} and ₹{$settings->max_bet}."
            ], 422);
        }

        $this->settlementService->settlePendingRounds();
        $currentRound = $this->gameService->getCurrentRound();

        // Validate betting window (must be before animation duration lock)
        $roundSeconds = $settings->round_seconds > 0 ? $settings->round_seconds : 60;
        $startedAt = $currentRound->started_at ? $currentRound->started_at->timestamp : time();
        $elapsed = max(0, time() - $startedAt);
        $countdown = max(0, $roundSeconds - $elapsed);

        if ($countdown <= $settings->animation_seconds) {
            return response()->json([
                'status' => false,
                'message' => 'Betting is closed for the current period. Please wait for the next round.'
            ], 422);
        }

        $user = auth()->user();

        try {
            $bet = DB::transaction(function () use ($user, $currentRound, $request, $amount) {
                // Deduct wallet balance atomically via WalletService
                $wallet = $this->walletService->debit(
                    $user->id,
                    $amount,
                    'main',
                    'bet',
                    "ANDAR_BAHAR_BET_{$currentRound->period_number}_" . time(),
                    "Bet ₹{$amount} on " . strtoupper($request->input('bet_option')) . " for Period #{$currentRound->period_number}"
                );

                $newBet = AndarBaharBet::create([
                    'user_id' => $user->id,
                    'andar_bahar_round_id' => $currentRound->id,
                    'period_number' => $currentRound->period_number,
                    'bet_option' => $request->input('bet_option'),
                    'bet_amount' => $amount,
                    'win_amount' => 0.00,
                    'multiplier' => 0.00,
                    'status' => 'pending',
                ]);

                // Process referral commissions
                $this->commissionService->processBetCommission($newBet);

                return $newBet;
            });

            return response()->json([
                'status' => true,
                'message' => 'Bet placed successfully!',
                'bet' => [
                    'id' => $bet->id,
                    'period' => $bet->period_number,
                    'bet_option' => strtoupper($bet->bet_option),
                    'amount' => number_format($bet->bet_amount, 2),
                ],
                'new_balance' => number_format($user->fresh()->wallet->main_balance, 2, '.', ''),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get Paginated History
     */
    public function getHistory()
    {
        $history = $this->historyService->getPaginatedHistory(20);
        return response()->json(['status' => true, 'data' => $history]);
    }
}
