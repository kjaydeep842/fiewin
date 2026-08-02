<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameBet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminSpinWheelController extends Controller
{
    public function dashboard()
    {
        $game = Game::where('code', 'spin_wheel')->firstOrFail();

        $today = now()->startOfDay();
        $todayBets = GameBet::where('game_id', $game->id)->where('created_at', '>=', $today);
        $todayBetsCount = $todayBets->count();
        $todayBetsTotal = $todayBets->sum('bet_amount');
        $todayWinsTotal = GameBet::where('game_id', $game->id)->where('created_at', '>=', $today)->where('status', 'won')->sum('win_amount');
        $houseProfit = $todayBetsTotal - $todayWinsTotal;
        $uniquePlayersCount = GameBet::where('game_id', $game->id)->where('created_at', '>=', $today)->distinct('user_id')->count();

        $recentBets = GameBet::where('game_id', $game->id)->orderBy('id', 'desc')->with('user')->take(20)->get();
        $override = Cache::get('override_spin_wheel');

        return view('admin.games.spin_wheel', compact(
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

        $game = Game::where('code', 'spin_wheel')->firstOrFail();
        $game->update(['rtp_percentage' => $request->rtp_percentage]);

        return back()->with('success', "Spin Wheel Winning Chance / RTP updated to {$request->rtp_percentage}%!");
    }

    public function updateLimits(Request $request)
    {
        $request->validate([
            'min_entry_fee' => 'required|numeric|min:1',
            'max_entry_fee' => 'required|numeric|gt:min_entry_fee',
        ]);

        $game = Game::where('code', 'spin_wheel')->firstOrFail();
        $game->update([
            'min_entry_fee' => $request->min_entry_fee,
            'max_entry_fee' => $request->max_entry_fee,
        ]);

        return back()->with('success', "Spin Wheel Min/Max bet limits updated successfully!");
    }

    public function setOverride(Request $request)
    {
        $request->validate([
            'multiplier' => 'nullable|numeric|in:0,2,3,5,10,50',
        ]);

        if ($request->filled('multiplier')) {
            Cache::put('override_spin_wheel', (float)$request->multiplier, 600);
            return back()->with('success', "Spin Wheel manual override set to {$request->multiplier}X multiplier!");
        } else {
            Cache::forget('override_spin_wheel');
            return back()->with('success', "Spin Wheel manual override cleared!");
        }
    }

    public function toggleActive()
    {
        $game = Game::where('code', 'spin_wheel')->firstOrFail();
        $game->is_active = !$game->is_active;
        $game->save();

        return back()->with('success', "Spin Wheel status changed to " . ($game->is_active ? 'Active' : 'Disabled') . ".");
    }
}
