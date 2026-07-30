<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameResult;
use App\Services\GameEngineService;
use Illuminate\Http\Request;

class GameManagerController extends Controller
{
    protected GameEngineService $gameEngine;

    public function __construct(GameEngineService $gameEngine)
    {
        $this->gameEngine = $gameEngine;
    }

    public function index()
    {
        $games = Game::all();
        return view('admin.games.index', compact('games'));
    }

    public function updateRTP(Request $request, Game $game)
    {
        $request->validate([
            'rtp_percentage' => 'required|numeric|between:0,100',
        ]);

        $game->update(['rtp_percentage' => $request->rtp_percentage]);

        return back()->with('success', "RTP percentage updated to {$request->rtp_percentage}% for {$game->name}");
    }

    public function toggleActive(Game $game)
    {
        $game->is_active = !$game->is_active;
        $game->save();

        return back()->with('success', "Game {$game->name} status changed.");
    }

    public function overrideResult(Request $request, Game $game)
    {
        $request->validate([
            'period_number' => 'required|string',
            'manual_number' => 'required|integer|between:0,9',
        ]);

        $result = $this->gameEngine->settleFastParityPeriod($game, $request->period_number, $request->manual_number);

        return back()->with('success', "Result manually overridden for period #{$request->period_number} with winning number {$request->manual_number}");
    }
}
