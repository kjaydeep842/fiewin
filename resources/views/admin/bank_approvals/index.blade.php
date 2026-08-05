@extends('layouts.admin')

@section('page-title', 'Bank Card Approval Center')
@section('page-subtitle', 'Verify and approve player bank accounts for secure withdrawal processing')

@section('content')

<!-- Stat Cards Summary -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="{{ route('admin.bank-approvals.index', ['status' => 'pending']) }}" class="text-decoration-none">
            <div class="admin-stat-card border-warning" style="{{ $status === 'pending' ? 'background: #FEFCE8;' : '' }}">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="text-muted small mb-1 fw-bold">PENDING APPROVALS</div>
                <h3 class="fw-bold mb-0 font-monospace text-warning">{{ number_format($pendingCount) }}</h3>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('admin.bank-approvals.index', ['status' => 'approved']) }}" class="text-decoration-none">
            <div class="admin-stat-card border-success" style="{{ $status === 'approved' ? 'background: #F0FDF4;' : '' }}">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
                <div class="text-muted small mb-1 fw-bold">APPROVED CARDS</div>
                <h3 class="fw-bold mb-0 font-monospace text-success">{{ number_format($approvedCount) }}</h3>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('admin.bank-approvals.index', ['status' => 'rejected']) }}" class="text-decoration-none">
            <div class="admin-stat-card border-danger" style="{{ $status === 'rejected' ? 'background: #FEF2F2;' : '' }}">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div class="text-muted small mb-1 fw-bold">REJECTED CARDS</div>
                <h3 class="fw-bold mb-0 font-monospace text-danger">{{ number_format($rejectedCount) }}</h3>
            </div>
        </a>
    </div>
</div>

<!-- Bank Account Requests Table Card -->
<div class="admin-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bank text-primary me-2 fs-5"></i>Bank Account Submissions</h6>
            <span class="text-muted small">Only approved accounts are permitted for player withdrawals</span>
        </div>

        <!-- Filter Pills -->
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.bank-approvals.index', ['status' => 'pending']) }}" class="btn {{ $status === 'pending' ? 'btn-warning fw-bold' : 'btn-outline-secondary' }}">
                Pending ({{ $pendingCount }})
            </a>
            <a href="{{ route('admin.bank-approvals.index', ['status' => 'approved']) }}" class="btn {{ $status === 'approved' ? 'btn-success fw-bold' : 'btn-outline-secondary' }}">
                Approved ({{ $approvedCount }})
            </a>
            <a href="{{ route('admin.bank-approvals.index', ['status' => 'rejected']) }}" class="btn {{ $status === 'rejected' ? 'btn-danger fw-bold' : 'btn-outline-secondary' }}">
                Rejected ({{ $rejectedCount }})
            </a>
            <a href="{{ route('admin.bank-approvals.index', ['status' => 'all']) }}" class="btn {{ $status === 'all' ? 'btn-dark fw-bold' : 'btn-outline-secondary' }}">
                All Records
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="bg-light text-secondary">
                <tr>
                    <th>Player Info</th>
                    <th>Bank Name</th>
                    <th>Account Number</th>
                    <th>IFSC Code</th>
                    <th>Holder Name</th>
                    <th>UPI ID</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bankAccounts as $b)
                <tr>
                    <td class="fw-bold text-dark">
                        <i class="bi bi-person-circle text-primary me-1"></i>{{ $b->user?->name ?? 'Unknown Player' }}<br>
                        <small class="text-muted fw-normal">{{ $b->user?->mobile }}</small>
                    </td>
                    <td class="fw-bold text-primary text-uppercase">{{ $b->bank_name }}</td>
                    <td class="font-monospace fw-bold text-dark">{{ $b->account_number }}</td>
                    <td class="font-monospace text-uppercase">{{ $b->ifsc_code }}</td>
                    <td class="fw-semibold">{{ $b->account_holder }}</td>
                    <td class="text-muted">{{ $b->upi_id ?? '-' }}</td>
                    <td>
                        @if($b->status === 'approved')
                            <span class="badge badge-soft-success rounded-pill px-3 py-1">APPROVED</span>
                        @elseif($b->status === 'rejected')
                            <span class="badge badge-soft-danger rounded-pill px-3 py-1" title="{{ $b->admin_notes }}">REJECTED</span>
                        @else
                            <span class="badge badge-soft-warning rounded-pill px-3 py-1">PENDING</span>
                        @endif
                    </td>
                    <td class="text-secondary small">{{ $b->created_at->format('d M Y, H:i') }}</td>
                    <td>
                        @if($b->status === 'pending')
                            <div class="d-flex gap-1">
                                <form action="{{ route('admin.bank-approvals.approve', $b->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success rounded-pill py-1 px-3" style="font-size: 0.75rem;">
                                        <i class="bi bi-check-circle me-1"></i>Approve
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill py-1 px-2" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $b->id }}" style="font-size: 0.75rem;">
                                    <i class="bi bi-x-circle me-1"></i>Reject
                                </button>
                            </div>

                            <!-- Reject Reason Modal -->
                            <div class="modal fade" id="rejectModal{{ $b->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title fw-bold text-dark">Reject Bank Card #{{ $b->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.bank-approvals.reject', $b->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body text-start">
                                                <p class="small text-secondary mb-2">Specify the reason for rejecting this bank account (this note will be shown to the player in their profile):</p>
                                                <textarea name="reason" class="form-control" rows="3" placeholder="e.g. Account holder name does not match KYC documents or invalid IFSC code." required></textarea>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">REJECT CARD</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <span class="text-muted small"><i class="bi bi-check-all me-1"></i>Processed</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-5 text-muted">No bank card records found for this status</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $bankAccounts->links() }}
    </div>
</div>
@endsection
