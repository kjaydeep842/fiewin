@extends('layouts.admin')

@section('page-title', 'Financial Analytics & Reports')
@section('page-subtitle', 'Daily deposit, withdrawal, game turnover, and commission summaries')

@section('content')

<!-- Summary Row -->
<div class="row g-3 mb-4">
    <!-- Daily Deposits -->
    <div class="col-md-6">
        <div class="admin-card p-3">
            <h6 class="fw-semibold mb-3" style="color: #111827; font-size: 0.9rem;">
                <i class="bi bi-arrow-down-left-circle text-success me-2"></i>Daily Deposits Summary
            </h6>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Count</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($depositReport as $dr)
                        <tr>
                            <td class="text-muted">{{ $dr->date }}</td>
                            <td><span class="badge badge-soft-primary rounded-pill px-2">{{ $dr->count }}</span></td>
                            <td class="fw-bold text-success">₹{{ number_format($dr->total_amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Daily Withdrawals -->
    <div class="col-md-6">
        <div class="admin-card p-3">
            <h6 class="fw-semibold mb-3" style="color: #111827; font-size: 0.9rem;">
                <i class="bi bi-arrow-up-right-circle text-danger me-2"></i>Daily Withdrawals Summary
            </h6>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Count</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($withdrawalReport as $wr)
                        <tr>
                            <td class="text-muted">{{ $wr->date }}</td>
                            <td><span class="badge badge-soft-danger rounded-pill px-2">{{ $wr->count }}</span></td>
                            <td class="fw-bold text-danger">₹{{ number_format($wr->total_amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bets Turnover -->
<div class="admin-card p-3 mb-4">
    <h6 class="fw-semibold mb-3" style="color: #111827; font-size: 0.9rem;">
        <i class="bi bi-controller text-warning me-2"></i>Game Bets Turnover & House Net Revenue
    </h6>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total Bets</th>
                    <th>Stakes Turnover</th>
                    <th>Winnings Paid</th>
                    <th>House Net Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($betReport as $br)
                @php $net = $br->total_bet - $br->total_win; @endphp
                <tr>
                    <td class="text-muted">{{ $br->date }}</td>
                    <td><span class="badge badge-soft-secondary rounded-pill px-2">{{ $br->count }}</span></td>
                    <td class="fw-bold">₹{{ number_format($br->total_bet, 2) }}</td>
                    <td class="text-danger">₹{{ number_format($br->total_win, 2) }}</td>
                    <td class="fw-bold {{ $net >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $net >= 0 ? '+' : '' }}₹{{ number_format($net, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Commission Breakdown -->
<div class="admin-card p-3">
    <h6 class="fw-semibold mb-3" style="color: #111827; font-size: 0.9rem;">
        <i class="bi bi-diagram-3 text-primary me-2"></i>Referral Commission Breakdown by Tier Level
    </h6>
    <div class="row g-3 text-center">
        @foreach($commissionReport as $cr)
        <div class="col-md-4">
            <div class="p-3 rounded-3 border" style="background: #FAFBFD;">
                <span class="badge badge-soft-primary mb-2 px-3 py-1 rounded-pill">LEVEL {{ $cr->level }}</span>
                <h4 class="fw-bold mb-1" style="color: #b45309;">₹{{ number_format($cr->total_commission, 2) }}</h4>
                <small class="text-muted">{{ $cr->count }} payouts</small>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
