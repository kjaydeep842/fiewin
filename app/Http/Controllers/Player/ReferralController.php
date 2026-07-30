<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Referral;

class ReferralController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $referralLink = route('register', ['ref' => $user->referral_code]);
        
        $level1Count = Referral::where('referrer_id', $user->id)->where('level', 1)->count();
        $level2Count = Referral::where('referrer_id', $user->id)->where('level', 2)->count();
        $level3Count = Referral::where('referrer_id', $user->id)->where('level', 3)->count();

        $totalCommission = Commission::where('user_id', $user->id)->sum('amount');
        $recentCommissions = Commission::where('user_id', $user->id)->with('sourceUser')->latest()->take(15)->get();

        return view('player.referral.index', compact('user', 'referralLink', 'level1Count', 'level2Count', 'level3Count', 'totalCommission', 'recentCommissions'));
    }
}
