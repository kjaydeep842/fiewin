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
                        <span class="badge {{ $w->status === 'approved' ? 'badge-soft-success' : 'badge-soft-warning' }} rounded-pill px-2">
                            {{ strtoupper($w->status) }}
                        </span>
                    </td>
                    <td>
                        @if($w->status === 'pending')
                            <form action="{{ route('admin.withdrawals.approve', $w->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-admin-primary rounded-pill py-1 px-3" style="font-size: 0.78rem;">
                                    <i class="bi bi-send me-1"></i>MARK DISBURSED
                                </button>
                            </form>
                        @else
                            <span class="text-muted small"><i class="bi bi-check-circle-fill text-success me-1"></i>Completed</span>
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
