<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

class FinancialController extends Controller
{
    protected PaymentGatewayService $paymentService;

    public function __construct(PaymentGatewayService $paymentService)
    {
        $this->paymentService = $paymentService;
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
        $withdrawal->update([
            'status' => 'approved',
            'processed_at' => now(),
        ]);

        return back()->with('success', "Withdrawal #{$withdrawal->transaction_id} marked as approved!");
    }
}
