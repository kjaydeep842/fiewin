<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['wallet', 'bankAccounts']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20);
        $pendingBankAccounts = BankAccount::where('status', 'pending')->with('user')->get();

        return view('admin.users.index', compact('users', 'pendingBankAccounts'));
    }

    public function toggleStatus(User $user)
    {
        $user->status = ($user->status === 'active') ? 'blocked' : 'active';
        $user->save();

        return back()->with('success', "User status updated to {$user->status}");
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile' => 'required|string|unique:users,mobile,' . $user->id,
            'status' => 'required|in:active,blocked',
            'kyc_status' => 'required|in:unverified,pending,verified,rejected',
            'main_balance' => 'nullable|numeric|min:0',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'status' => $request->status,
            'kyc_status' => $request->kyc_status,
        ]);

        if ($request->filled('main_balance') && $user->wallet) {
            $user->wallet->update([
                'main_balance' => (float) $request->main_balance,
            ]);
        }

        return back()->with('success', "User details for {$user->name} updated successfully!");
    }

    public function destroy(User $user)
    {
        $name = $user->name;
        $user->delete();

        return back()->with('success', "User {$name} deleted successfully.");
    }

    public function approveBankCard(BankAccount $bankAccount)
    {
        $bankAccount->update(['status' => 'approved']);

        Notification::create([
            'user_id' => $bankAccount->user_id,
            'title' => 'Bank Account Approved! ✅',
            'message' => "Your bank account ({$bankAccount->bank_name} - A/C: {$bankAccount->account_number}) has been approved by Admin and is ready for withdrawals.",
            'type' => 'bank_approved',
            'is_read' => false,
        ]);

        return back()->with('success', "Bank Account #{$bankAccount->id} for user #{$bankAccount->user_id} approved!");
    }

    public function rejectBankCard(Request $request, BankAccount $bankAccount)
    {
        $reason = $request->input('reason', 'Invalid bank details or holder mismatch.');
        $bankAccount->update([
            'status' => 'rejected',
            'admin_notes' => $reason,
        ]);

        Notification::create([
            'user_id' => $bankAccount->user_id,
            'title' => 'Bank Account Verification Failed',
            'message' => "Your bank account ({$bankAccount->bank_name}) was rejected. Reason: {$reason}. Please update your details in Profile.",
            'type' => 'bank_rejected',
            'is_read' => false,
        ]);

        return back()->with('success', "Bank Account #{$bankAccount->id} rejected.");
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $userId = $request->input('user_id');
        $type = $request->input('type', 'promo');

        if ($userId) {
            Notification::create([
                'user_id' => $userId,
                'title' => $request->title,
                'message' => $request->message,
                'type' => $type,
                'is_read' => false,
            ]);
            return back()->with('success', 'Notification sent to user successfully!');
        }

        $users = \App\Models\User::all();
        foreach ($users as $u) {
            Notification::create([
                'user_id' => $u->id,
                'title' => $request->title,
                'message' => $request->message,
                'type' => $type,
                'is_read' => false,
            ]);
        }

        return back()->with('success', "Notification broadcasted to all " . $users->count() . " players!");
    }
}
