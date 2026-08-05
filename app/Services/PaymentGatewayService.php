<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Deposit;
use App\Models\PaymentMethod;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class PaymentGatewayService
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Create deposit transaction
     */
    public function initiateDeposit(int $userId, string $methodCode, float $amount, ?string $utrNumber = null, ?string $proofImage = null): Deposit
    {
        $method = PaymentMethod::where('code', $methodCode)->where('is_active', true)->firstOrFail();

        if ($amount < $method->min_amount || $amount > $method->max_amount) {
            throw new Exception("Deposit amount must be between Rs {$method->min_amount} and Rs {$method->max_amount}");
        }

        $bonusAmount = round(($amount * $method->bonus_percentage) / 100.0, 2);
        $txId = 'DEP_' . strtoupper(Str::random(10));

        $deposit = Deposit::create([
            'user_id' => $userId,
            'transaction_id' => $txId,
            'payment_method' => $methodCode,
            'amount' => $amount,
            'bonus_amount' => $bonusAmount,
            'utr_number' => $utrNumber,
            'proof_image' => $proofImage,
            'status' => ($methodCode === 'razorpay' || $methodCode === 'phonepe') ? 'approved' : 'pending',
            'approved_at' => ($methodCode === 'razorpay' || $methodCode === 'phonepe') ? now() : null,
        ]);

        if ($deposit->status === 'approved') {
            $this->walletService->credit(
                $userId,
                $amount,
                'main',
                'deposit',
                $txId,
                "Online Deposit via {$method->name}"
            );

            if ($bonusAmount > 0) {
                $this->walletService->credit(
                    $userId,
                    $bonusAmount,
                    'bonus',
                    'deposit',
                    "BONUS_{$txId}",
                    "Deposit Bonus ({$method->bonus_percentage}%)"
                );
            }
        }

        return $deposit;
    }

    /**
     * Admin approve manual deposit
     */
    public function approveDeposit(Deposit $deposit): Deposit
    {
        return DB::transaction(function () use ($deposit) {
            if ($deposit->status !== 'pending') {
                return $deposit;
            }

            $deposit->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            $this->walletService->credit(
                $deposit->user_id,
                $deposit->amount,
                'main',
                'deposit',
                $deposit->transaction_id,
                "Approved Deposit #{$deposit->transaction_id}"
            );

            if ($deposit->bonus_amount > 0) {
                $this->walletService->credit(
                    $deposit->user_id,
                    $deposit->bonus_amount,
                    'bonus',
                    'deposit',
                    "BONUS_{$deposit->transaction_id}",
                    "Approved Deposit Bonus"
                );
            }

            // Send In-App Notification to User
            \App\Models\Notification::create([
                'user_id' => $deposit->user_id,
                'title' => 'Deposit Approved! 💰',
                'message' => "Your deposit #{$deposit->transaction_id} for ₹" . number_format($deposit->amount, 2) . " has been approved and credited to your wallet balance.",
                'type' => 'deposit_approved',
                'is_read' => false,
            ]);

            return $deposit;
        });
    }

    /**
     * Request withdrawal
     */
    public function requestWithdrawal(int $userId, float $amount, ?int $bankAccountId = null, ?string $upiId = null): Withdrawal
    {
        return DB::transaction(function () use ($userId, $amount, $bankAccountId, $upiId) {
            $fee = 5.00; // Flat fee Rs 5
            $netAmount = max(0.00, $amount - $fee);
            $txId = 'WD_' . strtoupper(Str::random(10));

            // Lock & debit balance immediately to prevent double spending
            $this->walletService->debit(
                $userId,
                $amount,
                'main',
                'withdrawal',
                $txId,
                "Withdrawal request #{$txId}"
            );

            return Withdrawal::create([
                'user_id' => $userId,
                'transaction_id' => $txId,
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'bank_account_id' => $bankAccountId,
                'upi_id' => $upiId,
                'status' => 'pending',
            ]);
        });
    }
}
