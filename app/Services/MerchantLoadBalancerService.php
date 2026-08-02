<?php

namespace App\Services;

use App\Models\MerchantAccount;
use Exception;

class MerchantLoadBalancerService
{
    /**
     * Automatically assign the optimal merchant account based on load, priority, and capacity.
     */
    public function selectOptimalMerchant(float $amount, string $paymentMethod = 'upi', string $region = 'IN'): MerchantAccount
    {
        $activeMerchants = MerchantAccount::where('status', 'active')
            ->where('region', $region)
            ->get();

        if ($activeMerchants->isEmpty()) {
            throw new Exception("No active merchant collection accounts available at the moment.");
        }

        // Filter merchants that support paymentMethod and have enough daily limit remaining
        $eligibleMerchants = $activeMerchants->filter(function (MerchantAccount $merchant) use ($amount, $paymentMethod) {
            $supportedTypes = $merchant->supported_payment_types ?? ['upi', 'bank_transfer', 'qr'];
            if (!in_array($paymentMethod, $supportedTypes) && !in_array('all', $supportedTypes)) {
                return false;
            }
            return $merchant->remaining_capacity >= $amount;
        });

        if ($eligibleMerchants->isEmpty()) {
            // Fallback: Pick active merchant with highest remaining capacity
            $fallback = $activeMerchants->sortByDesc(fn($m) => $m->remaining_capacity)->first();
            if (!$fallback) {
                throw new Exception("Merchant collection limits reached for today. Please try again later.");
            }
            return $fallback;
        }

        // Sort by load ratio ascending (least-loaded first), then by priority descending
        $selected = $eligibleMerchants->sort(function (MerchantAccount $a, MerchantAccount $b) {
            $ratioA = $a->load_ratio;
            $ratioB = $b->load_ratio;

            if (abs($ratioA - $ratioB) > 0.05) { // 5% threshold
                return $ratioA <=> $ratioB; // Least loaded first
            }

            return $b->priority <=> $a->priority; // Higher priority first
        })->first();

        return $selected;
    }
}
