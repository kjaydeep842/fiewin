<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AndarBaharBet;
use App\Models\AndarBaharResult;
use App\Models\AndarBaharRound;
use App\Models\AndarBaharSetting;
use App\Models\Game;
use App\Services\AndarBaharGameService;
use App\Services\AndarBaharSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAndarBaharController extends Controller
{
    protected AndarBaharGameService $gameService;
    protected AndarBaharSettlementService $settlementService;

    public function __construct(AndarBaharGameService $gameService, AndarBaharSettlementService $settlementService)
    {
        $this->gameService = $gameService;
        $this->settlementService = $settlementService;
    }

    /**
     * Admin Andar Bahar Dashboard & Overview
     */
    public function dashboard()
    {
        $this->settlementService->settlePendingRounds();

        $settings = AndarBaharSetting::getSettings();
        $currentRound = $this->gameService->getCurrentRound();
        $game = Game::where('code', 'andar_bahar')->first();

        // Today's Stats
        $today = now()->startOfDay();
        $todayBetsCount = AndarBaharBet::where('created_at', '>=', $today)->count();
        $todayBetsTotal = AndarBaharBet::where('created_at', '>=', $today)->sum('bet_amount');
        $todayWinsTotal = AndarBaharBet::where('created_at', '>=', $today)->where('status', 'won')->sum('win_amount');
        $houseProfit = $todayBetsTotal - $todayWinsTotal;

        $uniquePlayersCount = AndarBaharBet::where('created_at', '>=', $today)->distinct('user_id')->count();
        $pendingBetsCount = AndarBaharBet::where('status', 'pending')->count();
        $pendingBetsAmount = AndarBaharBet::where('status', 'pending')->sum('bet_amount');

        // Recent 15 settled rounds
        $recentRounds = AndarBaharResult::orderBy('id', 'desc')->take(15)->get();

        return view('admin.andar_bahar.dashboard', compact(
            'settings',
            'currentRound',
            'game',
            'todayBetsCount',
            'todayBetsTotal',
            'todayWinsTotal',
            'houseProfit',
            'uniquePlayersCount',
            'pendingBetsCount',
            'pendingBetsAmount',
            'recentRounds'
        ));
    }

    /**
     * Game Settings Form & Update
     */
    public function settings(Request $request)
    {
        $settings = AndarBaharSetting::getSettings();

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'round_seconds' => 'required|integer|min:30|max:300',
                'betting_seconds' => 'required|integer|min:15|max:240',
                'min_bet' => 'required|numeric|min:1',
                'max_bet' => 'required|numeric|gt:min_bet',
                'rtp_percentage' => 'required|numeric|min:50|max:100',
                'andar_odds' => 'required|numeric|min:1.1',
                'bahar_odds' => 'required|numeric|min:1.1',
                'tie_odds' => 'required|numeric|min:2',
                'is_active' => 'required|boolean',
            ]);

            $validated['animation_seconds'] = max(5, $validated['round_seconds'] - $validated['betting_seconds']);
            $settings->update($validated);

            // Also sync active state in main games table
            Game::where('code', 'andar_bahar')->update(['is_active' => $validated['is_active']]);

            return redirect()->back()->with('success', 'Andar Bahar game settings updated successfully!');
        }

        return view('admin.andar_bahar.settings', compact('settings'));
    }

    /**
     * Manual Result Override Action
     */
    public function setOverride(Request $request)
    {
        $request->validate([
            'winner' => 'nullable|in:andar,bahar,tie,clear',
        ]);

        $settings = AndarBaharSetting::getSettings();
        $target = $request->input('winner');

        if ($target === 'clear' || empty($target)) {
            $settings->update(['manual_override_winner' => null]);
            return redirect()->back()->with('success', 'Manual override cleared. System will use automated RTP.');
        } else {
            $settings->update(['manual_override_winner' => $target]);
            return redirect()->back()->with('success', 'Manual result override set to ' . strtoupper($target) . ' for the next round!');
        }
    }

    /**
     * History & Deal Log Inspector
     */
    public function history()
    {
        $results = AndarBaharResult::orderBy('id', 'desc')->paginate(20);
        return view('admin.andar_bahar.history', compact('results'));
    }

    /**
     * Financial Reports & CSV Export
     */
    public function reports(Request $request)
    {
        $range = $request->query('range', 'daily');

        $query = AndarBaharBet::query();

        if ($range === 'daily') {
            $query->where('created_at', '>=', now()->startOfDay());
        } elseif ($range === 'weekly') {
            $query->where('created_at', '>=', now()->startOfWeek());
        } elseif ($range === 'monthly') {
            $query->where('created_at', '>=', now()->startOfMonth());
        }

        $bets = $query->with('user')->orderBy('id', 'desc')->get();
        $totalStakes = $bets->sum('bet_amount');
        $totalWinnings = $bets->where('status', 'won')->sum('win_amount');
        $netProfit = $totalStakes - $totalWinnings;

        if ($request->query('export') === 'csv') {
            $filename = "andar_bahar_report_{$range}_" . date('Ymd_His') . ".csv";
            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename={$filename}",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];

            $callback = function () use ($bets) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Bet ID', 'Period', 'User ID', 'User Mobile', 'Bet Option', 'Bet Amount (INR)', 'Win Amount (INR)', 'Status', 'Date Time']);

                foreach ($bets as $b) {
                    fputcsv($file, [
                        $b->id,
                        $b->period_number,
                        $b->user_id,
                        $b->user->mobile_number ?? 'N/A',
                        strtoupper($b->bet_option),
                        $b->bet_amount,
                        $b->win_amount,
                        strtoupper($b->status),
                        $b->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return view('admin.andar_bahar.reports', compact('bets', 'range', 'totalStakes', 'totalWinnings', 'netProfit'));
    }
}
