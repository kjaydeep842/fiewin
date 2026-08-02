<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\CrashBet;
use App\Models\CrashRound;
use App\Models\CrashSetting;
use App\Models\Game;
use App\Services\CrashGameService;
use App\Services\CrashHistoryService;
use Illuminate\Http\Request;

class CrashController extends Controller
{
    protected CrashGameService $crashGameService;
    protected CrashHistoryService $crashHistoryService;

    public function __construct(
        CrashGameService $crashGameService,
        CrashHistoryService $crashHistoryService
    ) {
        $this->crashGameService = $crashGameService;
        $this->crashHistoryService = $crashHistoryService;
    }

    public function show()
    {
        $game = Game::where('code', 'crash')->firstOrFail();
        if (!$game->is_active) {
            return redirect()->route('home')->with('error', 'Crash Rocket game is currently undergoing maintenance.');
        }

        $settings = CrashSetting::getSettings();
        $user = auth()->user();
        $myBets = $this->crashHistoryService->getUserHistory($user->id, 'today');

        return view('player.games.crash', compact('game', 'settings', 'myBets'));
    }

    public function getCrashState(Request $request)
    {
        $user = auth()->user();
        $state = $this->crashGameService->getSynchronizedState($user);

        return response()->json($state);
    }

    public function getCurrentRound()
    {
        $user = auth()->user();
        $state = $this->crashGameService->getSynchronizedState($user);

        return response()->json([
            'success' => true,
            'round' => $state['round'],
            'seconds_remaining' => $state['seconds_remaining'],
            'current_multiplier' => $state['current_multiplier'],
        ]);
    }

    public function getCurrentBet()
    {
        $user = auth()->user();
        $state = $this->crashGameService->getSynchronizedState($user);

        return response()->json([
            'success' => true,
            'user_bet' => $state['user_bet'],
        ]);
    }

    public function getMyOrders(Request $request)
    {
        $user = auth()->user();
        $history = $this->crashHistoryService->getUserHistory(
            $user->id,
            $request->query('date', 'all'),
            $request->query('status', 'all')
        );

        return response()->json(['success' => true, 'data' => $history]);
    }

    public function placeBet(Request $request)
    {
        $request->validate([
            'bet_amount' => 'required|numeric|min:1',
            'auto_cashout' => 'nullable|numeric|min:1.01|max:500',
        ]);

        $user = auth()->user();
        $amount = (float)$request->input('bet_amount');
        $autoCashout = $request->input('auto_cashout') ? (float)$request->input('auto_cashout') : null;

        try {
            $res = $this->crashGameService->placeBet($user, $amount, $autoCashout);
            return response()->json($res);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 200);
        }
    }

    public function cashout(Request $request)
    {
        $request->validate([
            'bet_id' => 'required|integer',
            'multiplier' => 'required|numeric',
        ]);

        $user = auth()->user();
        $betId = (int)$request->input('bet_id');
        $mult = (float)$request->input('multiplier');

        try {
            $res = $this->crashGameService->processCashout($user, $betId, $mult);
            return response()->json($res);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 200);
        }
    }

    public function history(Request $request)
    {
        $user = auth()->user();
        $history = $this->crashHistoryService->getUserHistory(
            $user->id,
            $request->query('date', 'all'),
            $request->query('status', 'all')
        );

        return response()->json(['success' => true, 'data' => $history]);
    }
}
