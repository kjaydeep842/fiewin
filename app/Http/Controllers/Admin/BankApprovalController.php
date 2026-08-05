<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Notification;
use Illuminate\Http\Request;

class BankApprovalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $query = BankAccount::with('user');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $bankAccounts = $query->latest()->paginate(20);

        $pendingCount  = BankAccount::where('status', 'pending')->count();
        $approvedCount = BankAccount::where('status', 'approved')->count();
        $rejectedCount = BankAccount::where('status', 'rejected')->count();

        return view('admin.bank_approvals.index', compact('bankAccounts', 'status', 'pendingCount', 'approvedCount', 'rejectedCount'));
    }

    public function approve(BankAccount $bankAccount)
    {
        $bankAccount->update(['status' => 'approved']);

        Notification::create([
            'user_id' => $bankAccount->user_id,
            'title' => 'Bank Account Approved! ✅',
            'message' => "Your bank account ({$bankAccount->bank_name} - A/C: {$bankAccount->account_number}) has been approved by Admin and is ready for withdrawals.",
            'type' => 'bank_approved',
            'is_read' => false,
        ]);

        return back()->with('success', "Bank Account #{$bankAccount->id} for user {$bankAccount->user?->name} approved!");
    }

    public function reject(Request $request, BankAccount $bankAccount)
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
}
