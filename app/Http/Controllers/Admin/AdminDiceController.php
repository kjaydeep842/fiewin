<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameBet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminDiceController extends Controller
{
    public function dashboard()
    {
        $game = Game::where('code', 'dice')->firstOrFail();

        $today = now()->startOfDay();
        $todayBets = GameBet::where('game_id', $game->id)->where('created_at', '>=', $today);
        $todayBetsCount = $todayBets->count();
        $todayBetsTotal = $todayBets->sum('bet_amount');
        $todayWinsTotal = GameBet::where('game_id', $game->id)->where('created_at', '>=', $today)->where('status', 'won')->sum('win_amount');
        $houseProfit = $todayBetsTotal - $todayWinsTotal;
        $uniquePlayersCount = GameBet::where('game_id', $game->id)->where('created_at', '>=', $today)->distinct('user_id')->count();

        $recentBets = GameBet::where('game_id', $game->id)->orderBy('id', 'desc')->with('user')->take(20)->get();
        $override = Cache::get('override_dice');

        return view('admin.games.dice', compact(
            'game',
            'todayBetsCount',
            'todayBetsTotal',
            'todayWinsTotal',
            'houseProfit',
            'uniquePlayersCount',
            'recentBets',
            'override'
        ));
    }

    public function updateRTP(Request $request)
    {
        $request->validate([
            'rtp_percentage' => 'required|numeric|between:0,100',
        ]);

        $game = Game::where('code', 'dice')->firstOrFail();
        $game->update(['rtp_percentage' => $request->rtp_percentage]);

        return back()->with('success', "Dice Roll Winning Chance / RTP updated to {$request->rtp_percentage}%!");
    }

    public function updateLimits(Request $request)
    {
        $request->validate([
            'min_entry_fee' => 'required|numeric|min:1',
            'max_entry_fee' => 'required|numeric|gt:min_entry_fee',
        ]);

        $game = Game::where('code', 'dice')->firstOrFail();
        $game->update([
            'min_entry_fee' => $request->min_entry_fee,
            'max_entry_fee' => $request->max_entry_fee,
        ]);

        return back()->with('success', "Dice Min/Max bet limits updated successfully!");
    }

    public function setOverride(Request $request)
    {
        $request->validate([
            'manual_dice_value' => 'nullable|integer|between:1,100',
        ]);

        if ($request->filled('manual_dice_value')) {
            Cache::put('override_dice', (int)$request->manual_dice_value, 600);
            return back()->with('success', "Dice Roll manual override set to roll number {$request->manual_dice_value}!");
        } else {
            Cache::forget('override_dice');
            return back()->with('success', "Dice Roll manual override cleared!");
        }
    }

    public function toggleActive()
    {
        $game = Game::where('code', 'dice')->firstOrFail();
        $game->is_active = !$game->is_active;
        $game->save();

        return back()->with('success', "Dice status changed to " . ($game->is_active ? 'Active' : 'Disabled') . ".");
    }
}
