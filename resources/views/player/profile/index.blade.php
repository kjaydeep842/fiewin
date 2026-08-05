@extends('layouts.app')

@section('content')
<!-- User Profile Header -->
<div class="gh-card p-4 mb-3">
    <div class="d-flex align-items-center gap-3">
        <div class="display-5 text-primary p-2 bg-light rounded-circle border"><i class="bi bi-person-fill"></i></div>
        <div>
            <h4 class="fw-bold text-dark mb-0">{{ $user->name }}</h4>
            <span class="text-secondary small"><i class="bi bi-phone me-1"></i>{{ $user->mobile }} | <i class="bi bi-envelope me-1"></i>{{ $user->email }}</span>
            <div class="mt-2">
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 me-1">STATUS: {{ strtoupper($user->status) }}</span>
                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">KYC: {{ strtoupper($user->kyc_status) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Profile Action Cards -->
<div class="row g-3 mb-3">
    <!-- Saved Bank Accounts -->
    <div class="col-md-6">
        <div class="gh-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bank text-primary me-2"></i>Bank Accounts</h6>
                <button class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#bankModal">+ Add Bank</button>
            </div>

            @forelse($bankAccounts as $bank)
                <div class="p-3 bg-light rounded-3 border mb-2 position-relative">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2 border-bottom pb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-bank text-primary fs-5"></i>
                            <span class="fw-bold text-dark text-uppercase" style="font-size: 0.9rem;">{{ $bank->bank_name }}</span>
                        </div>
                        @if(($bank->status ?? 'pending') === 'approved')
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.68rem;">
                                <i class="bi bi-patch-check-fill me-1"></i>APPROVED
                            </span>
                        @elseif(($bank->status ?? 'pending') === 'rejected')
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.68rem;">
                                <i class="bi bi-x-circle-fill me-1"></i>REJECTED
                            </span>
                        @else
                            <span class="badge bg-warning bg-opacity-25 text-dark border border-warning border-opacity-50 rounded-pill px-2 py-1" style="font-size: 0.68rem; background-color: #FEF08A !important;">
                                <i class="bi bi-clock-history me-1"></i>PENDING VERIFICATION
                            </span>
                        @endif
                    </div>
                    <div class="text-secondary small font-monospace mb-1">
                        A/C: <strong class="text-dark">{{ $bank->account_number }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.78rem;">
                        <span>IFSC: <strong class="text-dark">{{ $bank->ifsc_code }}</strong></span>
                        <span>Holder: <strong class="text-primary">{{ $bank->account_holder }}</strong></span>
                    </div>
                    @if(($bank->status ?? 'pending') === 'rejected' && !empty($bank->admin_notes))
                        <div class="mt-2 text-danger small bg-danger bg-opacity-10 p-2 rounded border border-danger border-opacity-25" style="font-size: 0.72rem;">
                            <i class="bi bi-info-circle me-1"></i><strong>Admin Note:</strong> {{ $bank->admin_notes }}
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-secondary small mb-0">No bank accounts linked yet.</p>
            @endforelse
        </div>
    </div>

    <!-- Security & Password -->
    <div class="col-md-6">
        <div class="gh-card p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-lock-fill text-primary me-2"></i>Update Password</h6>
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                <div class="mb-2">
                    <input type="password" name="current_password" class="form-control form-control-sm" placeholder="Current Password" required>
                </div>
                <div class="mb-2">
                    <input type="password" name="password" class="form-control form-control-sm" placeholder="New Password" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="Confirm New Password" required>
                </div>
                <button type="submit" class="btn gh-btn-primary btn-sm w-100 rounded-pill">UPDATE PASSWORD</button>
            </form>
        </div>
    </div>
</div>

<!-- Legal, Safety & Help Desk Section -->
<div class="gh-card p-3 mb-3">
    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-shield-check text-success me-2"></i>Legal, Compliance & Support</h6>
    <div class="row g-2">
        <div class="col-6 col-md-4">
            <a href="{{ route('privacy') }}" class="p-2 text-decoration-none bg-light border rounded-3 d-flex align-items-center gap-2 text-dark hover-shadow">
                <i class="bi bi-shield-lock-fill text-primary fs-5"></i>
                <span class="small fw-semibold">Privacy Policy</span>
            </a>
        </div>
        <div class="col-6 col-md-4">
            <a href="{{ route('terms') }}" class="p-2 text-decoration-none bg-light border rounded-3 d-flex align-items-center gap-2 text-dark hover-shadow">
                <i class="bi bi-file-earmark-text-fill text-primary fs-5"></i>
                <span class="small fw-semibold">Terms & Rules</span>
            </a>
        </div>
        <div class="col-6 col-md-4">
            <a href="{{ route('responsible-gaming') }}" class="p-2 text-decoration-none bg-light border rounded-3 d-flex align-items-center gap-2 text-dark hover-shadow">
                <i class="bi bi-heart-pulse-fill text-danger fs-5"></i>
                <span class="small fw-semibold">Responsible Gaming</span>
            </a>
        </div>
        <div class="col-6 col-md-4">
            <a href="{{ route('legal-availability') }}" class="p-2 text-decoration-none bg-light border rounded-3 d-flex align-items-center gap-2 text-dark hover-shadow">
                <i class="bi bi-geo-alt-fill text-warning fs-5"></i>
                <span class="small fw-semibold">Legal Availability</span>
            </a>
        </div>
        <div class="col-6 col-md-4">
            <a href="{{ route('security') }}" class="p-2 text-decoration-none bg-light border rounded-3 d-flex align-items-center gap-2 text-dark hover-shadow">
                <i class="bi bi-lock-fill text-success fs-5"></i>
                <span class="small fw-semibold">HTTPS Security</span>
            </a>
        </div>
        <div class="col-6 col-md-4">
            <a href="{{ route('contact') }}" class="p-2 text-decoration-none bg-light border rounded-3 d-flex align-items-center gap-2 text-dark hover-shadow">
                <i class="bi bi-headset text-info fs-5"></i>
                <span class="small fw-semibold">Contact & Support</span>
            </a>
        </div>
    </div>
</div>

<!-- Add Bank Account Modal -->
<div class="modal fade" id="bankModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-light">
                <h5 class="modal-title fw-bold text-dark">Add Bank Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('profile.bank') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-semibold">ACCOUNT HOLDER NAME</label>
                        <input type="text" name="account_holder" class="form-control text-uppercase" placeholder="Rahul Sharma" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-semibold">BANK NAME</label>
                        <input type="text" name="bank_name" class="form-control text-uppercase" placeholder="HDFC BANK" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-semibold">ACCOUNT NUMBER</label>
                        <input type="text" name="account_number" class="form-control" placeholder="5010023456789" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-semibold">IFSC CODE</label>
                        <input type="text" name="ifsc_code" class="form-control text-uppercase" placeholder="HDFC0001234" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-semibold">UPI ID (OPTIONAL)</label>
                        <input type="text" name="upi_id" class="form-control" placeholder="user@upi">
                    </div>
                </div>
                <div class="modal-footer border-light">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn gh-btn-success rounded-pill px-4">SAVE BANK ACCOUNT</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Logout Button -->
<div class="text-center mt-3">
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-outline-danger w-100 py-2 rounded-pill fw-bold"><i class="bi bi-box-arrow-right me-2"></i>LOGOUT</button>
    </form>
</div>
@endsection
