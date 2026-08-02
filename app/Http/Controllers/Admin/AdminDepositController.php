<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DepositRequest;
use App\Models\MerchantAccount;
use App\Services\ManualDepositService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDepositController extends Controller
{
    protected ManualDepositService $depositService;

    public function __construct(ManualDepositService $depositService)
    {
        $this->depositService = $depositService;
    }

    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 'pending');
        $search = $request->query('search');
        $merchantId = $request->query('merchant_id');

        $query = DepositRequest::with(['user', 'merchantAccount', 'proofs', 'verifications', 'approver'])
            ->orderBy('id', 'desc');

        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($merchantId) {
            $query->where('merchant_account_id', $merchantId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('deposit_id', 'like', "%{$search}%")
                  ->orWhere('utr_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $deposits = $query->paginate(20)->withQueryString();
        $merchants = MerchantAccount::all();

        // Count summary
        $pendingCount = DepositRequest::where('status', 'pending')->count();
        $approvedCount = DepositRequest::where('status', 'approved')->count();
        $rejectedCount = DepositRequest::where('status', 'rejected')->count();

        return view('admin.deposits.index', compact(
            'deposits',
            'merchants',
            'statusFilter',
            'search',
            'merchantId',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    public function approve(Request $request, DepositRequest $deposit)
    {
        $admin = auth('admin')->user() ?? auth()->user();
        $notes = $request->input('admin_notes', 'Manual Admin Verification Approved');

        try {
            $res = $this->depositService->approveDeposit($deposit, $admin, $notes);
            return redirect()->back()->with('success', $res['message']);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, DepositRequest $deposit)
    {
        $request->validate(['reason' => 'required|string|max:255']);
        $admin = auth('admin')->user() ?? auth()->user();

        try {
            $res = $this->depositService->rejectDeposit($deposit, $admin, $request->input('reason'));
            return redirect()->back()->with('success', $res['message']);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function bulkApprove(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No deposits selected for approval.');
        }

        $admin = auth('admin')->user() ?? auth()->user();
        $approvedCount = 0;
        $errors = [];

        foreach ($ids as $id) {
            $deposit = DepositRequest::find($id);
            if ($deposit && $deposit->status === 'pending') {
                try {
                    $this->depositService->approveDeposit($deposit, $admin, 'Bulk Approved by Admin');
                    $approvedCount++;
                } catch (\Throwable $e) {
                    $errors[] = "#{$deposit->deposit_id}: " . $e->getMessage();
                }
            }
        }

        $msg = "Bulk approved {$approvedCount} deposits successfully!";
        if (!empty($errors)) {
            $msg .= ' Warnings: ' . implode(' | ', $errors);
        }

        return redirect()->back()->with('success', $msg);
    }

    public function bulkReject(Request $request)
    {
        $ids = $request->input('ids', []);
        $reason = $request->input('reason', 'Bulk rejected by admin verification.');

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No deposits selected for rejection.');
        }

        $admin = auth('admin')->user() ?? auth()->user();
        $rejectedCount = 0;

        foreach ($ids as $id) {
            $deposit = DepositRequest::find($id);
            if ($deposit && $deposit->status === 'pending') {
                try {
                    $this->depositService->rejectDeposit($deposit, $admin, $reason);
                    $rejectedCount++;
                } catch (\Throwable $e) {
                    // Ignore transient
                }
            }
        }

        return redirect()->back()->with('success', "Bulk rejected {$rejectedCount} deposits.");
    }

    public function exportCSV(Request $request): StreamedResponse
    {
        $filename = 'deposits_report_' . date('Y-m-d_H-i-s') . '.csv';

        $deposits = DepositRequest::with(['user', 'merchantAccount', 'approver'])
            ->orderBy('id', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($deposits) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Deposit ID',
                'User Name',
                'User Phone',
                'Merchant Name',
                'Amount (₹)',
                'Payment Method',
                'UTR Number',
                'Status',
                'Created At',
                'Approved/Rejected At',
                'Processed By',
            ]);

            foreach ($deposits as $d) {
                fputcsv($file, [
                    $d->deposit_id,
                    $d->user ? $d->user->name : 'N/A',
                    $d->user ? $d->user->phone : 'N/A',
                    $d->merchantAccount ? $d->merchantAccount->name : 'N/A',
                    $d->amount,
                    strtoupper($d->payment_method),
                    $d->utr_number ?? 'Pending',
                    strtoupper($d->status),
                    $d->created_at ? $d->created_at->format('Y-m-d H:i:s') : '',
                    $d->approved_at ? $d->approved_at->format('Y-m-d H:i:s') : ($d->rejected_at ? $d->rejected_at->format('Y-m-d H:i:s') : ''),
                    $d->approver ? $d->approver->name : ($d->rejecter ? $d->rejecter->name : 'System'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
