<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\GameBet;
use App\Models\User;
use App\Models\Withdrawal;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::where('role', 'player')->count();
        $totalDeposits = Deposit::where('status', 'approved')->sum('amount');
        $totalWithdrawals = Withdrawal::where('status', 'approved')->sum('amount');
        $totalBetsAmount = GameBet::sum('bet_amount');
        $totalWinningsAmount = GameBet::sum('win_amount');
        $todayProfit = $totalBetsAmount - $totalWinningsAmount;

        $recentUsers = User::latest()->take(5)->get();
        $recentDeposits = Deposit::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalDeposits',
            'totalWithdrawals',
            'totalBetsAmount',
            'totalWinningsAmount',
            'todayProfit',
            'recentUsers',
            'recentDeposits'
        ));
    }
}
