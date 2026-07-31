@extends('layouts.app')

@section('content')
<div class="gh-card p-3 mb-3 bg-white border border-light shadow-sm rounded-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('games.show', 'crash') }}" class="btn btn-sm btn-light border rounded-circle flex-shrink-0">
                <i class="bi bi-arrow-left text-dark"></i>
            </a>
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-journal-text text-primary me-2"></i>My Crash Order History
                </h6>
                <small class="text-secondary" style="font-size: 0.72rem;">Full transactional breakdown of all your bets</small>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('games.crash.history') }}" class="row g-2 mb-3">
        <div class="col-6 col-md-4">
            <label class="form-label text-secondary small mb-1 fw-bold">DATE RANGE</label>
            <select name="date" class="form-select form-select-sm border-secondary border-opacity-25 fw-bold" onchange="this.form.submit()">
                <option value="all" {{ $dateFilter === 'all' ? 'selected' : '' }}>All Time</option>
                <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                <option value="last_7_days" {{ $dateFilter === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
            </select>
        </div>
        <div class="col-6 col-md-4">
            <label class="form-label text-secondary small mb-1 fw-bold">BET STATUS</label>
            <select name="status" class="form-select form-select-sm border-secondary border-opacity-25 fw-bold" onchange="this.form.submit()">
                <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Statuses</option>
                <option value="cashed_out" {{ $statusFilter === 'cashed_out' ? 'selected' : '' }}>Cashed Out (Won)</option>
                <option value="lost" {{ $statusFilter === 'lost' ? 'selected' : '' }}>Crashed (Lost)</option>
                <option value="flying" {{ $statusFilter === 'flying' ? 'selected' : '' }}>Flying (Active)</option>
            </select>
        </div>
    </form>

    <!-- Orders Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle small text-center mb-0">
            <thead class="table-light">
                <tr class="text-secondary">
                    <th class="text-start">Round ID</th>
                    <th>Bet (₹)</th>
                    <th>Cashout Multiplier</th>
                    <th>Profit (₹)</th>
                    <th>Status</th>
                    <th class="text-end">Date & Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td class="text-start font-monospace fw-bold text-dark" style="font-size: 0.78rem;">
                        {{ $order->round_id }}
                    </td>
                    <td class="fw-bold">₹{{ number_format($order->bet_amount, 2) }}</td>
                    <td class="font-monospace fw-bold text-info">
                        {{ $order->cashout_multiplier ? number_format($order->cashout_multiplier, 2) . 'x' : '-' }}
                    </td>
                    <td class="fw-bold {{ $order->profit > 0 ? 'text-success' : ($order->status === 'lost' ? 'text-danger' : 'text-secondary') }}">
                        {{ $order->profit > 0 ? '+₹' . number_format($order->profit, 2) : ($order->status === 'lost' ? '-₹' . number_format($order->bet_amount, 2) : '₹0.00') }}
                    </td>
                    <td>
                        @if($order->status === 'cashed_out')
                            <span class="badge bg-success bg-opacity-10 text-success border border-success">💰 Cashed Out</span>
                        @elseif($order->status === 'lost')
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">💥 Crashed</span>
                        @else
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">✈️ Flying</span>
                        @endif
                    </td>
                    <td class="text-end text-secondary font-monospace" style="font-size: 0.72rem;">
                        {{ $order->created_at->format('d M Y, H:i:s') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-secondary text-center py-4">No order records found for selected filters</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Links -->
    <div class="mt-3 d-flex justify-content-end">
        {{ $orders->appends(request()->query())->links() }}
    </div>
</div>
@endsection
