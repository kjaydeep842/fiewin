<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameBet;
use App\Models\GameResult;
use App\Services\GameEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminFastParityController extends Controller
{
    protected GameEngineService $gameEngine;

    public function __construct(GameEngineService $gameEngine)
    {
        $this->gameEngine = $gameEngine;
    }

    public function dashboard()
    {
        $game = Game::where('code', 'fast_parity')->firstOrFail();

        $today = now()->startOfDay();
        $todayBets = GameBet::where('game_id', $game->id)->where('created_at', '>=', $today);
        $todayBetsCount = $todayBets->count();
        $todayBetsTotal = $todayBets->sum('bet_amount');
        $todayWinsTotal = GameBet::where('game_id', $game->id)->where('created_at', '>=', $today)->where('status', 'won')->sum('win_amount');
        $houseProfit = $todayBetsTotal - $todayWinsTotal;
        $uniquePlayersCount = GameBet::where('game_id', $game->id)->where('created_at', '>=', $today)->distinct('user_id')->count();

        $recentResults = GameResult::where('game_id', $game->id)->orderBy('id', 'desc')->take(15)->get();
        $pendingBets = GameBet::where('game_id', $game->id)->where('status', 'pending')->with('user')->get();

        $override = Cache::get('override_fast_parity');

        return view('admin.games.fast_parity', compact(
            'game',
            'todayBetsCount',
            'todayBetsTotal',
            'todayWinsTotal',
            'houseProfit',
            'uniquePlayersCount',
            'recentResults',
            'pendingBets',
            'override'
        ));
    }

    public function updateRTP(Request $request)
    {
        $request->validate([
            'rtp_percentage' => 'required|numeric|between:0,100',
        ]);

        $game = Game::where('code', 'fast_parity')->firstOrFail();
        $game->update(['rtp_percentage' => $request->rtp_percentage]);

        return back()->with('success', "Fast Parity Winning Chance / RTP updated to {$request->rtp_percentage}%!");
    }

    public function updateLimits(Request $request)
    {
        $request->validate([
            'min_entry_fee' => 'required|numeric|min:1',
            'max_entry_fee' => 'required|numeric|gt:min_entry_fee',
        ]);

        $game = Game::where('code', 'fast_parity')->firstOrFail();
        $game->update([
            'min_entry_fee' => $request->min_entry_fee,
            'max_entry_fee' => $request->max_entry_fee,
        ]);

        return back()->with('success', "Fast Parity Min/Max bet limits updated successfully!");
    }

    public function setOverride(Request $request)
    {
        $request->validate([
            'manual_number' => 'nullable|integer|between:0,9',
        ]);

        if ($request->filled('manual_number')) {
            Cache::put('override_fast_parity', (int)$request->manual_number, 600);
            return back()->with('success', "Fast Parity manual override set to number {$request->manual_number}!");
        } else {
            Cache::forget('override_fast_parity');
            return back()->with('success', "Fast Parity manual override cleared! Automatic RTP active.");
        }
    }

    public function toggleActive()
    {
        $game = Game::where('code', 'fast_parity')->firstOrFail();
        $game->is_active = !$game->is_active;
        $game->save();

        return back()->with('success', "Fast Parity status changed to " . ($game->is_active ? 'Active' : 'Disabled') . ".");
    }
}
