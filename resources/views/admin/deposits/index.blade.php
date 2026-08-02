@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">Manual Deposit Verification Center</h4>
        <small class="text-secondary">Audit payment proofs, UTR numbers & credit user wallets via WalletService</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.deposits.export', request()->query()) }}" class="btn btn-outline-success btn-sm fw-bold rounded-pill">
            <i class="bi bi-file-earmark-excel me-1"></i>Export CSV Report
        </a>
    </div>
</div>

{{-- Summary Badges --}}
<div class="row g-2 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-warning bg-opacity-10 border-start border-warning border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted d-block fw-semibold">PENDING VERIFICATION</small>
                    <h3 class="fw-bold text-warning mb-0">{{ $pendingCount }}</h3>
                </div>
                <i class="bi bi-hourglass-split display-5 text-warning opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-success bg-opacity-10 border-start border-success border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted d-block fw-semibold">APPROVED DEPOSITS</small>
                    <h3 class="fw-bold text-success mb-0">{{ $approvedCount }}</h3>
                </div>
                <i class="bi bi-check-circle-fill display-5 text-success opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-danger bg-opacity-10 border-start border-danger border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted d-block fw-semibold">REJECTED DEPOSITS</small>
                    <h3 class="fw-bold text-danger mb-0">{{ $rejectedCount }}</h3>
                </div>
                <i class="bi bi-x-circle-fill display-5 text-danger opacity-50"></i>
            </div>
        </div>
    </div>
</div>

{{-- Search & Filter Controls --}}
<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.deposits.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search Deposit ID, UTR, User..." value="{{ $search }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending Verification</option>
                    <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Statuses</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="merchant_id" class="form-select">
                    <option value="">All Merchant Accounts</option>
                    @foreach($merchants as $m)
                        <option value="{{ $m->id }}" {{ $merchantId == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="{{ route('admin.deposits.index') }}" class="btn btn-light border">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Bulk Action Form wrapper --}}
<form id="bulkForm" method="POST">
    @csrf
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <div class="d-flex align-items-center gap-2">
                <input type="checkbox" id="selectAll" class="form-check-input">
                <label for="selectAll" class="small fw-semibold text-secondary">Select All</label>
            </div>
            <div class="d-flex gap-2">
                <button type="submit"
                        formaction="{{ route('admin.deposits.bulk-approve') }}"
                        class="btn btn-sm btn-success rounded-pill px-3"
                        onclick="return confirm('Approve selected deposits and credit user wallets?')">
                    <i class="bi bi-check-all me-1"></i>Bulk Approve
                </button>
                <button type="submit"
                        formaction="{{ route('admin.deposits.bulk-reject') }}"
                        class="btn btn-sm btn-outline-danger rounded-pill px-3"
                        onclick="return confirm('Reject selected deposit requests?')">
                    <i class="bi bi-x-circle me-1"></i>Bulk Reject
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;"></th>
                        <th>Deposit ID</th>
                        <th>User</th>
                        <th>Merchant Assigned</th>
                        <th>Amount (₹)</th>
                        <th>UTR Number</th>
                        <th>Proof</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deposits as $d)
                        @php
                            $dupCount = $d->utr_number ? \App\Models\DepositRequest::where('utr_number', $d->utr_number)->where('id', '!=', $d->id)->count() : 0;
                        @endphp
                        <tr>
                            <td>
                                @if($d->status === 'pending')
                                    <input type="checkbox" name="ids[]" value="{{ $d->id }}" class="form-check-input deposit-checkbox">
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold font-monospace text-primary">#{{ $d->deposit_id }}</span>
                                <small class="d-block text-muted">{{ $d->created_at ? $d->created_at->format('M d, H:i') : '' }}</small>
                            </td>
                            <td>
                                <span class="fw-bold text-dark d-block">{{ $d->user->name ?? 'User #' . $d->user_id }}</span>
                                <small class="text-muted">{{ $d->user->phone ?? $d->user->email ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $d->merchantAccount->name ?? 'N/A' }}</span>
                                @if($d->merchantAccount && $d->merchantAccount->upi_id)
                                    <small class="d-block text-muted font-monospace" style="font-size: 0.7rem;">{{ $d->merchantAccount->upi_id }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="fs-6 fw-bold font-monospace text-success">₹{{ number_format($d->amount, 2) }}</span>
                                <small class="d-block text-muted text-uppercase" style="font-size: 0.68rem;">{{ $d->payment_method }}</small>
                            </td>
                            <td>
                                @if($d->utr_number)
                                    <span class="fw-bold font-monospace text-dark d-block">{{ $d->utr_number }}</span>
                                    @if($dupCount > 0)
                                        <span class="badge bg-danger rounded-pill mt-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>DUPLICATE UTR ({{ $dupCount }})</span>
                                    @endif
                                @else
                                    <span class="badge bg-light text-muted border">Not Submitted Yet</span>
                                @endif
                            </td>
                            <td>
                                @if($d->proofs->isNotEmpty())
                                    @php $proof = $d->proofs->first(); @endphp
                                    <a href="{{ asset($proof->file_path) }}" target="_blank" class="btn btn-sm btn-light border py-1 px-2" style="font-size: 0.75rem;">
                                        <i class="bi bi-image me-1 text-primary"></i>View Proof
                                    </a>
                                @else
                                    <span class="text-muted small">No Proof</span>
                                @endif
                            </td>
                            <td>
                                @if($d->status === 'approved')
                                    <span class="badge bg-success rounded-pill px-2 py-1">APPROVED</span>
                                @elseif($d->status === 'rejected')
                                    <span class="badge bg-danger rounded-pill px-2 py-1">REJECTED</span>
                                @elseif($d->is_expired)
                                    <span class="badge bg-secondary rounded-pill px-2 py-1">EXPIRED</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1">PENDING VERIFICATION</span>
                                @endif
                            </td>
                            <td>
                                @if($d->status === 'pending')
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#approveModal{{ $d->id }}">
                                            Approve
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $d->id }}">
                                            Reject
                                        </button>
                                    </div>
                                @else
                                    <span class="text-muted small">Processed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                No deposit requests found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white py-3">
            {{ $deposits->links() }}
        </div>
    </div>
</form>

{{-- Render Approve & Reject Modals OUTSIDE the bulk form to prevent nested form submissions --}}
@foreach($deposits as $d)
    @if($d->status === 'pending')
        {{-- Approve Modal --}}
        <div class="modal fade" id="approveModal{{ $d->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content rounded-4 border-0">
                    <form action="{{ route('admin.deposits.approve', $d->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold text-success"><i class="bi bi-check-circle-fill me-2"></i>Approve Deposit #{{ $d->deposit_id }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">User:</small>
                                    <span class="fw-bold">{{ $d->user->name ?? 'User #' . $d->user_id }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Amount to Credit:</small>
                                    <span class="fw-bold text-success fs-5">₹{{ number_format($d->amount, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Submitted UTR:</small>
                                    <span class="fw-bold font-monospace text-primary">{{ $d->utr_number ?? 'Not provided' }}</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Admin Notes (Optional)</label>
                                <input type="text" name="admin_notes" class="form-control" value="Verified against bank statement">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success fw-bold">Confirm & Credit Wallet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div class="modal fade" id="rejectModal{{ $d->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content rounded-4 border-0">
                    <form action="{{ route('admin.deposits.reject', $d->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold text-danger"><i class="bi bi-x-circle-fill me-2"></i>Reject Deposit #{{ $d->deposit_id }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Rejection Reason <span class="text-danger">*</span></label>
                                <select class="form-select mb-2" onchange="this.nextElementSibling.value=this.value">
                                    <option value="Invalid UTR Number / UTR not found on statement">Invalid UTR Number / UTR not found on statement</option>
                                    <option value="Payment amount does not match bank statement">Payment amount does not match bank statement</option>
                                    <option value="Duplicate UTR transaction">Duplicate UTR transaction</option>
                                    <option value="Payment proof image is unreadable">Payment proof image is unreadable</option>
                                </select>
                                <input type="text" name="reason" class="form-control" placeholder="Enter reason" required value="Invalid UTR Number / UTR not found on statement">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger fw-bold">Reject Deposit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

@push('scripts')
<script>
    document.getElementById('selectAll')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.deposit-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>
@endpush
@endsection
