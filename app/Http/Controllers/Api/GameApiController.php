<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameBet;
use App\Models\GameResult;
use App\Services\WalletService;
use Illuminate\Http\Request;

class GameApiController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function getGames()
    {
        $games = Game::where('is_active', true)->get();
        return response()->json([
            'status' => 'success',
            'data' => $games,
        ]);
    }

    public function getGameHistory(string $code)
    {
        $game = Game::where('code', $code)->firstOrFail();
        $history = GameResult::where('game_id', $game->id)->where('status', 'settled')->orderBy('id', 'desc')->take(20)->get();

        return response()->json([
            'status' => 'success',
            'data' => $history,
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

        $user = $request->user();
        $game = Game::findOrFail($request->game_id);

        try {
            $this->walletService->debit(
                $user->id,
                $request->bet_amount,
                'main',
                'bet',
                "API_BET_{$request->period_number}",
                "API Bet on {$game->name}"
            );

            $bet = GameBet::create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'period_number' => $request->period_number,
                'bet_amount' => $request->bet_amount,
                'bet_type' => $request->bet_type,
                'status' => 'pending',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Bet placed successfully',
                'data' => $bet,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
