<?php

namespace App\Repositories;

use App\Models\Wallet;
use App\Models\WalletTransaction;

class WalletRepository
{
    public function getWalletByUserId(int $userId): ?Wallet
    {
        return Wallet::where('user_id', $userId)->first();
    }

    public function recordTransaction(
        int $walletId,
        float $amount,
        string $balanceType,
        string $transactionType,
        ?string $referenceId = null,
        ?string $description = null,
        float $balanceAfter = 0.00
    ): WalletTransaction {
        return WalletTransaction::create([
            'wallet_id' => $walletId,
            'amount' => $amount,
            'balance_type' => $balanceType,
            'transaction_type' => $transactionType,
            'reference_id' => $referenceId,
            'description' => $description,
            'balance_after' => $balanceAfter,
        ]);
    }
}
