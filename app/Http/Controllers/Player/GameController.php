<?php

namespace App\Http\Controllers\Player;

use App\Helpers\GameHelper;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameBet;
use App\Models\GameResult;
use App\Services\CrashGameService;
use App\Services\CrashHistoryService;
use App\Services\GameEngineService;
use App\Services\MineGameService;
use App\Services\WalletService;
use Illuminate\Http\Request;

class GameController extends Controller
{
    protected WalletService $walletService;
    protected GameEngineService $gameEngine;
    protected MineGameService $mineGameService;
    protected CrashGameService $crashGameService;
    protected CrashHistoryService $crashHistoryService;

    public function __construct(
        WalletService $walletService,
        GameEngineService $gameEngine,
        MineGameService $mineGameService,
        CrashGameService $crashGameService,
        CrashHistoryService $crashHistoryService
    ) {
        $this->walletService = $walletService;
        $this->gameEngine = $gameEngine;
        $this->mineGameService = $mineGameService;
        $this->crashGameService = $crashGameService;
        $this->crashHistoryService = $crashHistoryService;
    }

    public function index()
    {
        $games = Game::where('is_active', true)->get();
        return view('player.games.index', compact('games'));
    }

    public function show(string $code, Request $request)
    {
        $game = Game::where('code', $code)->where('is_active', true)->firstOrFail();
        $user = auth()->user();

        if ($code === 'fast_parity' || $code === 'parity') {
            $secondsInterval = (int)$request->query('interval', 30);
            if (!in_array($secondsInterval, [30, 60])) {
                $secondsInterval = 30;
            }

            $currentPeriod = GameHelper::generatePeriodNumber($code, $secondsInterval);
            
            // Auto settle all past pending periods up to current period
            $pendingPeriods = GameBet::where('game_id', $game->id)
                ->where('status', 'pending')
                ->pluck('period_number')
                ->unique();

            foreach ($pendingPeriods as $pNum) {
                if ($pNum !== $currentPeriod) {
                    $this->gameEngine->settleFastParityPeriod($game, $pNum);
                }
            }

            $history = GameResult::where('game_id', $game->id)->where('status', 'settled')->orderBy('id', 'desc')->take(20)->get();
            $myBets = GameBet::where('user_id', $user->id)->where('game_id', $game->id)->orderBy('id', 'desc')->take(15)->get();

            return view('player.games.fast_parity', compact('game', 'currentPeriod', 'secondsInterval', 'history', 'myBets'));
        }

        if ($code === 'mines') {
            $myBets = GameBet::where('user_id', $user->id)->where('game_id', $game->id)->orderBy('id', 'desc')->take(10)->get();
            return view('player.games.mines', compact('game', 'myBets'));
        }

        if ($code === 'crash' || $code === 'jet') {
            $myBets = GameBet::where('user_id', $user->id)->where('game_id', $game->id)->orderBy('id', 'desc')->take(10)->get();
            return view('player.games.crash', compact('game', 'myBets'));
        }

        if ($code === 'spin_wheel') {
            $myBets = GameBet::where('user_id', $user->id)->where('game_id', $game->id)->orderBy('id', 'desc')->take(10)->get();
            return view('player.games.spin_wheel', compact('game', 'myBets'));
        }

        if ($code === 'dice') {
            $myBets = GameBet::where('user_id', $user->id)->where('game_id', $game->id)->orderBy('id', 'desc')->take(15)->get();
            return view('player.games.dice', compact('game', 'myBets'));
        }

        return view('player.games.show', compact('game'));
    }

    public function getGameState(string $code, Request $request)
    {
        $interval = (int)$request->query('interval', 30);
        if (!in_array($interval, [30, 60])) {
            $interval = 30;
        }

        $game = Game::where('code', $code)->where('is_active', true)->firstOrFail();
        $user = auth()->user();

        $timestamp = time();
        $secondsIntoPeriod = $timestamp % $interval;
        $secondsRemaining = $interval - $secondsIntoPeriod;

        $currentPeriodIndex = (int)floor($timestamp / $interval);
        $previousPeriodIndex = $currentPeriodIndex - 1;

        $dateStr = date('Ymd', $timestamp);
        $currentPeriod = $dateStr . str_pad($currentPeriodIndex % 10000, 4, '0', STR_PAD_LEFT);
        $previousPeriod = $dateStr . str_pad($previousPeriodIndex % 10000, 4, '0', STR_PAD_LEFT);

        // Auto settle all past pending periods for this game
        $pendingPeriods = GameBet::where('game_id', $game->id)
            ->where('status', 'pending')
            ->pluck('period_number')
            ->unique();

        foreach ($pendingPeriods as $pNum) {
            if ($pNum !== $currentPeriod) {
                $this->gameEngine->settleFastParityPeriod($game, $pNum);
            }
        }

        // Always settle previous period if not settled
        $this->gameEngine->settleFastParityPeriod($game, $previousPeriod);

        // Get previous period result
        $lastResult = GameResult::where('game_id', $game->id)
            ->where('period_number', $previousPeriod)
            ->first();

        if (!$lastResult) {
            $lastResult = GameResult::where('game_id', $game->id)
                ->where('status', 'settled')
                ->orderBy('id', 'desc')
                ->first();
        }

        // Get user's latest settled bet
        $userLatestSettledBet = GameBet::where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->whereIn('status', ['won', 'lost'])
            ->orderBy('id', 'desc')
            ->first();

        $latestBetData = null;
        if ($userLatestSettledBet) {
            $betResult = GameResult::where('game_id', $game->id)
                ->where('period_number', $userLatestSettledBet->period_number)
                ->first();

            $latestBetData = [
                'id'             => $userLatestSettledBet->id,
                'period_number'  => $userLatestSettledBet->period_number,
                'bet_type'       => strtoupper($userLatestSettledBet->bet_type),
                'bet_amount'     => number_format($userLatestSettledBet->bet_amount, 2),
                'win_amount'     => number_format($userLatestSettledBet->win_amount, 2),
                'status'         => $userLatestSettledBet->status,
                'winning_number' => $betResult ? ($betResult->result_data['number'] ?? 0) : 0,
                'winning_colors' => $betResult ? ($betResult->result_data['colors'] ?? []) : [],
                // Unix timestamp of when the bet was settled — used by frontend to
                // gate popups: only show if settled AFTER the current page load.
                'settled_at_unix' => $userLatestSettledBet->updated_at
                    ? $userLatestSettledBet->updated_at->timestamp
                    : 0,
            ];
        }

        // Latest period history (settled)
        $history = GameResult::where('game_id', $game->id)
            ->where('status', 'settled')
            ->orderBy('id', 'desc')
            ->take(20)
            ->get()
            ->map(function ($item) {
                $data = $item->result_data ?? [];
                return [
                    'period_number' => $item->period_number,
                    'number' => $data['number'] ?? 0,
                    'colors' => $data['colors'] ?? ['green'],
                ];
            });

        // Latest user bets
        $myBets = GameBet::where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->orderBy('id', 'desc')
            ->take(15)
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'period_number' => $b->period_number,
                    'bet_type' => $b->bet_type,
                    'bet_amount' => number_format($b->bet_amount, 2),
                    'win_amount' => number_format($b->win_amount, 2),
                    'status' => $b->status,
                ];
            });

        $userWallet = $user->wallet ? $user->wallet->fresh() : null;

        return response()->json([
            'success' => true,
            'interval' => $interval,
            'current_period' => $currentPeriod,
            'previous_period' => $previousPeriod,
            'seconds_remaining' => $secondsRemaining,
            'user_balance' => number_format($userWallet ? $userWallet->main_balance : 0, 2),
            'last_result' => $lastResult ? [
                'period_number' => $lastResult->period_number,
                'number' => $lastResult->result_data['number'] ?? 0,
                'colors' => $lastResult->result_data['colors'] ?? [],
            ] : null,
            'user_latest_settled_bet' => $latestBetData,
            'history' => $history,
            'my_bets' => $myBets,
        ]);
    }

    public function placeBet(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'period_number' => 'required|string',
            'bet_amount' => 'required|numeric|min:1',
            'bet_type' => 'required|string',
        ]);

        $user = auth()->user();
        $game = Game::findOrFail($request->game_id);

        if ($request->bet_amount < $game->min_entry_fee || $request->bet_amount > $game->max_entry_fee) {
            $msg = "Bet amount must be between Rs {$game->min_entry_fee} and Rs {$game->max_entry_fee}";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        try {
            // Debit user wallet
            $this->walletService->debit(
                $user->id,
                $request->bet_amount,
                'main',
                'bet',
                "BET_{$request->period_number}",
                "Bet on {$game->name} Period #{$request->period_number}"
            );

            // Record bet
            $bet = GameBet::create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'period_number' => $request->period_number,
                'bet_amount' => $request->bet_amount,
                'bet_type' => $request->bet_type,
                'status' => 'pending',
            ]);

            $newBalance = number_format($user->wallet->fresh()->main_balance, 2);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Bet placed successfully on " . strtoupper($request->bet_type) . "!",
                    'new_balance' => $newBalance,
                    'bet' => $bet
                ]);
            }

            return back()->with('success', "Bet placed successfully on {$request->bet_type}!");
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function startMines(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'bet_amount' => 'required|numeric|min:1',
            'mines_count' => 'required|integer|in:1,3,5,10,15,20',
        ]);

        $game = Game::findOrFail($request->game_id);
        if ($request->bet_amount < $game->min_entry_fee || $request->bet_amount > $game->max_entry_fee) {
            return response()->json([
                'success' => false,
                'message' => "Bet amount must be between Rs {$game->min_entry_fee} and Rs {$game->max_entry_fee}"
            ], 422);
        }

        try {
            $result = $this->mineGameService->startGame(
                auth()->user(),
                $game,
                (float)$request->bet_amount,
                (int)$request->mines_count
            );
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function revealMinesTile(Request $request)
    {
        $request->validate([
            'bet_id' => 'required|exists:game_bets,id',
            'tile_index' => 'required|integer|min:0|max:24',
        ]);

        try {
            $result = $this->mineGameService->revealTile(
                auth()->user(),
                (int)$request->bet_id,
                (int)$request->tile_index
            );
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function cashoutMines(Request $request)
    {
        $request->validate([
            'bet_id' => 'required|exists:game_bets,id',
        ]);

        try {
            $result = $this->mineGameService->cashoutGame(
                auth()->user(),
                (int)$request->bet_id
            );
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getMinesHistory(Request $request)
    {
        $game = Game::where('code', 'mines')->firstOrFail();
        $history = GameBet::where('user_id', auth()->id())
            ->where('game_id', $game->id)
            ->orderBy('id', 'desc')
            ->take(15)
            ->get()
            ->map(function ($bet) {
                return [
                    'id' => $bet->id,
                    'time' => $bet->created_at->format('H:i:s'),
                    'bet_amount' => number_format($bet->bet_amount, 2),
                    'mines_count' => $bet->bet_details['mines_count'] ?? 3,
                    'multiplier' => number_format($bet->multiplier, 2) . 'x',
                    'win_amount' => number_format($bet->win_amount, 2),
                    'status' => $bet->status,
                ];
            });

        return response()->json(['success' => true, 'history' => $history]);
    }

    public function getCrashState(Request $request)
    {
        $state = $this->crashGameService->getSynchronizedState(auth()->user());
        return response()->json($state);
    }

    public function placeCrashBet(Request $request)
    {
        $request->validate([
            'bet_amount' => 'required|numeric|min:1',
        ]);

        try {
            $result = $this->crashGameService->placeBet(auth()->user(), (float)$request->bet_amount);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function cashoutCrash(Request $request)
    {
        $request->validate([
            'bet_id' => 'required|integer',
            'multiplier' => 'required|numeric|min:1.00',
        ]);

        try {
            $result = $this->crashGameService->processCashout(
                auth()->user(),
                (int)$request->bet_id,
                (float)$request->multiplier
            );
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function showMyCrashHistory(Request $request)
    {
        $dateFilter = $request->query('date', 'all');
        $statusFilter = $request->query('status', 'all');

        $orders = $this->crashHistoryService->getUserHistory(auth()->id(), $dateFilter, $statusFilter);

        return view('player.games.crash_history', compact('orders', 'dateFilter', 'statusFilter'));
    }

    public function settleSpinWheel(Request $request)
    {
        $request->validate([
            'bet_id' => 'required|exists:game_bets,id',
            'multiplier' => 'required|numeric|min:0',
        ]);

        $bet = GameBet::where('user_id', auth()->id())->findOrFail($request->bet_id);
        if ($bet->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Bet already settled'], 400);
        }

        $winAmount = round($bet->bet_amount * $request->multiplier, 2);
        if ($winAmount > 0) {
            $bet->update([
                'win_amount' => $winAmount,
                'multiplier' => $request->multiplier,
                'status' => 'won',
            ]);

            $this->walletService->credit(
                auth()->id(),
                $winAmount,
                'main',
                'win',
                "SPIN_WIN_{$bet->id}",
                "Spin Wheel Win at {$request->multiplier}x multiplier"
            );
        } else {
            $bet->update([
                'win_amount' => 0.00,
                'multiplier' => 0.00,
                'status' => 'lost',
            ]);
        }

        $userWallet = auth()->user()->wallet->fresh();

        return response()->json([
            'success' => true,
            'win_amount' => number_format($winAmount, 2),
            'new_balance' => number_format($userWallet->main_balance, 2),
        ]);
    }

    /**
     * Settle a Dice Roll bet server-side.
     * Bet types: 'over' (4,5,6) | 'under' (1,2,3) | 'exact_N' (1-6)
     * Multipliers: over/under → 1.9x | exact → 5.5x
     */
    public function settleDice(Request $request)
    {
        $request->validate([
            'bet_id' => 'required|exists:game_bets,id',
        ]);

        $bet = GameBet::where('user_id', auth()->id())->findOrFail($request->bet_id);

        if ($bet->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Bet already settled.'], 400);
        }

        // Roll the dice server-side (1-6, truly random)
        $rolled = random_int(1, 6);

        // Determine win based on bet type
        $betType = $bet->bet_type; // 'over' | 'under' | 'exact_N'
        $multiplier = 0;
        $won = false;

        if ($betType === 'over') {
            // Win on 4, 5, 6
            $won = $rolled >= 4;
            $multiplier = 1.9;
        } elseif ($betType === 'under') {
            // Win on 1, 2, 3
            $won = $rolled <= 3;
            $multiplier = 1.9;
        } elseif (str_starts_with($betType, 'exact_')) {
            $targetNum = (int) str_replace('exact_', '', $betType);
            $won = ($rolled === $targetNum);
            $multiplier = 5.5;
        }

        $winAmount = $won ? round($bet->bet_amount * $multiplier, 2) : 0.00;

        $bet->update([
            'win_amount'  => $winAmount,
            'multiplier'  => $won ? $multiplier : 0,
            'status'      => $won ? 'won' : 'lost',
            'bet_details' => array_merge($bet->bet_details ?? [], [
                'rolled'     => $rolled,
                'multiplier' => $multiplier,
            ]),
        ]);

        if ($won && $winAmount > 0) {
            $this->walletService->credit(
                auth()->id(),
                $winAmount,
                'main',
                'win',
                "DICE_WIN_{$bet->id}",
                "Dice Roll Win: rolled {$rolled} on bet type '{$betType}' at {$multiplier}x"
            );
        }

        $newBalance = number_format(auth()->user()->wallet->fresh()->main_balance, 2);

        return response()->json([
            'success'     => true,
            'rolled'      => $rolled,
            'status'      => $won ? 'won' : 'lost',
            'win_amount'  => number_format($winAmount, 2),
            'new_balance' => $newBalance,
        ]);
    }
}

