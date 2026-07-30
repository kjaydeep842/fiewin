<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Deposit;
use App\Models\PaymentMethod;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use App\Services\PaymentGatewayService;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    protected PaymentGatewayService $paymentService;
    protected WalletService $walletService;

    public function __construct(PaymentGatewayService $paymentService, WalletService $walletService)
    {
        $this->paymentService = $paymentService;
        $this->walletService = $walletService;
    }

    public function index()
    {
        $user = auth()->user();
        $wallet = $user->wallet;
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        $bankAccounts = BankAccount::where('user_id', $user->id)->get();
        $deposits = Deposit::where('user_id', $user->id)->latest()->take(10)->get();
        $withdrawals = Withdrawal::where('user_id', $user->id)->latest()->take(10)->get();
        $transactions = WalletTransaction::where('wallet_id', $wallet->id)->latest()->take(20)->get();

        return view('player.wallet.index', compact('wallet', 'paymentMethods', 'bankAccounts', 'deposits', 'withdrawals', 'transactions'));
    }

    public function deposit(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'amount' => 'required|numeric|min:10',
            'utr_number' => 'nullable|string',
        ]);

        try {
            $deposit = $this->paymentService->initiateDeposit(
                auth()->id(),
                $request->payment_method,
                $request->amount,
                $request->utr_number
            );

            return back()->with('success', "Deposit request submitted successfully! Tx ID: {$deposit->transaction_id}");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'upi_id' => 'nullable|string',
        ]);

        try {
            $withdrawal = $this->paymentService->requestWithdrawal(
                auth()->id(),
                $request->amount,
                $request->bank_account_id,
                $request->upi_id
            );

            return back()->with('success', "Withdrawal request of Rs {$request->amount} submitted! Tx ID: {$withdrawal->transaction_id}");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function transferCommission(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
        ]);

        try {
            $this->walletService->transferCommissionToMain(auth()->id(), $request->amount);
            return back()->with('success', "Rs {$request->amount} transferred from Commission to Main Wallet!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
