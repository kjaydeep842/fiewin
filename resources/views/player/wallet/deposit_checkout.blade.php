@extends('layouts.app')

@section('content')
<style>
    .checkout-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .merchant-banner {
        background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
        color: #ffffff;
        padding: 20px;
        border-radius: 16px;
    }

    .copy-box {
        background: rgba(255, 255, 255, 0.08);
        border: 1px dashed rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 10px 14px;
    }

    .stepper-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        position: relative;
        padding-bottom: 20px;
    }

    .stepper-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 15px;
        top: 30px;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }

    .stepper-item.active:not(:last-child)::after {
        background: #10B981;
    }

    .stepper-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.85rem;
        background: #f1f5f9;
        color: #64748b;
        z-index: 2;
        flex-shrink: 0;
    }

    .stepper-item.completed .stepper-icon,
    .stepper-item.active .stepper-icon {
        background: #10B981;
        color: #ffffff;
    }

    .stepper-item.rejected .stepper-icon {
        background: #EF4444;
        color: #ffffff;
    }
</style>

{{-- Page Navigation Header --}}
<div class="gh-card p-3 mb-3">
    <div class="d-flex align-items-center justify-content-between">
        <a href="{{ route('wallet.index') }}" class="btn btn-sm btn-light border rounded-circle flex-shrink-0">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="text-center">
            <h6 class="fw-bold mb-0 text-dark">Deposit Checkout</h6>
            <small class="text-muted font-monospace" style="font-size: 0.72rem;">#{{ $depositRequest->deposit_id }}</small>
        </div>
        <button class="btn btn-sm btn-light border rounded-circle" onclick="window.location.reload()">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
    </div>
</div>

{{-- Main Deposit Checkout Card --}}
<div class="checkout-card p-3 p-sm-4 mb-3">

    {{-- Status Banner & Timer --}}
    <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded-3 bg-light border">
        <div>
            <small class="text-secondary d-block fw-semibold" style="font-size: 0.7rem;">DEPOSIT STATUS</small>
            @if($depositRequest->status === 'approved')
                <span class="badge bg-success fs-6 px-3 py-1 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i>APPROVED & CREDITED</span>
            @elseif($depositRequest->status === 'rejected')
                <span class="badge bg-danger fs-6 px-3 py-1 rounded-pill"><i class="bi bi-x-circle-fill me-1"></i>REJECTED</span>
            @elseif($depositRequest->is_expired)
                <span class="badge bg-secondary fs-6 px-3 py-1 rounded-pill"><i class="bi bi-clock-history me-1"></i>EXPIRED</span>
            @elseif($depositRequest->utr_number)
                <span class="badge bg-warning text-dark fs-6 px-3 py-1 rounded-pill"><i class="bi bi-hourglass-split me-1"></i>PENDING ADMIN VERIFICATION</span>
            @else
                <span class="badge bg-primary fs-6 px-3 py-1 rounded-pill"><i class="bi bi-info-circle-fill me-1"></i>AWAITING PAYMENT & UTR</span>
            @endif
        </div>

        @if($depositRequest->status === 'pending' && !$depositRequest->is_expired)
            <div class="text-end">
                <small class="text-danger d-block fw-bold" style="font-size: 0.68rem;">TIME REMAINING</small>
                <div id="checkoutTimer" class="fw-bold font-monospace text-danger fs-5">--:--</div>
            </div>
        @endif
    </div>

    {{-- Assigned Merchant Collection Details --}}
    @if($depositRequest->merchantAccount)
        <div class="merchant-banner mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-primary rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">AUTHORIZED MERCHANT</span>
                <span class="fs-4 text-warning font-monospace fw-bold">₹{{ number_format($depositRequest->amount, 2) }}</span>
            </div>

            {{-- Merchant Name & QR --}}
            <div class="row align-items-center g-3 mb-3">
                <div class="col-8">
                    <h5 class="fw-bold mb-1 text-white">{{ $depositRequest->merchantAccount->name }}</h5>
                    <small class="text-secondary d-block" style="font-size: 0.75rem;">Account Holder: {{ $depositRequest->merchantAccount->account_holder ?? $depositRequest->merchantAccount->name }}</small>
                </div>
                @if($depositRequest->merchantAccount->qr_image)
                    <div class="col-4 text-end">
                        <img src="{{ asset($depositRequest->merchantAccount->qr_image) }}"
                             alt="Merchant QR Code"
                             class="img-fluid rounded-3 border border-white border-2 shadow-sm"
                             style="max-height: 90px; cursor: pointer;"
                             data-bs-toggle="modal" data-bs-target="#qrModal">
                        <small class="d-block text-warning" style="font-size: 0.65rem;">Tap QR to Zoom</small>
                    </div>
                @endif
            </div>

            {{-- UPI ID Box --}}
            @if($depositRequest->merchantAccount->upi_id)
                <div class="copy-box d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <small class="text-secondary d-block" style="font-size: 0.65rem;">MERCHANT UPI ID</small>
                        <span id="merchantUpiId" class="fw-bold font-monospace text-warning fs-6">{{ $depositRequest->merchantAccount->upi_id }}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-warning fw-bold rounded-pill px-3" onclick="copyText('merchantUpiId', 'UPI ID')">
                        <i class="bi bi-copy me-1"></i>COPY
                    </button>
                </div>
            @endif

            {{-- Bank Account Box --}}
            @if($depositRequest->merchantAccount->bank_name)
                <div class="p-3 rounded-3 mt-2" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255,255,255,0.1);">
                    <small class="text-secondary d-block fw-semibold mb-2" style="font-size: 0.68rem;">BANK TRANSFER DETAILS</small>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-secondary">Bank Name:</small>
                        <span class="fw-bold text-white small">{{ $depositRequest->merchantAccount->bank_name }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-secondary">Account Number:</small>
                        <span id="bankAccNo" class="fw-bold font-monospace text-warning small">{{ $depositRequest->merchantAccount->account_number }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-secondary">IFSC Code:</small>
                        <span id="bankIfsc" class="fw-bold font-monospace text-white small">{{ $depositRequest->merchantAccount->ifsc }}</span>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Payment Proof Submission Form --}}
    @if($depositRequest->status === 'pending' && !$depositRequest->is_expired)
        <div class="p-3 bg-light rounded-4 border mb-4">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-upload text-primary me-2"></i>Submit UTR & Payment Screenshot</h6>

            <form action="{{ route('wallet.deposit.proof', $depositRequest->deposit_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">12-DIGIT UTR / REFERENCE TRANSACTION NO. <span class="text-danger">*</span></label>
                    <input type="text"
                           name="utr_number"
                           class="form-control form-control-lg font-monospace fw-bold"
                           placeholder="Enter 12-digit UTR Number"
                           value="{{ $depositRequest->utr_number }}"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">UPLOAD PAYMENT SCREENSHOT (Optional)</label>
                    <input type="file" name="screenshot" class="form-control" accept="image/*">
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">REMARKS (Optional)</label>
                    <input type="text" name="user_remarks" class="form-control" placeholder="Any note for admin" value="{{ $depositRequest->user_remarks }}">
                </div>

                <button type="submit" class="btn gh-btn-success w-100 py-3 fs-6 fw-bold rounded-3 shadow">
                    <i class="bi bi-check-circle-fill me-2"></i>SUBMIT UTR FOR VERIFICATION
                </button>
            </form>
        </div>
    @endif

    {{-- Processing Timeline Stepper --}}
    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check text-primary me-2"></i>Deposit Timeline</h6>
    <div class="ps-2">
        {{-- Step 1 --}}
        <div class="stepper-item completed">
            <div class="stepper-icon"><i class="bi bi-check"></i></div>
            <div>
                <div class="fw-bold text-dark" style="font-size: 0.85rem;">Request Generated</div>
                <small class="text-muted" style="font-size: 0.72rem;">#{{ $depositRequest->deposit_id }} assigned to {{ $depositRequest->merchantAccount ? $depositRequest->merchantAccount->name : 'Merchant' }}</small>
            </div>
        </div>

        {{-- Step 2 --}}
        <div class="stepper-item {{ $depositRequest->utr_number ? 'completed' : 'active' }}">
            <div class="stepper-icon">{{ $depositRequest->utr_number ? '✓' : '2' }}</div>
            <div>
                <div class="fw-bold text-dark" style="font-size: 0.85rem;">Payment & UTR Submission</div>
                <small class="text-muted" style="font-size: 0.72rem;">
                    {{ $depositRequest->utr_number ? "UTR #{$depositRequest->utr_number} submitted" : "Transfer ₹" . number_format($depositRequest->amount, 2) . " & enter UTR" }}
                </small>
            </div>
        </div>

        {{-- Step 3 --}}
        <div class="stepper-item {{ $depositRequest->status === 'approved' ? 'completed' : ($depositRequest->status === 'rejected' ? 'rejected' : ($depositRequest->utr_number ? 'active' : '')) }}">
            <div class="stepper-icon">
                @if($depositRequest->status === 'approved') ✓ @elseif($depositRequest->status === 'rejected') ✕ @else 3 @endif
            </div>
            <div>
                <div class="fw-bold text-dark" style="font-size: 0.85rem;">Admin Verification</div>
                <small class="text-muted" style="font-size: 0.72rem;">
                    @if($depositRequest->status === 'approved') Verified & Approved @elseif($depositRequest->status === 'rejected') Rejected: {{ $depositRequest->admin_notes }} @else Admin verifying UTR & statement @endif
                </small>
            </div>
        </div>

        {{-- Step 4 --}}
        <div class="stepper-item {{ $depositRequest->status === 'approved' ? 'completed' : '' }}">
            <div class="stepper-icon">✓</div>
            <div>
                <div class="fw-bold text-dark" style="font-size: 0.85rem;">Wallet Balance Credited</div>
                <small class="text-muted" style="font-size: 0.72rem;">
                    @if($depositRequest->status === 'approved') ₹{{ number_format($depositRequest->amount, 2) }} added to Main Balance @else Pending completion @endif
                </small>
            </div>
        </div>
    </div>
</div>

{{-- QR Code Zoom Modal --}}
@if($depositRequest->merchantAccount && $depositRequest->merchantAccount->qr_image)
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3 rounded-4">
            <h6 class="fw-bold text-dark mb-2">Scan QR Code to Pay</h6>
            <img src="{{ asset($depositRequest->merchantAccount->qr_image) }}" alt="QR Code" class="img-fluid rounded-3 mb-2 border">
            <span class="fs-5 font-monospace fw-bold text-primary mb-2">₹{{ number_format($depositRequest->amount, 2) }}</span>
            <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Close</button>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    function copyText(elementId, label) {
        const el = document.getElementById(elementId);
        if (!el) return;
        const text = el.textContent || el.innerText;
        navigator.clipboard.writeText(text).then(() => {
            alert(label + ' copied to clipboard: ' + text);
            if (window.soundManager) window.soundManager.play('click');
        });
    }

    // Timer Countdown
    let secondsLeft = {{ $depositRequest->seconds_remaining }};
    const timerEl = document.getElementById('checkoutTimer');

    if (timerEl && secondsLeft > 0) {
        const interval = setInterval(() => {
            secondsLeft--;
            if (secondsLeft <= 0) {
                clearInterval(interval);
                timerEl.textContent = 'EXPIRED';
                window.location.reload();
            } else {
                const mins = String(Math.floor(secondsLeft / 60)).padStart(2, '0');
                const secs = String(secondsLeft % 60).padStart(2, '0');
                timerEl.textContent = `${mins}:${secs}`;
            }
        }, 1000);
    }
</script>
@endpush
@endsection
