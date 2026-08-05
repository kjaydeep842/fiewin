<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password request form.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a reset link to the user's email via SMTP.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'We could not find a user account with that email address.',
        ]);

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => bcrypt($token),
                'created_at' => now()
            ]
        );

        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $request->email
        ]);

        try {
            Mail::html("
                <div style='font-family: Arial, sans-serif; max-width: 550px; margin: 0 auto; padding: 25px; border: 1px solid #e5e7eb; border-radius: 12px; background: #ffffff;'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <h2 style='color: #1E88E5; margin: 0;'>GameHub</h2>
                        <p style='color: #6b7280; font-size: 14px;'>Password Reset Request</p>
                    </div>
                    <p style='color: #374151; font-size: 15px;'>Hello,</p>
                    <p style='color: #374151; font-size: 14px; line-height: 1.5;'>
                        We received a request to reset your password for your GameHub account (<strong>{$request->email}</strong>). Click the button below to reset your password:
                    </p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetUrl}' style='background: linear-gradient(135deg, #1E88E5, #42A5F5); color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; display: inline-block;'>RESET YOUR PASSWORD</a>
                    </div>
                    <p style='color: #6b7280; font-size: 13px;'>If you did not request a password reset, please ignore this email.</p>
                    <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;' />
                    <p style='color: #9ca3af; font-size: 12px; text-align: center;'>© GameHub Real-Money Gaming Platform. All rights reserved.</p>
                </div>
            ", function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('GameHub - Password Reset Request');
            });
        } catch (\Throwable $e) {
            logger()->error("Mail send error: " . $e->getMessage());
        }

        return back()->with([
            'status' => 'We have emailed your password reset link to ' . $request->email . '! Please check your inbox and click the link in the email to reset your password.',
            'target_email' => $request->email
        ]);
    }
}
