@extends('layouts.admin')

@section('page-title', 'Withdrawal Processing')
@section('page-subtitle', 'Process payout requests to player bank accounts or UPI IDs')

@section('content')
<div class="admin-card p-3">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Tx ID</th>
                    <th>Player</th>
                    <th>Net Payout</th>
                    <th>Fee</th>
                    <th>Payout Destination</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($withdrawals as $w)
                <tr>
                    <td class="font-monospace text-muted small">{{ $w->transaction_id }}</td>
                    <td class="fw-semibold">
                        {{ $w->user?->name }}<br>
                        <small class="text-muted">{{ $w->user?->mobile }}</small>
                    </td>
                    <td class="fw-bold text-danger">₹{{ number_format($w->net_amount, 2) }}</td>
                    <td class="text-muted">₹{{ number_format($w->fee, 2) }}</td>
                    <td>
                        @if($w->bankAccount)
                            <span class="fw-semibold">{{ $w->bankAccount->bank_name }}</span><br>
                            <small class="text-muted">A/C: {{ $w->bankAccount->account_number }} (IFSC: {{ $w->bankAccount->ifsc_code }})</small>
                        @else
                            <span class="fw-semibold text-primary">UPI: {{ $w->upi_id ?? '-' }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $w->status === 'approved' ? 'badge-soft-success' : ($w->status === 'rejected' ? 'badge-soft-danger' : 'badge-soft-warning') }} rounded-pill px-2">
                            {{ strtoupper($w->status) }}
                        </span>
                    </td>
                    <td>
                        @if($w->status === 'pending')
                            <div class="d-flex gap-1">
                                <form action="{{ route('admin.withdrawals.approve', $w->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success rounded-pill py-1 px-2" style="font-size: 0.78rem;">
                                        <i class="bi bi-check-circle me-1"></i>Approve
                                    </button>
                                </form>
                                <form action="{{ route('admin.withdrawals.reject', $w->id) }}" method="POST" onsubmit="return confirm('Reject withdrawal and refund balance to user?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill py-1 px-2" style="font-size: 0.78rem;">
                                        <i class="bi bi-x-circle me-1"></i>Reject
                                    </button>
                                </form>
                            </div>
                        @elseif($w->status === 'approved')
                            <span class="text-success small fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Approved</span>
                        @else
                            <span class="text-danger small fw-semibold"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $withdrawals->links() }}
    </div>
</div>
@endsection
