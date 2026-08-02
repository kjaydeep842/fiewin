<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrashBet;
use App\Models\CrashRound;
use App\Models\CrashSetting;
use App\Models\Game;
use App\Services\CrashGameService;
use App\Services\CrashRoundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCrashController extends Controller
{
    protected CrashGameService $crashGameService;
    protected CrashRoundService $crashRoundService;

    public function __construct(CrashGameService $crashGameService, CrashRoundService $crashRoundService)
    {
        $this->crashGameService = $crashGameService;
        $this->crashRoundService = $crashRoundService;
    }

    public function dashboard()
    {
        $settings = CrashSetting::getSettings();
        $currentRound = $this->crashRoundService->getOrSyncActiveRound();
        $game = Game::where('code', 'crash')->first();

        $today = now()->startOfDay();
        $todayBetsCount = CrashBet::where('created_at', '>=', $today)->count();
        $todayBetsTotal = CrashBet::where('created_at', '>=', $today)->sum('bet_amount');
        $todayWinsTotal = CrashBet::where('created_at', '>=', $today)->where('status', 'cashed_out')->selectRaw('SUM(bet_amount + profit) as total')->value('total') ?? 0;
        $houseProfit = $todayBetsTotal - $todayWinsTotal;

        $uniquePlayersCount = CrashBet::where('created_at', '>=', $today)->distinct('user_id')->count();
        $pendingBetsCount = CrashBet::where('status', 'flying')->count();

        $recentRounds = CrashRound::where('status', 'CRASHED')->orderBy('id', 'desc')->take(15)->get();

        return view('admin.crash.dashboard', compact(
            'settings',
            'currentRound',
            'game',
            'todayBetsCount',
            'todayBetsTotal',
            'todayWinsTotal',
            'houseProfit',
            'uniquePlayersCount',
            'pendingBetsCount',
            'recentRounds'
        ));
    }

    public function settings(Request $request)
    {
        $settings = CrashSetting::getSettings();

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'round_seconds' => 'required|integer|min:30|max:300',
                'betting_seconds' => 'required|integer|min:5|max:60',
                'min_bet' => 'required|numeric|min:1',
                'max_bet' => 'required|numeric|gt:min_bet',
                'rtp_percentage' => 'required|numeric|min:50|max:100',
                'is_active' => 'required|boolean',
            ]);

            $settings->update($validated);
            Game::where('code', 'crash')->update(['is_active' => $validated['is_active']]);

            return redirect()->back()->with('success', 'Crash Rocket game settings updated successfully!');
        }

        return view('admin.crash.settings', compact('settings'));
    }

    public function setOverride(Request $request)
    {
        $request->validate([
            'multiplier' => 'nullable|numeric|min:1.01|max:500',
        ]);

        $settings = CrashSetting::getSettings();
        $mult = $request->input('multiplier');

        if (empty($mult)) {
            $settings->update(['manual_override_multiplier' => null]);
            return redirect()->back()->with('success', 'Crash manual override cleared.');
        } else {
            $settings->update(['manual_override_multiplier' => $mult]);
            return redirect()->back()->with('success', "Crash target multiplier forced to {$mult}x for next round!");
        }
    }

    public function history()
    {
        $results = CrashRound::where('status', 'CRASHED')->orderBy('id', 'desc')->paginate(20);
        return view('admin.crash.history', compact('results'));
    }

    public function reports(Request $request)
    {
        $range = $request->query('range', 'daily');
        $query = CrashBet::query();

        if ($range === 'daily') {
            $query->where('created_at', '>=', now()->startOfDay());
        } elseif ($range === 'weekly') {
            $query->where('created_at', '>=', now()->startOfWeek());
        } elseif ($range === 'monthly') {
            $query->where('created_at', '>=', now()->startOfMonth());
        }

        $bets = $query->with('user')->orderBy('id', 'desc')->get();
        $totalStakes = $bets->sum('bet_amount');
        $totalWinnings = $bets->where('status', 'cashed_out')->sum(fn($b) => (float)$b->bet_amount + (float)$b->profit);
        $netProfit = $totalStakes - $totalWinnings;

        if ($request->query('export') === 'csv') {
            $filename = "crash_report_{$range}_" . date('Ymd_His') . ".csv";
            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename={$filename}",
            ];

            $callback = function () use ($bets) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Bet ID', 'Round ID', 'User ID', 'User Mobile', 'Bet Amount', 'Cashout Mult', 'Profit', 'Status', 'Date Time']);

                foreach ($bets as $b) {
                    fputcsv($file, [
                        $b->id,
                        $b->round_id,
                        $b->user_id,
                        $b->user->mobile_number ?? 'N/A',
                        $b->bet_amount,
                        $b->cashout_multiplier ?? '-',
                        $b->profit,
                        strtoupper($b->status),
                        $b->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return view('admin.crash.reports', compact('bets', 'range', 'totalStakes', 'totalWinnings', 'netProfit'));
    }
}
