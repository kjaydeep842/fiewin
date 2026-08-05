@extends('layouts.admin')

@section('page-title', 'Platform Overview & Revenue')
@section('page-subtitle', 'Real-time financial counts, daily statistics & top earning players')

@section('content')

<!-- Overall Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-3 col-6">
        <div class="admin-stat-card">
            <div class="stat-icon" style="background: rgba(30,136,229,0.10); color: #1E88E5;">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="text-muted small mb-1">TOTAL USERS</div>
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
            <div class="text-muted small mb-1">TOTAL NET PROFIT</div>
            <h3 class="fw-bold mb-0 font-monospace text-warning">₹{{ number_format($totalProfit, 2) }}</h3>
        </div>
    </div>
</div>

<!-- Today's Financial Metrics Card Banner -->
<div class="admin-card p-3 mb-4" style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color: #ffffff;">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0 text-white"><i class="bi bi-calendar-check text-success me-2"></i>Today's Financial Summary ({{ date('d M Y') }})</h6>
        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25">LIVE UPDATED</span>
    </div>
    <div class="row g-2 text-center pt-2">
        <div class="col-4">
            <div class="p-2 rounded-3" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                <small class="d-block text-white-50" style="font-size: 0.72rem;">TODAY DEPOSITS</small>
                <span class="fw-bold text-success font-monospace" style="font-size: 1.1rem;">₹{{ number_format($todayDeposits, 2) }}</span>
            </div>
        </div>
        <div class="col-4">
            <div class="p-2 rounded-3" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                <small class="d-block text-white-50" style="font-size: 0.72rem;">TODAY WITHDRAWALS</small>
                <span class="fw-bold text-danger font-monospace" style="font-size: 1.1rem;">₹{{ number_format($todayWithdrawals, 2) }}</span>
            </div>
        </div>
        <div class="col-4">
            <div class="p-2 rounded-3" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                <small class="d-block text-white-50" style="font-size: 0.72rem;">TODAY NET PROFIT</small>
                <span class="fw-bold text-warning font-monospace" style="font-size: 1.1rem;">₹{{ number_format($todayProfit, 2) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="admin-card p-4 mb-4">
    <h6 class="fw-bold mb-3" style="color: #111827;"><i class="bi bi-graph-up text-primary me-2"></i>Turnover vs House Revenue</h6>
    <canvas id="adminProfitChart" height="90"></canvas>
</div>

<!-- Recent Deposits & Withdrawals Tables with Direct Action Buttons -->
<div class="row g-3 mb-4">
    <!-- Latest 5 Deposit Requests -->
    <div class="col-md-6">
        <div class="admin-card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.92rem;">
                        <i class="bi bi-arrow-down-circle-fill text-success me-2 fs-5"></i>Recent Deposit Requests
                    </h6>
                    <span class="text-muted small" style="font-size: 0.72rem;">Latest 5 deposit requests</span>
                </div>
                <a href="{{ route('admin.deposits.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold" style="font-size: 0.78rem;">
                    View All Deposits <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Player</th>
                            <th>Amount</th>
                            <th>UTR / Method</th>
                            <th class="text-end">Status / Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDeposits as $d)
                        <tr>
                            <td class="fw-semibold">
                                <div class="text-dark">{{ $d->user?->name ?? 'Player #' . $d->user_id }}</div>
                                <span class="text-muted small" style="font-size: 0.7rem;">{{ $d->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="fw-bold text-success font-monospace">₹{{ number_format($d->amount, 2) }}</td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace px-2" style="font-size: 0.68rem;">
                                    {{ Str::limit($d->utr_number ?? $d->payment_method ?? 'UPI', 12) }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if($d->status === 'pending')
                                    <div class="d-flex justify-content-end gap-1">
                                        <form method="POST" action="{{ route('admin.deposits.approve', $d->id) }}" class="d-inline" onsubmit="return confirm('Approve deposit request of ₹{{ number_format($d->amount, 2) }}?')">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success rounded-pill px-2 py-1 text-white fw-bold shadow-sm" style="font-size:0.7rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i>Approve
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 fw-bold" style="font-size:0.7rem;" data-bs-toggle="modal" data-bs-target="#rejectDepModal{{ $d->id }}">
                                            <i class="bi bi-x-circle-fill me-1"></i>Reject
                                        </button>
                                    </div>
                                @elseif($d->status === 'approved')
                                    <span class="badge badge-soft-success rounded-pill px-2">APPROVED</span>
                                @else
                                    <span class="badge badge-soft-danger rounded-pill px-2">REJECTED</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3 text-muted">No deposit requests yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Latest 5 Withdrawal Requests -->
    <div class="col-md-6">
        <div class="admin-card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.92rem;">
                        <i class="bi bi-arrow-up-circle-fill text-danger me-2 fs-5"></i>Recent Withdrawal Requests
                    </h6>
                    <span class="text-muted small" style="font-size: 0.72rem;">Latest 5 withdrawal requests</span>
                </div>
                <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold" style="font-size: 0.78rem;">
                    View All Withdrawals <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Player</th>
                            <th>Amount</th>
                            <th>Requested</th>
                            <th class="text-end">Status / Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentWithdrawals as $w)
                        <tr>
                            <td class="fw-semibold">
                                <div class="text-dark">{{ $w->user?->name ?? 'Player #' . $w->user_id }}</div>
                                <span class="text-muted small" style="font-size: 0.7rem;">{{ $w->user?->mobile }}</span>
                            </td>
                            <td class="fw-bold text-danger font-monospace">₹{{ number_format($w->amount, 2) }}</td>
                            <td class="text-muted small" style="font-size: 0.72rem;">{{ $w->created_at->diffForHumans() }}</td>
                            <td class="text-end">
                                @if($w->status === 'pending')
                                    <div class="d-flex justify-content-end gap-1">
                                        <form method="POST" action="{{ route('admin.withdrawals.approve', $w->id) }}" class="d-inline" onsubmit="return confirm('Approve withdrawal request of ₹{{ number_format($w->amount, 2) }}?')">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success rounded-pill px-2 py-1 text-white fw-bold shadow-sm" style="font-size:0.7rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i>Approve
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 fw-bold" style="font-size:0.7rem;" data-bs-toggle="modal" data-bs-target="#rejectWdModal{{ $w->id }}">
                                            <i class="bi bi-x-circle-fill me-1"></i>Reject
                                        </button>
                                    </div>
                                @elseif($w->status === 'approved')
                                    <span class="badge badge-soft-success rounded-pill px-2">APPROVED</span>
                                @else
                                    <span class="badge badge-soft-danger rounded-pill px-2">REJECTED</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3 text-muted">No withdrawal requests yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 🏆 NEW SECTION: TOP EARNING PLAYERS LEADERBOARD 🏆 -->
<div class="admin-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="fw-bold mb-0" style="color: #111827;"><i class="bi bi-trophy-fill text-warning me-2 fs-5"></i>Top Earning Players Leaderboard</h6>
            <span class="text-muted small">Highest earning and top winning players on our platform</span>
        </div>
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fw-bold">TOP 10 PLAYERS</span>
    </div>

    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Player Name</th>
                    <th>Mobile / Email</th>
                    <th>Total Winnings</th>
                    <th>Total Turnover</th>
                    <th>Current Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topEarners as $index => $player)
                <tr>
                    <td>
                        @if($loop->iteration === 1)
                            <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill"><i class="bi bi-award-fill me-1"></i>#1 CHAMPION</span>
                        @elseif($loop->iteration === 2)
                            <span class="badge bg-secondary text-white fw-bold px-3 py-1 rounded-pill"><i class="bi bi-award me-1"></i>#2 RUNNER</span>
                        @elseif($loop->iteration === 3)
                            <span class="badge bg-bronze text-white fw-bold px-3 py-1 rounded-pill" style="background: #cd7f32;"><i class="bi bi-award me-1"></i>#3 THIRD</span>
                        @else
                            <span class="fw-bold text-muted ps-2">#{{ $loop->iteration }}</span>
                        @endif
                    </td>
                    <td class="fw-bold text-dark">
                        <i class="bi bi-person-circle text-primary me-1"></i>{{ $player->name }}
                    </td>
                    <td class="text-muted small">
                        {{ $player->mobile }}<br>
                        <span class="text-secondary opacity-75">{{ $player->email }}</span>
                    </td>
                    <td class="fw-bold text-success font-monospace" style="font-size: 0.95rem;">
                        ₹{{ number_format($player->total_won ?? 0, 2) }}
                    </td>
                    <td class="fw-semibold text-secondary font-monospace">
                        ₹{{ number_format($player->total_bet ?? 0, 2) }}
                    </td>
                    <td class="fw-bold text-primary font-monospace">
                        ₹{{ number_format($player->wallet?->main_balance ?? 0, 2) }}
                    </td>
                    <td>
                        <span class="badge {{ $player->status === 'active' ? 'badge-soft-success' : 'badge-soft-danger' }} rounded-pill px-2">
                            {{ strtoupper($player->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">No top earning player records found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Rejection Modals for Deposit Requests -->
@foreach($recentDeposits as $d)
    @if($d->status === 'pending')
    <div class="modal fade" id="rejectDepModal{{ $d->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <form method="POST" action="{{ route('admin.deposits.reject', $d->id) }}">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h6 class="modal-title fw-bold text-danger">Reject Deposit Request</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <p class="text-muted small mb-2">Rejecting deposit request of <strong>₹{{ number_format($d->amount, 2) }}</strong> submitted by <strong>{{ $d->user?->name }}</strong>.</p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">Reason for Rejection <span class="text-danger">*</span></label>
                            <input type="text" name="reason" class="form-control form-control-sm rounded-3" placeholder="e.g. UTR Invalid / Payment not received" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold">Reject Deposit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

<!-- Rejection Modals for Withdrawal Requests -->
@foreach($recentWithdrawals as $w)
    @if($w->status === 'pending')
    <div class="modal fade" id="rejectWdModal{{ $w->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <form method="POST" action="{{ route('admin.withdrawals.reject', $w->id) }}">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h6 class="modal-title fw-bold text-danger">Reject Withdrawal Request</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <p class="text-muted small mb-2">Rejecting withdrawal request of <strong>₹{{ number_format($w->amount, 2) }}</strong> requested by <strong>{{ $w->user?->name }}</strong>. Amount will be refunded to player wallet.</p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">Reason for Rejection</label>
                            <input type="text" name="reason" class="form-control form-control-sm rounded-3" placeholder="e.g. Invalid bank details / account mismatch">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold">Confirm Rejection & Refund</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

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
