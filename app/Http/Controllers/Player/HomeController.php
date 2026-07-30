<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameCategory;
use App\Models\Promotion;

class HomeController extends Controller
{
    public function index()
    {
        $categories = GameCategory::where('is_active', true)->orderBy('sort_order')->get();
        $featuredGames = Game::where('is_active', true)->take(8)->get();
        $promotions = Promotion::where('is_active', true)->latest()->get();

        $liveWinners = [
            ['user' => 'User***92', 'game' => 'Fast Parity', 'amount' => 1800.00, 'time' => '10s ago'],
            ['user' => 'Player***41', 'game' => 'Mines', 'amount' => 3450.00, 'time' => '18s ago'],
            ['user' => 'Winner***07', 'game' => 'Crash', 'amount' => 5200.00, 'time' => '25s ago'],
            ['user' => 'Lucky***88', 'game' => 'Spin Wheel', 'amount' => 1000.00, 'time' => '32s ago'],
            ['user' => 'Pro***12', 'game' => 'Jet', 'amount' => 7800.00, 'time' => '45s ago'],
        ];

        return view('player.home', compact('categories', 'featuredGames', 'promotions', 'liveWinners'));
    }
}
