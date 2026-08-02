@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">Merchant Collection Accounts</h4>
        <small class="text-secondary">Manage merchant bank accounts, UPI IDs, QR codes & load balancing limits</small>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.merchants.reset-daily') }}" method="POST" onsubmit="return confirm('Reset all merchant daily totals to ₹0.00?')">
            @csrf
            <button type="submit" class="btn btn-outline-warning btn-sm fw-bold rounded-pill">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Daily Totals
            </button>
        </form>
        <button type="button" class="btn btn-primary btn-sm fw-bold rounded-pill" data-bs-toggle="modal" data-bs-target="#addMerchantModal">
            <i class="bi bi-plus-lg me-1"></i>Add Merchant Account
        </button>
    </div>
</div>

<div class="row g-3">
    @forelse($merchants as $m)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge {{ $m->status === 'active' ? 'bg-success' : 'bg-secondary' }} rounded-pill me-1">
                                {{ strtoupper($m->status) }}
                            </span>
                            <span class="badge bg-light text-dark border rounded-pill">Priority: {{ $m->priority }}</span>
                        </div>
                        <form action="{{ route('admin.merchants.toggle', $m->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-{{ $m->status === 'active' ? 'danger' : 'success' }} rounded-pill px-2 py-0" style="font-size: 0.72rem;">
                                {{ $m->status === 'active' ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                    </div>

                    <h5 class="fw-bold text-dark mb-1">{{ $m->name }}</h5>
                    <small class="text-secondary d-block mb-2">Holder: {{ $m->account_holder ?? 'N/A' }}</small>

                    @if($m->upi_id)
                        <div class="p-2 bg-light rounded-3 border mb-2" style="font-size: 0.8rem;">
                            <small class="text-muted d-block" style="font-size: 0.65rem;">UPI ID</small>
                            <span class="fw-bold text-primary font-monospace">{{ $m->upi_id }}</span>
                        </div>
                    @endif

                    @if($m->bank_name)
                        <div class="p-2 bg-light rounded-3 border mb-2" style="font-size: 0.78rem;">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Bank:</small>
                                <span class="fw-semibold">{{ $m->bank_name }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Acc No:</small>
                                <span class="fw-bold font-monospace">{{ $m->account_number }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">IFSC:</small>
                                <span class="fw-mono">{{ $m->ifsc }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Capacity & Progress --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Today Collected:</span>
                            <span class="fw-bold text-dark">₹{{ number_format($m->current_daily_total, 2) }} / ₹{{ number_format($m->daily_limit, 2) }}</span>
                        </div>
                        @php
                            $pct = min(100, $m->daily_limit > 0 ? ($m->current_daily_total / $m->daily_limit) * 100 : 0);
                        @endphp
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar {{ $pct > 80 ? 'bg-danger' : ($pct > 50 ? 'bg-warning' : 'bg-success') }}" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        @if($m->qr_image)
                            <a href="{{ asset($m->qr_image) }}" target="_blank" class="small text-primary text-decoration-none">
                                <i class="bi bi-qr-code me-1"></i>View QR Image
                            </a>
                        @else
                            <span class="small text-muted">No QR Code</span>
                        @endif

                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#editMerchantModal{{ $m->id }}">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div class="modal fade" id="editMerchantModal{{ $m->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content rounded-4 border-0">
                    <form action="{{ route('admin.merchants.update', $m->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Merchant: {{ $m->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Merchant Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $m->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Account Holder</label>
                                <input type="text" name="account_holder" class="form-control" value="{{ $m->account_holder }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">UPI ID</label>
                                <input type="text" name="upi_id" class="form-control" value="{{ $m->upi_id }}">
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control" value="{{ $m->bank_name }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">IFSC Code</label>
                                    <input type="text" name="ifsc" class="form-control" value="{{ $m->ifsc }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Account Number</label>
                                <input type="text" name="account_number" class="form-control" value="{{ $m->account_number }}">
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">Daily Limit (₹)</label>
                                    <input type="number" name="daily_limit" class="form-control" value="{{ $m->daily_limit }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">Priority (1-100)</label>
                                    <input type="number" name="priority" class="form-control" value="{{ $m->priority }}" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Change QR Code Image</label>
                                <input type="file" name="qr_image" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="bi bi-bank display-4 d-block mb-2"></i>
            No merchant collection accounts created yet.
        </div>
    @endforelse
</div>

{{-- Add Merchant Modal --}}
<div class="modal fade" id="addMerchantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <form action="{{ route('admin.merchants.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Merchant Collection Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Merchant Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Merchant Alpha Pay" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Account Holder Name</label>
                        <input type="text" name="account_holder" class="form-control" placeholder="e.g. Fiewin Services Pvt Ltd">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">UPI ID</label>
                        <input type="text" name="upi_id" class="form-control" placeholder="e.g. merchant@upi">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" placeholder="HDFC Bank">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">IFSC Code</label>
                            <input type="text" name="ifsc" class="form-control" placeholder="HDFC0001234">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Account Number</label>
                        <input type="text" name="account_number" class="form-control" placeholder="50100234123981">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Daily Limit (₹)</label>
                            <input type="number" name="daily_limit" class="form-control" value="200000" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Priority (1-100)</label>
                            <input type="number" name="priority" class="form-control" value="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">QR Code Image</label>
                        <input type="file" name="qr_image" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Merchant</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
