<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\DailyReward;
use App\Models\Promotion;
use App\Services\WalletService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index()
    {
        $promotions = Promotion::where('is_active', true)->get();
        $user = auth()->user();

        // 7-day daily check-in status
        $todayClaimed = DailyReward::where('user_id', $user->id)
            ->whereDate('claimed_date', now()->toDateString())
            ->exists();

        $checkinHistory = DailyReward::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->take(7)
            ->get();

        return view('player.promotion.index', compact('promotions', 'todayClaimed', 'checkinHistory'));
    }

    public function claimDailyCheckin()
    {
        $user = auth()->user();
        $todayStr = now()->toDateString();

        $alreadyClaimed = DailyReward::where('user_id', $user->id)
            ->whereDate('claimed_date', $todayStr)
            ->exists();

        if ($alreadyClaimed) {
            return back()->with('error', 'You have already claimed today\'s daily check-in bonus!');
        }

        $streakCount = DailyReward::where('user_id', $user->id)->count() % 7 + 1;
        $rewardAmount = 10.00 * $streakCount; // Rs 10 to Rs 70

        DailyReward::create([
            'user_id' => $user->id,
            'day_number' => $streakCount,
            'reward_amount' => $rewardAmount,
            'claimed_date' => $todayStr,
        ]);

        $this->walletService->credit(
            $user->id,
            $rewardAmount,
            'bonus',
            'daily_checkin',
            "CHECKIN_{$todayStr}",
            "Daily Check-in Day #{$streakCount} Bonus"
        );

        return back()->with('success', "Claimed Rs {$rewardAmount} Daily Check-in Bonus!");
    }

    public function redeemCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $coupon = Coupon::where('code', $request->code)->where('is_active', true)->first();

        if (!$coupon) {
            return back()->with('error', 'Invalid or expired coupon code.');
        }

        if ($coupon->times_used >= $coupon->usage_limit) {
            return back()->with('error', 'Coupon limit reached.');
        }

        $coupon->increment('times_used');

        $this->walletService->credit(
            auth()->id(),
            $coupon->amount,
            'bonus',
            'deposit',
            "COUPON_{$coupon->code}",
            "Redeemed Coupon {$coupon->code}"
        );

        return back()->with('success', "Coupon redeemed! Rs {$coupon->amount} added to Bonus wallet.");
    }
}
