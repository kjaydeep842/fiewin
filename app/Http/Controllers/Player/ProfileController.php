<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\GameBet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $bankAccounts = BankAccount::where('user_id', $user->id)->get();
        $recentBets = GameBet::where('user_id', $user->id)->with('game')->latest()->take(10)->get();

        return view('player.profile.index', compact('user', 'bankAccounts', 'recentBets'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()->with('error', 'Current password does not match.');
        }

        auth()->user()->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully!');
    }

    public function submitKYC(Request $request)
    {
        $request->validate([
            'id_type' => 'required|string',
            'id_number' => 'required|string',
        ]);

        auth()->user()->update(['kyc_status' => 'pending']);

        return back()->with('success', 'KYC documents submitted for review.');
    }

    public function addBankAccount(Request $request)
    {
        $request->validate([
            'account_holder' => 'required|string',
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'ifsc_code' => 'required|string',
            'upi_id' => 'nullable|string',
        ]);

        BankAccount::create([
            'user_id' => auth()->id(),
            'account_holder' => $request->account_holder,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
            'upi_id' => $request->upi_id,
            'is_primary' => true,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Bank account added successfully! Status is PENDING admin verification before withdrawal usage.');
    }
}
