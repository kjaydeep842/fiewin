@extends('layouts.admin')

@section('page-title', 'Platform Overview')
@section('page-subtitle', 'Real-time gaming stats & profit performance')

@section('content')

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="admin-stat-card">
            <div class="stat-icon" style="background: rgba(30,136,229,0.10); color: #1E88E5;">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="text-muted small mb-1">TOTAL REGISTERED USERS</div>
            <h3 class="fw-bold mb-0 font-monospace" style="color: #111827;">{{ number_format($totalUsers) }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="admin-stat-card">
            <div class="stat-icon" style="background: rgba(34,197,94,0.10); color: #16a34a;">
                <i class="bi bi-arrow-down-circle-fill"></i>
            </div>
            <div class="text-muted small mb-1">TOTAL DEPOSITS</div>
            <h3 class="fw-bold mb-0 font-monospace text-success">₹{{ number_format($totalDeposits, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="admin-stat-card">
            <div class="stat-icon" style="background: rgba(239,68,68,0.10); color: #dc2626;">
                <i class="bi bi-arrow-up-circle-fill"></i>
            </div>
            <div class="text-muted small mb-1">TOTAL WITHDRAWALS</div>
            <h3 class="fw-bold mb-0 font-monospace text-danger">₹{{ number_format($totalWithdrawals, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="admin-stat-card">
            <div class="stat-icon" style="background: rgba(245,158,11,0.10); color: #b45309;">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div class="text-muted small mb-1">HOUSE NET PROFIT</div>
            <h3 class="fw-bold mb-0 font-monospace text-warning">₹{{ number_format($todayProfit, 2) }}</h3>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="admin-card p-4 mb-4">
    <h6 class="fw-bold mb-3" style="color: #111827;"><i class="bi bi-graph-up text-primary me-2"></i>Turnover vs House Revenue</h6>
    <canvas id="adminProfitChart" height="90"></canvas>
</div>

<!-- Recent Tables -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="admin-card p-3">
            <h6 class="fw-semibold mb-3" style="color: #111827; font-size: 0.9rem;">
                <i class="bi bi-person-check text-primary me-2"></i>Recently Registered Players
            </h6>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Mobile</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentUsers as $u)
                        <tr>
                            <td class="fw-semibold">{{ $u->name }}</td>
                            <td class="text-muted">{{ $u->mobile }}</td>
                            <td><span class="badge badge-soft-success rounded-pill px-3">Active</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="admin-card p-3">
            <h6 class="fw-semibold mb-3" style="color: #111827; font-size: 0.9rem;">
                <i class="bi bi-receipt text-success me-2"></i>Recent Deposit Requests
            </h6>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Tx ID</th>
                            <th>Player</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentDeposits as $d)
                        <tr>
                            <td class="font-monospace text-muted small">{{ Str::limit($d->transaction_id, 12) }}</td>
                            <td class="fw-semibold">{{ $d->user?->name }}</td>
                            <td class="fw-bold text-success">₹{{ number_format($d->amount, 2) }}</td>
                            <td>
                                <span class="badge {{ $d->status === 'approved' ? 'badge-soft-success' : 'badge-soft-warning' }} rounded-pill px-2">
                                    {{ strtoupper($d->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const ctx = document.getElementById('adminProfitChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Total Turnover (₹)',
            data: [12000, 19000, 15000, 25000, 22000, 30000, 45000],
            borderColor: '#1E88E5',
            backgroundColor: 'rgba(30,136,229,0.08)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#1E88E5',
            pointRadius: 4,
        }, {
            label: 'House Profit (₹)',
            data: [2000, 3500, 2800, 4200, 3900, 5500, 8200],
            borderColor: '#22C55E',
            backgroundColor: 'rgba(34,197,94,0.08)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#22C55E',
            pointRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { labels: { color: '#6B7280', font: { family: 'Poppins', size: 12 } } }
        },
        scales: {
            x: { ticks: { color: '#9CA3AF' }, grid: { color: '#F3F4F6' } },
            y: { ticks: { color: '#9CA3AF' }, grid: { color: '#F3F4F6' } }
        }
    }
});
</script>
@endpush
@endsection
