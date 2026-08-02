@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="bi bi-gem text-warning me-2"></i>Mines Game Control Center
        </h4>
        <small class="text-secondary">Manage Mines winning chances (RTP %), house edge, min/max entry fees & game status</small>
    </div>
    <form action="{{ route('admin.mines-admin.toggle') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-{{ $game->is_active ? 'success' : 'danger' }} fw-bold rounded-pill px-3">
            <i class="bi bi-power me-1"></i>{{ $game->is_active ? '● GAME ENABLED' : '○ GAME DISABLED' }}
        </button>
    </form>
</div>

{{-- Overview Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <small class="text-muted fw-bold d-block mb-1">TODAY'S MINES STAKES</small>
            <h3 class="fw-bold text-dark mb-0">₹{{ number_format($todayBetsTotal, 2) }}</h3>
            <small class="text-secondary">{{ $todayBetsCount }} total bets</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <small class="text-muted fw-bold d-block mb-1">TODAY'S PAYOUTS</small>
            <h3 class="fw-bold text-danger mb-0">₹{{ number_format($todayWinsTotal, 2) }}</h3>
            <small class="text-secondary">Paid to winners</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <small class="text-muted fw-bold d-block mb-1">HOUSE NET PROFIT</small>
            <h3 class="fw-bold {{ $houseProfit >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                ₹{{ number_format($houseProfit, 2) }}
            </h3>
            <small class="text-secondary">House Margin: {{ number_format(100 - $game->rtp_percentage, 1) }}%</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <small class="text-muted fw-bold d-block mb-1">ACTIVE WINNING CHANCE (RTP)</small>
            <h3 class="fw-bold text-primary mb-0">{{ number_format($game->rtp_percentage, 1) }}%</h3>
            <small class="text-secondary">{{ $uniquePlayersCount }} unique bettors today</small>
        </div>
    </div>
</div>

{{-- Control Forms Grid --}}
<div class="row g-3 mb-4">
    {{-- RTP / Winning Chance % Form --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 fw-bold pt-3 pb-0">
                <i class="bi bi-percent text-primary me-2"></i>Set Winning Chance / Target RTP %
            </div>
            <div class="card-body">
                <form action="{{ route('admin.mines-admin.rtp') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">RTP (Return To Player) %</label>
                        <div class="input-group">
                            <input type="number" step="0.5" min="0" max="100" name="rtp_percentage" class="form-control form-control-lg fw-bold" value="{{ $game->rtp_percentage }}" required>
                            <span class="input-group-text bg-light fw-bold">%</span>
                        </div>
                        <small class="text-muted mt-1 d-block">House Edge will be automatically set to: <strong>{{ number_format(100 - $game->rtp_percentage, 1) }}%</strong></small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-3">UPDATE WINNING CHANCE</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Min / Max Bet Limits Form --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 fw-bold pt-3 pb-0">
                <i class="bi bi-sliders text-success me-2"></i>Min & Max Entry Fee Limits
            </div>
            <div class="card-body">
                <form action="{{ route('admin.mines-admin.limits') }}" method="POST">
                    @csrf
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small fw-semibold">Min Entry Fee (₹)</label>
                            <input type="number" name="min_entry_fee" class="form-control form-control-lg fw-bold" value="{{ $game->min_entry_fee }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small fw-semibold">Max Entry Fee (₹)</label>
                            <input type="number" name="max_entry_fee" class="form-control form-control-lg fw-bold" value="{{ $game->max_entry_fee }}" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-bold py-2 rounded-3">UPDATE BET LIMITS</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Recent Mines Games Table --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 fw-bold py-3">
        <i class="bi bi-clock-history me-2 text-primary"></i>Recent Mines Played Games
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>Bet Amount</th>
                    <th>Status</th>
                    <th>Cashout Multiplier</th>
                    <th>Payout (₹)</th>
                    <th>Played Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentBets as $b)
                    <tr>
                        <td><span class="fw-bold text-dark">{{ $b->user->name ?? 'User #' . $b->user_id }}</span></td>
                        <td class="fw-bold font-monospace">₹{{ number_format($b->bet_amount, 2) }}</td>
                        <td>
                            @if($b->status === 'won')
                                <span class="badge bg-success rounded-pill px-2 py-1">CASHED OUT (WON)</span>
                            @elseif($b->status === 'lost')
                                <span class="badge bg-danger rounded-pill px-2 py-1">HIT MINE (LOST)</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1">PLAYING</span>
                            @endif
                        </td>
                        <td class="fw-bold font-monospace text-primary">{{ $b->multiplier ? number_format($b->multiplier, 2) . 'x' : '-' }}</td>
                        <td class="fw-bold font-monospace text-success">₹{{ number_format($b->win_amount, 2) }}</td>
                        <td class="text-secondary small">{{ $b->created_at ? $b->created_at->format('H:i:s, M d') : '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-4">No mines games played today.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
