@extends('layouts.admin')

@section('content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>Andar Bahar Financial Reports</h4>
            <p class="text-muted small mb-0">Player betting statements, win payouts, house net profit, and CSV export.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.andar-bahar.reports', ['range' => $range, 'export' => 'csv']) }}" class="btn btn-success fw-bold">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
            <a href="{{ route('admin.andar-bahar.dashboard') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <!-- Filter Pills -->
    <div class="d-flex gap-2 mb-4">
        <a href="{{ route('admin.andar-bahar.reports', ['range' => 'daily']) }}" class="btn {{ $range === 'daily' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill px-4">Daily</a>
        <a href="{{ route('admin.andar-bahar.reports', ['range' => 'weekly']) }}" class="btn {{ $range === 'weekly' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill px-4">Weekly</a>
        <a href="{{ route('admin.andar-bahar.reports', ['range' => 'monthly']) }}" class="btn {{ $range === 'monthly' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill px-4">Monthly</a>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <small class="text-muted fw-semibold">TOTAL PLAYER STAKES</small>
                <div class="fs-4 fw-bold text-dark mt-1">₹{{ number_format($totalStakes, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <small class="text-muted fw-semibold">TOTAL WINNINGS PAID</small>
                <div class="fs-4 fw-bold text-danger mt-1">₹{{ number_format($totalWinnings, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <small class="text-muted fw-semibold">HOUSE NET PROFIT</small>
                <div class="fs-4 fw-bold {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }} mt-1">
                    ₹{{ number_format($netProfit, 2) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Bet Statements Table -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <h5 class="fw-bold mb-3">Player Bet Statements ({{ ucfirst($range) }})</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="table-light">
                    <tr>
                        <th>Bet ID</th>
                        <th>Period</th>
                        <th>User</th>
                        <th>Selection</th>
                        <th>Bet Amount</th>
                        <th>Win Amount</th>
                        <th>Status</th>
                        <th>Date Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bets as $b)
                        <tr>
                            <td class="font-monospace">#{{ $b->id }}</td>
                            <td class="fw-bold font-monospace">{{ $b->period_number }}</td>
                            <td>{{ $b->user->mobile_number ?? $b->user->name ?? 'User #'.$b->user_id }}</td>
                            <td>
                                <span class="badge {{ $b->bet_option === 'andar' ? 'bg-primary' : ($b->bet_option === 'bahar' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                    {{ strtoupper($b->bet_option) }}
                                </span>
                            </td>
                            <td class="fw-bold">₹{{ number_format($b->bet_amount, 2) }}</td>
                            <td class="fw-bold {{ $b->win_amount > 0 ? 'text-success' : 'text-muted' }}">₹{{ number_format($b->win_amount, 2) }}</td>
                            <td>
                                @if($b->status === 'won')
                                    <span class="badge bg-success">WON</span>
                                @elseif($b->status === 'lost')
                                    <span class="badge bg-danger">LOST</span>
                                @else
                                    <span class="badge bg-secondary">PENDING</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $b->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No bet records found for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
