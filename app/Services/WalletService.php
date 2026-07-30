<?php

namespace App\Services;

use App\Models\Wallet;
use App\Repositories\WalletRepository;
use Illuminate\Support\Facades\DB;
use Exception;

class WalletService
{
    protected WalletRepository $walletRepo;

    public function __construct(WalletRepository $walletRepo)
    {
        $this->walletRepo = $walletRepo;
    }

    public function credit(
        int $userId,
        float $amount,
        string $balanceType = 'main',
        string $transactionType = 'deposit',
        ?string $referenceId = null,
        ?string $description = null
    ): Wallet {
        return DB::transaction(function () use ($userId, $amount, $balanceType, $transactionType, $referenceId, $description) {
            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->firstOrFail();

            if ($balanceType === 'main') {
                $wallet->main_balance += $amount;
                $balanceAfter = $wallet->main_balance;
            } elseif ($balanceType === 'bonus') {
                $wallet->bonus_balance += $amount;
                $balanceAfter = $wallet->bonus_balance;
            } elseif ($balanceType === 'commission') {
                $wallet->commission_balance += $amount;
                $balanceAfter = $wallet->commission_balance;
            } else {
                throw new Exception("Invalid balance type: {$balanceType}");
            }

            if ($transactionType === 'deposit') {
                $wallet->total_deposited += $amount;
            } elseif ($transactionType === 'win') {
                $wallet->total_winnings += $amount;
            }

            $wallet->save();

            $this->walletRepo->recordTransaction(
                $wallet->id,
                $amount,
                $balanceType,
                $transactionType,
                $referenceId,
                $description,
                $balanceAfter
            );

            return $wallet;
        });
    }

    public function debit(
        int $userId,
        float $amount,
        string $balanceType = 'main',
        string $transactionType = 'bet',
        ?string $referenceId = null,
        ?string $description = null
    ): Wallet {
        return DB::transaction(function () use ($userId, $amount, $balanceType, $transactionType, $referenceId, $description) {
            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->firstOrFail();

            if ($wallet->status === 'frozen') {
                throw new Exception("Account wallet is frozen. Transactions prohibited.");
            }

            if ($balanceType === 'main') {
                if ($wallet->main_balance < $amount) {
                    throw new Exception("Insufficient main balance.");
                }
                $wallet->main_balance -= $amount;
                $balanceAfter = $wallet->main_balance;
            } elseif ($balanceType === 'bonus') {
                if ($wallet->bonus_balance < $amount) {
                    throw new Exception("Insufficient bonus balance.");
                }
                $wallet->bonus_balance -= $amount;
                $balanceAfter = $wallet->bonus_balance;
            } elseif ($balanceType === 'commission') {
                if ($wallet->commission_balance < $amount) {
                    throw new Exception("Insufficient commission balance.");
                }
                $wallet->commission_balance -= $amount;
                $balanceAfter = $wallet->commission_balance;
            }

            $wallet->save();

            $this->walletRepo->recordTransaction(
                $wallet->id,
                -$amount,
                $balanceType,
                $transactionType,
                $referenceId,
                $description,
                $balanceAfter
            );

            return $wallet;
        });
    }

    public function transferCommissionToMain(int $userId, float $amount): Wallet
    {
        return DB::transaction(function () use ($userId, $amount) {
            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->firstOrFail();
            if ($wallet->commission_balance < $amount) {
                throw new Exception("Insufficient commission balance to transfer.");
            }

            $wallet->commission_balance -= $amount;
            $wallet->main_balance += $amount;
            $wallet->save();

            $this->walletRepo->recordTransaction(
                $wallet->id,
                $amount,
                'main',
                'commission_transfer',
                'TRANSFER_' . time(),
                'Commission transferred to Main Wallet balance',
                $wallet->main_balance
            );

            return $wallet;
        });
    }
}
