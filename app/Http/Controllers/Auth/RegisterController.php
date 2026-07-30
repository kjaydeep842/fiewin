<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    protected UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function showRegistrationForm(Request $request)
    {
        $refCode = $request->query('ref');
        return view('auth.register', compact('refCode'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mobile' => 'required|string|max:15|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'referral_code' => 'nullable|string|exists:users,referral_code',
        ]);

        $referredBy = null;
        if (!empty($data['referral_code'])) {
            $referrer = $this->userRepo->findByReferralCode($data['referral_code']);
            if ($referrer) {
                $referredBy = $referrer->id;
            }
        }

        $user = $this->userRepo->createPlayer([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'password' => bcrypt($data['password']),
            'referred_by' => $referredBy,
        ]);

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Registration successful! Welcome bonus credited to your wallet.');
    }
}
