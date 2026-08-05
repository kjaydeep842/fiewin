<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Deposit;
use App\Models\DepositRequest;
use App\Models\PaymentMethod;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use App\Services\ManualDepositService;
use App\Services\PaymentGatewayService;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    protected PaymentGatewayService $paymentService;
    protected WalletService $walletService;
    protected ManualDepositService $manualDepositService;

    public function __construct(
        PaymentGatewayService $paymentService,
        WalletService $walletService,
        ManualDepositService $manualDepositService
    ) {
        $this->paymentService = $paymentService;
        $this->walletService = $walletService;
        $this->manualDepositService = $manualDepositService;
    }

    public function index()
    {
        $user = auth()->user();
        $wallet = $user->wallet;
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        $allBankAccounts = BankAccount::where('user_id', $user->id)->get();
        $bankAccounts = BankAccount::where('user_id', $user->id)->where('status', 'approved')->get();
        $deposits = Deposit::where('user_id', $user->id)->latest()->take(10)->get();
        $manualDeposits = DepositRequest::where('user_id', $user->id)->with('merchantAccount')->latest()->take(10)->get();
        $withdrawals = Withdrawal::where('user_id', $user->id)->latest()->take(10)->get();
        $transactions = WalletTransaction::where('wallet_id', $wallet->id)->latest()->take(20)->get();

        return view('player.wallet.index', compact('wallet', 'paymentMethods', 'bankAccounts', 'allBankAccounts', 'deposits', 'manualDeposits', 'withdrawals', 'transactions'));
    }

    /**
     * Display User Wallet & Transaction History Dashboard with Withdrawal Tracking
     */
    public function history()
    {
        $user = auth()->user();
        $wallet = $user->wallet;
        $deposits = Deposit::where('user_id', $user->id)->latest()->get();
        $manualDeposits = DepositRequest::where('user_id', $user->id)->with('merchantAccount')->latest()->get();
        $withdrawals = Withdrawal::where('user_id', $user->id)->with('bankAccount')->latest()->get();
        $transactions = WalletTransaction::where('wallet_id', $wallet->id)->latest()->paginate(25);

        return view('player.wallet.history', compact('user', 'wallet', 'deposits', 'manualDeposits', 'withdrawals', 'transactions'));
    }

    /**
     * Initiate Manual Merchant Deposit and redirect to Checkout.
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'amount' => 'required|numeric|min:10',
            'utr_number' => 'nullable|string|max:64',
            'screenshot' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $pmInput = strtolower($request->input('payment_method', 'upi'));
        $pmType = str_contains($pmInput, 'bank') ? 'bank_transfer' : 'upi';

        try {
            $depositRequest = $this->manualDepositService->createDepositRequest(
                auth()->user(),
                (float)$request->input('amount'),
                $pmType
            );

            if ($request->filled('utr_number')) {
                $this->manualDepositService->submitPaymentProof(
                    $depositRequest,
                    $request->input('utr_number'),
                    $request->file('screenshot'),
                    $request->input('user_remarks')
                );
            }

            return redirect()->route('wallet.deposit.checkout', $depositRequest->deposit_id)
                ->with('success', "Deposit Request #{$depositRequest->deposit_id} created! Please complete payment to assigned merchant.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * View Deposit Checkout Screen with assigned Merchant, Timer & UTR Upload.
     */
    public function checkout($depositId)
    {
        $user = auth()->user();
        $depositRequest = DepositRequest::where('deposit_id', $depositId)
            ->where('user_id', $user->id)
            ->with(['merchantAccount', 'proofs', 'verifications'])
            ->firstOrFail();

        return view('player.wallet.deposit_checkout', compact('depositRequest'));
    }

    /**
     * Submit UTR & Payment Screenshot Proof
     */
    public function submitProof(Request $request, $depositId)
    {
        $request->validate([
            'utr_number' => 'required|string|min:6|max:64',
            'screenshot' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'user_remarks' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $depositRequest = DepositRequest::where('deposit_id', $depositId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        try {
            $this->manualDepositService->submitPaymentProof(
                $depositRequest,
                $request->input('utr_number'),
                $request->file('screenshot'),
                $request->input('user_remarks')
            );

            return redirect()->route('wallet.deposit.checkout', $depositId)
                ->with('success', "Payment UTR #{$request->input('utr_number')} submitted successfully! Pending admin verification.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Fetch Live Deposit Status JSON
     */
    public function depositStatus($depositId)
    {
        $user = auth()->user();
        $depositRequest = DepositRequest::where('deposit_id', $depositId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'deposit_id' => $depositRequest->deposit_id,
            'status' => $depositRequest->status,
            'is_expired' => $depositRequest->is_expired,
            'seconds_remaining' => $depositRequest->seconds_remaining,
            'utr_number' => $depositRequest->utr_number,
            'admin_notes' => $depositRequest->admin_notes,
            'user_balance' => number_format($user->wallet->fresh()->main_balance, 2),
        ]);
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'upi_id' => 'nullable|string',
        ]);

        $user = auth()->user();

        // Enforce approved bank card requirement
        if ($request->filled('bank_account_id')) {
            $bank = BankAccount::where('id', $request->bank_account_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$bank || $bank->status !== 'approved') {
                return back()->with('error', 'Selected Bank Account is pending admin approval or invalid. You can only withdraw to an APPROVED bank card.');
            }
        } else {
            // Check if user has at least one approved bank card
            $approvedCount = BankAccount::where('user_id', $user->id)->where('status', 'approved')->count();
            if ($approvedCount === 0) {
                return back()->with('error', 'No approved bank account linked. Please add your Bank Details in Profile and wait for admin approval before withdrawing.');
            }
        }

        try {
            $withdrawal = $this->paymentService->requestWithdrawal(
                $user->id,
                $request->amount,
                $request->bank_account_id,
                $request->upi_id
            );

            return back()->with('success', "Withdrawal request of ₹{$request->amount} submitted! Tx ID: {$withdrawal->transaction_id}. Track progress in Wallet History.");
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
            return back()->with('success', "₹{$request->amount} transferred from Commission to Main Wallet!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
