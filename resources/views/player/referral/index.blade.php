@extends('layouts.app')

@section('content')
<!-- Referral Header Banner Card -->
<div class="gh-card p-4 mb-3" style="background: linear-gradient(135deg, #1E88E5 0%, #1565C0 100%); color: #ffffff;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="badge bg-warning text-dark font-monospace fw-bold mb-1">3-TIER COMMISSIONS</span>
            <h4 class="fw-bold mb-1">Invite & Earn Forever</h4>
            <p class="small opacity-85 mb-0" style="font-size: 0.75rem;">3% Level 1 | 2% Level 2 | 1% Level 3</p>
        </div>
        <div class="display-3 text-warning"><i class="bi bi-people-fill"></i></div>
    </div>

    <!-- Referral Link Copy Box -->
    <div class="p-3 bg-white text-dark rounded-4 shadow-sm">
        <label class="form-label text-secondary small fw-semibold">YOUR EXCLUSIVE REFERRAL LINK</label>
        <div class="input-group">
            <input type="text" id="refLinkInput" class="form-control fw-bold bg-light" value="{{ $referralLink }}" readonly>
            <button class="btn gh-btn-primary px-3 rounded-end-3" onclick="copyRefLink()"><i class="bi bi-clipboard me-1"></i>Copy Link</button>
        </div>
    </div>
</div>

<!-- 3-Tier Stats Row -->
<div class="row g-2 mb-3">
    <div class="col-4">
        <div class="gh-card p-3 text-center">
            <span class="badge bg-success bg-opacity-10 text-success mb-1">LEVEL 1 (3%)</span>
            <h4 class="fw-bold text-dark mb-0">{{ $level1Count }}</h4>
            <small class="text-secondary" style="font-size: 0.68rem;">Direct Invites</small>
        </div>
    </div>
    <div class="col-4">
        <div class="gh-card p-3 text-center">
            <span class="badge bg-info bg-opacity-10 text-info mb-1">LEVEL 2 (2%)</span>
            <h4 class="fw-bold text-dark mb-0">{{ $level2Count }}</h4>
            <small class="text-secondary" style="font-size: 0.68rem;">Team Players</small>
        </div>
    </div>
    <div class="col-4">
        <div class="gh-card p-3 text-center">
            <span class="badge bg-warning bg-opacity-10 text-warning mb-1">LEVEL 3 (1%)</span>
            <h4 class="fw-bold text-dark mb-0">{{ $level3Count }}</h4>
            <small class="text-secondary" style="font-size: 0.68rem;">Sub Players</small>
        </div>
    </div>
</div>

<!-- Commission History Table -->
<div class="gh-card p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-cash-stack text-warning me-2"></i>Commission Logs</h6>
        <span class="fw-bold text-success fs-6">Total Earned: ₹{{ number_format($totalCommission, 2) }}</span>
    </div>

    <div class="table-responsive">
        <table class="table table-borderless table-sm align-middle mb-0" style="font-size: 0.82rem;">
            <thead class="text-secondary border-bottom">
                <tr>
                    <th>From User</th>
                    <th>Level</th>
                    <th>Rate</th>
                    <th>Amount</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentCommissions as $c)
                    <tr class="border-bottom border-light">
                        <td class="fw-bold text-dark">{{ $c->sourceUser?->name ?? 'Player' }}</td>
                        <td><span class="badge bg-light text-dark border">L{{ $c->level }}</span></td>
                        <td class="text-primary">{{ $c->rate_percentage }}%</td>
                        <td class="fw-bold text-success">+₹{{ number_format($c->amount, 2) }}</td>
                        <td class="text-secondary" style="font-size: 0.72rem;">{{ $c->created_at->format('M d, H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-secondary text-center py-3">No commission logs yet. Share your link to start earning!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    function copyRefLink() {
        let input = document.getElementById('refLinkInput');
        input.select();
        document.execCommand('copy');
        alert('Referral link copied to clipboard!');
    }
</script>
@endpush
@endsection
