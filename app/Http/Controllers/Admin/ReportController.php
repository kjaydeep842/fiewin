<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Deposit;
use App\Models\GameBet;
use App\Models\Withdrawal;

class ReportController extends Controller
{
    public function index()
    {
        $depositReport = Deposit::selectRaw('DATE(created_at) as date, count(*) as count, sum(amount) as total_amount')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();

        $withdrawalReport = Withdrawal::selectRaw('DATE(created_at) as date, count(*) as count, sum(amount) as total_amount')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();

        $betReport = GameBet::selectRaw('DATE(created_at) as date, count(*) as count, sum(bet_amount) as total_bet, sum(win_amount) as total_win')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();

        $commissionReport = Commission::selectRaw('level, count(*) as count, sum(amount) as total_commission')
            ->groupBy('level')
            ->get();

        return view('admin.reports.index', compact('depositReport', 'withdrawalReport', 'betReport', 'commissionReport'));
    }
}
