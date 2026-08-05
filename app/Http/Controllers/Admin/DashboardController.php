<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Deposit;
use App\Models\DepositRequest;
use App\Models\GameBet;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::where('role', 'player')->count();

        // Financial Totals
        $onlineDeposits = Deposit::where('status', 'approved')->sum('amount');
        $manualDeposits = DepositRequest::where('status', 'approved')->sum('amount');
        $totalDeposits = $onlineDeposits + $manualDeposits;

        $totalWithdrawals = Withdrawal::where('status', 'approved')->sum('amount');
        $totalProfit = $totalDeposits - $totalWithdrawals;

        // Today's Financial Metrics
        $todayOnlineDeposits = Deposit::where('status', 'approved')->whereDate('created_at', now())->sum('amount');
        $todayManualDeposits = DepositRequest::where('status', 'approved')->whereDate('created_at', now())->sum('amount');
        $todayDeposits = $todayOnlineDeposits + $todayManualDeposits;

        $todayWithdrawals = Withdrawal::where('status', 'approved')->whereDate('created_at', now())->sum('amount');
        $todayProfit = $todayDeposits - $todayWithdrawals;

        // Game Metrics
        $totalBetsAmount = GameBet::sum('bet_amount');
        $totalWinningsAmount = GameBet::sum('win_amount');

        // Recent Activity
        $recentUsers = User::where('role', 'player')->latest()->take(5)->get();
        $recentDeposits = DepositRequest::with('user')->latest()->take(5)->get();
        $recentWithdrawals = Withdrawal::with('user')->latest()->take(5)->get();

        // Top Earning Players Section
        $topEarners = User::where('role', 'player')
            ->with('wallet')
            ->withSum(['bets as total_won' => function ($query) {
                $query->where('status', 'won');
            }], 'win_amount')
            ->withSum('bets as total_bet', 'bet_amount')
            ->get()
            ->sortByDesc(function ($user) {
                return $user->total_won ?? $user->wallet?->main_balance ?? 0;
            })
            ->take(10);

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalDeposits',
            'totalWithdrawals',
            'totalProfit',
            'todayDeposits',
            'todayWithdrawals',
            'todayProfit',
            'totalBetsAmount',
            'totalWinningsAmount',
            'recentUsers',
            'recentDeposits',
            'recentWithdrawals',
            'topEarners'
        ));
    }

    public function realtimeAlerts(Request $request)
    {
        // 1. Pending Manual Deposit Requests (UPI/Bank)
        $manualDeposits = DepositRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(15)
            ->get()
            ->map(function ($d) {
                return [
                    'id' => 'dep_manual_' . $d->id,
                    'amount' => number_format((float)$d->amount, 2),
                    'payment_method' => $d->payment_method ?? 'Manual UPI',
                    'user_name' => $d->user ? $d->user->name : 'Player #' . $d->user_id,
                    'utr' => $d->utr_number,
                    'created_at_human' => $d->created_at->diffForHumans(),
                ];
            });

        // 2. Pending Online Gateway Deposits
        $onlineDeposits = Deposit::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(15)
            ->get()
            ->map(function ($d) {
                return [
                    'id' => 'dep_online_' . $d->id,
                    'amount' => number_format((float)$d->amount, 2),
                    'payment_method' => $d->payment_method ?? 'Online QR',
                    'user_name' => $d->user ? $d->user->name : 'Player #' . $d->user_id,
                    'utr' => $d->utr_number ?? $d->txn_id,
                    'created_at_human' => $d->created_at->diffForHumans(),
                ];
            });

        $pendingDeposits = $manualDeposits->concat($onlineDeposits)->values();

        // 3. Pending Withdrawals
        $pendingWithdrawals = Withdrawal::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(15)
            ->get()
            ->map(function ($w) {
                return [
                    'id' => 'wd_' . $w->id,
                    'amount' => number_format((float)$w->amount, 2),
                    'user_name' => $w->user ? $w->user->name : 'Player #' . $w->user_id,
                    'created_at_human' => $w->created_at->diffForHumans(),
                ];
            });

        // 4. Pending Bank Accounts
        $pendingBankCards = BankAccount::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($b) {
                return [
                    'id' => 'bank_' . $b->id,
                    'bank_name' => $b->bank_name,
                    'user_name' => $b->user ? $b->user->name : 'Player #' . $b->user_id,
                    'created_at_human' => $b->created_at->diffForHumans(),
                ];
            });

        // 5. Recent Registered Users (in last 1 hour)
        $recentUsers = User::where('role', 'player')
            ->where('created_at', '>=', now()->subHour())
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($u) {
                return [
                    'id' => 'usr_' . $u->id,
                    'name' => $u->name,
                    'mobile' => $u->mobile,
                    'created_at_human' => $u->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'deposits' => $pendingDeposits,
            'withdrawals' => $pendingWithdrawals,
            'bank_cards' => $pendingBankCards,
            'new_users' => $recentUsers,
        ]);
    }
}
