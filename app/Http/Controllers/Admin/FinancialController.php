<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Notification;
use App\Models\Withdrawal;
use App\Services\PaymentGatewayService;
use App\Services\WalletService;
use Illuminate\Http\Request;

class FinancialController extends Controller
{
    protected PaymentGatewayService $paymentService;
    protected WalletService $walletService;

    public function __construct(PaymentGatewayService $paymentService, WalletService $walletService)
    {
        $this->paymentService = $paymentService;
        $this->walletService = $walletService;
    }

    public function deposits()
    {
        $deposits = Deposit::with('user')->latest()->paginate(20);
        return view('admin.financial.deposits', compact('deposits'));
    }

    public function approveDeposit(Deposit $deposit)
    {
        $this->paymentService->approveDeposit($deposit);
        return back()->with('success', "Deposit #{$deposit->transaction_id} approved and user wallet credited.");
    }

    public function withdrawals()
    {
        $withdrawals = Withdrawal::with(['user', 'bankAccount'])->latest()->paginate(20);
        return view('admin.financial.withdrawals', compact('withdrawals'));
    }

    public function approveWithdrawal(Withdrawal $withdrawal)
    {
        if ($withdrawal->status === 'approved') {
            return back()->with('info', "Withdrawal #{$withdrawal->transaction_id} is already approved.");
        }

        $withdrawal->update([
            'status' => 'approved',
            'processed_at' => now(),
        ]);

        // Create User Notification
        Notification::create([
            'user_id' => $withdrawal->user_id,
            'title' => 'Withdrawal Approved! 🎉',
            'message' => "Your withdrawal request #{$withdrawal->transaction_id} for ₹{$withdrawal->amount} has been approved and transferred to your bank account.",
            'type' => 'withdrawal_approved',
            'is_read' => false,
        ]);

        return back()->with('success', "Withdrawal #{$withdrawal->transaction_id} marked as approved & notification sent to player!");
    }

    public function rejectWithdrawal(Request $request, Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', "Withdrawal #{$withdrawal->transaction_id} is not in pending status.");
        }

        $reason = $request->input('reason', 'Details mismatched or rejected by admin.');

        // Refund debited main balance to user
        $this->walletService->credit(
            $withdrawal->user_id,
            $withdrawal->amount,
            'main',
            'refund',
            "REFUND_{$withdrawal->transaction_id}",
            "Refund for rejected withdrawal #{$withdrawal->transaction_id}"
        );

        $withdrawal->update([
            'status' => 'rejected',
            'processed_at' => now(),
            'admin_notes' => $reason,
        ]);

        // Create User Notification
        Notification::create([
            'user_id' => $withdrawal->user_id,
            'title' => 'Withdrawal Request Rejected',
            'message' => "Your withdrawal request #{$withdrawal->transaction_id} for ₹{$withdrawal->amount} was rejected. Reason: {$reason}. Amount has been refunded to your wallet.",
            'type' => 'withdrawal_rejected',
            'is_read' => false,
        ]);

        return back()->with('success', "Withdrawal #{$withdrawal->transaction_id} rejected, amount refunded to player wallet, and notification sent!");
    }
}
