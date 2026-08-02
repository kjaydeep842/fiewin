<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\GameBet;
use App\Models\Referral;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class ReferralCommissionService
{
    /**
     * Commission rates:
     * Level 1: 3.0%
     * Level 2: 2.0%
     * Level 3: 1.0%
     */
    protected array $levelRates = [
        1 => 3.0,
        2 => 2.0,
        3 => 1.0,
    ];

    public function processBetCommission(object $bet): void
    {
        $bettor = $bet->user;
        if (!$bettor || !$bettor->referred_by) {
            return;
        }

        $currentReferrerId = $bettor->referred_by;

        for ($level = 1; $level <= 3; $level++) {
            if (!$currentReferrerId) {
                break;
            }

            $referrer = User::find($currentReferrerId);
            if (!$referrer) {
                break;
            }

            $rate = $this->levelRates[$level] ?? 0.0;
            $commissionAmount = round(($bet->bet_amount * $rate) / 100.0, 4);

            if ($commissionAmount > 0) {
                // Record commission log
                Commission::create([
                    'user_id' => $referrer->id,
                    'source_user_id' => $bettor->id,
                    'bet_id' => ($bet instanceof GameBet) ? $bet->id : null,
                    'level' => $level,
                    'amount' => $commissionAmount,
                    'rate_percentage' => $rate,
                    'status' => 'credited',
                ]);

                // Update referral cumulative total
                Referral::updateOrCreate(
                    ['referrer_id' => $referrer->id, 'referee_id' => $bettor->id, 'level' => $level],
                    ['total_commission_earned' => \DB::raw("total_commission_earned + {$commissionAmount}")]
                );

                // Direct credit to referrer's commission wallet balance
                $referrerWallet = Wallet::where('user_id', $referrer->id)->first();
                if ($referrerWallet) {
                    $referrerWallet->commission_balance += $commissionAmount;
                    $referrerWallet->save();

                    WalletTransaction::create([
                        'wallet_id' => $referrerWallet->id,
                        'amount' => $commissionAmount,
                        'balance_type' => 'commission',
                        'transaction_type' => 'referral_bonus',
                        'reference_id' => "COMM_BET_{$bet->id}_L{$level}",
                        'description' => "Level {$level} commission from player {$bettor->name} bet #{$bet->id}",
                        'balance_after' => $referrerWallet->commission_balance,
                    ]);
                }
            }

            // Move to next upstream referrer (Level 2 & Level 3)
            $currentReferrerId = $referrer->referred_by;
        }
    }
}
