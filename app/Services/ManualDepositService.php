<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\DepositProof;
use App\Models\DepositRequest;
use App\Models\DepositVerification;
use App\Models\MerchantAccount;
use App\Models\MerchantAssignmentLog;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ManualDepositService
{
    protected MerchantLoadBalancerService $loadBalancer;
    protected WalletService $walletService;

    public function __construct(
        MerchantLoadBalancerService $loadBalancer,
        WalletService $walletService
    ) {
        $this->loadBalancer = $loadBalancer;
        $this->walletService = $walletService;
    }

    /**
     * Generate unique deposit ID e.g. DEP20260802849201
     */
    public function generateDepositId(): string
    {
        $prefix = 'DEP' . Carbon::now()->format('Ymd');
        do {
            $random = strtoupper(Str::random(6));
            $depositId = $prefix . $random;
        } while (DepositRequest::where('deposit_id', $depositId)->exists());

        return $depositId;
    }

    /**
     * Create a new deposit request & assign optimal merchant.
     */
    public function createDepositRequest(User $user, float $amount, string $paymentMethod = 'upi', ?int $merchantId = null): DepositRequest
    {
        return DB::transaction(function () use ($user, $amount, $paymentMethod, $merchantId) {
            // Cancel any pending stale deposit request older than 30 mins for user
            DepositRequest::where('user_id', $user->id)
                ->where('status', 'pending')
                ->whereNull('utr_number')
                ->where('expires_at', '<', Carbon::now())
                ->update(['status' => 'expired']);

            if ($merchantId) {
                $merchant = MerchantAccount::findOrFail($merchantId);
            } else {
                $merchant = $this->loadBalancer->selectOptimalMerchant($amount, $paymentMethod);
            }

            $depositId = $this->generateDepositId();
            $expiresAt = Carbon::now()->addMinutes(30);

            $depositRequest = DepositRequest::create([
                'deposit_id' => $depositId,
                'user_id' => $user->id,
                'merchant_account_id' => $merchant->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'expires_at' => $expiresAt,
            ]);

            // Log merchant assignment
            MerchantAssignmentLog::create([
                'deposit_request_id' => $depositRequest->id,
                'merchant_account_id' => $merchant->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'assignment_reason' => 'Load Balancer Assigned (Capacity: ₹' . number_format($merchant->remaining_capacity, 2) . ')',
                'assigned_at' => Carbon::now(),
            ]);

            // Notify user
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Deposit Request Created',
                'message' => "Deposit Request #{$depositId} created for ₹" . number_format($amount, 2) . ". Please transfer to assigned merchant within 30 minutes.",
                'is_read' => false,
            ]);

            return $depositRequest;
        });
    }

    /**
     * Submit UTR & Payment Proof Screenshot
     */
    public function submitPaymentProof(
        DepositRequest $depositRequest,
        string $utrNumber,
        ?UploadedFile $proofFile = null,
        ?string $userRemarks = null
    ): DepositRequest {
        return DB::transaction(function () use ($depositRequest, $utrNumber, $proofFile, $userRemarks) {
            if (in_array($depositRequest->status, ['approved', 'rejected', 'cancelled'])) {
                throw new Exception("Deposit request #{$depositRequest->deposit_id} is already {$depositRequest->status}.");
            }

            $utrNumber = trim($utrNumber);

            // Check duplicate UTR
            $duplicate = DepositRequest::where('utr_number', $utrNumber)
                ->where('id', '!=', $depositRequest->id)
                ->whereIn('status', ['pending', 'verified', 'approved'])
                ->first();

            if ($duplicate) {
                throw new Exception("UTR Number '{$utrNumber}' has already been submitted for Deposit #{$duplicate->deposit_id}. Duplicate UTRs are strictly prohibited.");
            }

            $filePath = null;
            if ($proofFile) {
                $destinationPath = public_path('uploads/proofs');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $filename = 'proof_' . $depositRequest->deposit_id . '_' . time() . '.' . $proofFile->getClientOriginalExtension();
                $proofFile->move($destinationPath, $filename);
                $filePath = 'uploads/proofs/' . $filename;

                DepositProof::create([
                    'deposit_request_id' => $depositRequest->id,
                    'file_path' => $filePath,
                    'file_type' => $proofFile->getClientOriginalExtension(),
                    'original_name' => $proofFile->getClientOriginalName(),
                    'uploaded_at' => Carbon::now(),
                ]);
            }

            $depositRequest->update([
                'utr_number' => $utrNumber,
                'user_remarks' => $userRemarks,
                'status' => 'pending',
            ]);

            // Create verification audit log entry
            DepositVerification::create([
                'deposit_request_id' => $depositRequest->id,
                'admin_id' => null,
                'status_from' => 'pending',
                'status_to' => 'pending_verification',
                'verification_notes' => "User submitted UTR: {$utrNumber}",
                'verified_at' => Carbon::now(),
            ]);

            Notification::create([
                'user_id' => $depositRequest->user_id,
                'title' => 'Deposit Payment Submitted',
                'message' => "Payment proof & UTR #{$utrNumber} submitted for Deposit #{$depositRequest->deposit_id}. Pending admin verification.",
                'is_read' => false,
            ]);

            return $depositRequest;
        });
    }

    /**
     * Approve Deposit and credit wallet via WalletService
     */
    public function approveDeposit(DepositRequest $depositRequest, Admin $admin, ?string $adminNotes = null): array
    {
        return DB::transaction(function () use ($depositRequest, $admin, $adminNotes) {
            if ($depositRequest->status === 'approved') {
                throw new Exception("Deposit #{$depositRequest->deposit_id} is already approved.");
            }

            $oldStatus = $depositRequest->status;
            $user = $depositRequest->user;
            $amount = (float)$depositRequest->amount;

            // 1. Credit User Wallet via WalletService
            $wallet = $this->walletService->credit(
                $user->id,
                $amount,
                'main',
                'deposit',
                $depositRequest->deposit_id,
                "Manual Deposit Approval #{$depositRequest->deposit_id} (UTR: {$depositRequest->utr_number})"
            );

            // 2. Update Deposit Request status
            $depositRequest->update([
                'status' => 'approved',
                'admin_notes' => $adminNotes,
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now(),
            ]);

            // 3. Update Merchant's daily total
            if ($depositRequest->merchantAccount) {
                $depositRequest->merchantAccount->increment('current_daily_total', $amount);
            }

            // 4. Log Audit Trail
            DepositVerification::create([
                'deposit_request_id' => $depositRequest->id,
                'admin_id' => $admin->id,
                'status_from' => $oldStatus,
                'status_to' => 'approved',
                'verification_notes' => "Approved by Admin {$admin->name}. Note: {$adminNotes}",
                'verified_at' => Carbon::now(),
            ]);

            // 5. Create Notification
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Deposit Approved! 🎉',
                'message' => "Your Deposit #{$depositRequest->deposit_id} of ₹" . number_format($amount, 2) . " has been approved & credited to your wallet balance!",
                'is_read' => false,
            ]);

            return [
                'success' => true,
                'message' => "Deposit #{$depositRequest->deposit_id} approved successfully!",
                'new_balance' => number_format($wallet->main_balance, 2),
            ];
        });
    }

    /**
     * Reject Deposit
     */
    public function rejectDeposit(DepositRequest $depositRequest, Admin $admin, string $reason): array
    {
        return DB::transaction(function () use ($depositRequest, $admin, $reason) {
            if ($depositRequest->status === 'approved') {
                throw new Exception("Cannot reject an already approved deposit.");
            }

            $oldStatus = $depositRequest->status;

            $depositRequest->update([
                'status' => 'rejected',
                'admin_notes' => $reason,
                'rejected_by' => $admin->id,
                'rejected_at' => Carbon::now(),
            ]);

            DepositVerification::create([
                'deposit_request_id' => $depositRequest->id,
                'admin_id' => $admin->id,
                'status_from' => $oldStatus,
                'status_to' => 'rejected',
                'verification_notes' => "Rejected by Admin {$admin->name}. Reason: {$reason}",
                'verified_at' => Carbon::now(),
            ]);

            Notification::create([
                'user_id' => $depositRequest->user_id,
                'title' => 'Deposit Rejected',
                'message' => "Your Deposit #{$depositRequest->deposit_id} of ₹" . number_format($depositRequest->amount, 2) . " was rejected. Reason: {$reason}",
                'is_read' => false,
            ]);

            return [
                'success' => true,
                'message' => "Deposit #{$depositRequest->deposit_id} rejected.",
            ];
        });
    }

    /**
     * Check if UTR is duplicate
     */
    public function checkDuplicateUTR(?string $utrNumber, ?int $ignoreId = null): ?DepositRequest
    {
        if (empty($utrNumber)) return null;

        return DepositRequest::where('utr_number', trim($utrNumber))
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->whereIn('status', ['pending', 'verified', 'approved'])
            ->first();
    }
}
