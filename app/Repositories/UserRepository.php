<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Str;

class UserRepository
{
    public function createPlayer(array $data): User
    {
        $referralCode = strtoupper(Str::random(8));
        
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
            'password' => $data['password'],
            'referral_code' => $referralCode,
            'referred_by' => $data['referred_by'] ?? null,
            'role' => 'player',
            'status' => 'active',
            'kyc_status' => 'not_submitted',
        ]);

        // Auto-create associated wallet
        Wallet::create([
            'user_id' => $user->id,
            'main_balance' => 50.00, // Sign-up bonus Rs 50
            'bonus_balance' => 20.00,
            'commission_balance' => 0.00,
        ]);

        return $user;
    }

    public function findByReferralCode(string $code): ?User
    {
        return User::where('referral_code', $code)->first();
    }
}
