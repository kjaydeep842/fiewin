@extends('layouts.admin')

@section('page-title', 'Deposit Management & Verification')
@section('page-subtitle', 'Verify manual UPI UTR numbers and approve deposits')

@section('content')
<div class="admin-card p-3">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Tx ID</th>
                    <th>Player Name</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Bonus</th>
                    <th>UTR / Ref</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deposits as $d)
                <tr>
                    <td class="font-monospace text-muted small">{{ $d->transaction_id }}</td>
                    <td class="fw-semibold">
                        {{ $d->user?->name }}<br>
                        <small class="text-muted">{{ $d->user?->mobile }}</small>
                    </td>
                    <td><span class="badge badge-soft-secondary text-uppercase rounded-pill px-2">{{ $d->payment_method }}</span></td>
                    <td class="fw-bold text-success">₹{{ number_format($d->amount, 2) }}</td>
                    <td class="text-primary">₹{{ number_format($d->bonus_amount, 2) }}</td>
                    <td class="font-monospace fw-semibold" style="color: #b45309;">{{ $d->utr_number ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $d->status === 'approved' ? 'badge-soft-success' : 'badge-soft-warning' }} rounded-pill px-2">
                            {{ strtoupper($d->status) }}
                        </span>
                    </td>
                    <td>
                        @if($d->status === 'pending')
                            <form action="{{ route('admin.deposits.approve', $d->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success rounded-pill py-1 px-3" style="font-size: 0.78rem;">
                                    <i class="bi bi-check-circle me-1"></i>APPROVE & CREDIT
                                </button>
                            </form>
                        @else
                            <span class="text-muted small"><i class="bi bi-check-all text-success me-1"></i>Approved</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $deposits->links() }}
    </div>
</div>
@endsection
