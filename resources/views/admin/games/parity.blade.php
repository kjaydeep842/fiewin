@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="bi bi-clock-fill text-primary me-2"></i>Parity (3-Min) Control Center
        </h4>
        <small class="text-secondary">Manage 3-Minute Parity winning chances (RTP %), house edge, min/max limits & manual period overrides</small>
    </div>
    <form action="{{ route('admin.parity.toggle') }}" method="POST">
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
            <small class="text-muted fw-bold d-block mb-1">TODAY'S TOTAL STAKES</small>
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
                <form action="{{ route('admin.parity.rtp') }}" method="POST">
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
                <form action="{{ route('admin.parity.limits') }}" method="POST">
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

{{-- Manual Result Override Card --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 fw-bold pt-3 pb-0 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-crosshair text-danger me-2"></i>Manual Winning Result Override</span>
        @if($override !== null)
            <span class="badge bg-warning text-dark px-3 py-1 rounded-pill">
                ACTIVE OVERRIDE: FORCE NUMBER {{ $override }}
            </span>
        @else
            <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill">
                AUTOMATIC RTP ALGORITHM ACTIVE
            </span>
        @endif
    </div>
    <div class="card-body">
        <form action="{{ route('admin.parity.override') }}" method="POST">
            @csrf
            <label class="form-label text-muted small fw-bold mb-2">FORCE NEXT PERIOD WINNING NUMBER (0 - 9)</label>
            <div class="d-flex gap-2 flex-wrap mb-3">
                @for($n = 0; $n <= 9; $n++)
                    @php
                        if ($n == 0) {
                            $bgColor = 'linear-gradient(135deg, #EF4444 50%, #8B5CF6 50%)';
                        } elseif ($n == 5) {
                            $bgColor = 'linear-gradient(135deg, #10B981 50%, #8B5CF6 50%)';
                        } elseif ($n % 2 == 1) {
                            $bgColor = '#10B981';
                        } else {
                            $bgColor = '#EF4444';
                        }
                    @endphp
                    <button type="submit" name="manual_number" value="{{ $n }}" class="btn btn-lg flex-fill text-white fw-bold shadow-sm" style="background: {{ $bgColor }}; min-width: 55px;">
                        {{ $n }}
                    </button>
                @endfor
            </div>
        </form>
        @if($override !== null)
            <form action="{{ route('admin.parity.override') }}" method="POST">
                @csrf
                <input type="hidden" name="manual_number" value="">
                <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill">
                    <i class="bi bi-x-circle me-1"></i>Clear Manual Override (Return to Auto RTP)
                </button>
            </form>
        @endif
    </div>
</div>

{{-- Recent History Table --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 fw-bold py-3">
        <i class="bi bi-clock-history me-2 text-primary"></i>Recent Parity (3-Min) Settled Periods
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light">
                <tr>
                    <th>Period Number</th>
                    <th>Winning Number</th>
                    <th>Colors</th>
                    <th>Override?</th>
                    <th>Settled Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentResults as $r)
                    @php $data = $r->result_data ?? []; @endphp
                    <tr>
                        <td class="fw-bold font-monospace text-primary">#{{ $r->period_number }}</td>
                        <td>
                            <span class="badge fs-6 px-3 py-1 rounded-circle text-white"
                                  style="background: {{ ($data['number'] ?? 0) == 0 ? '#8B5CF6' : ((($data['number'] ?? 0) == 5) ? '#10B981' : ((($data['number'] ?? 0) % 2 == 0) ? '#EF4444' : '#10B981')) }}">
                                {{ $data['number'] ?? 0 }}
                            </span>
                        </td>
                        <td>
                            @foreach($data['colors'] ?? ['green'] as $c)
                                <span class="badge text-uppercase" style="background: {{ $c === 'red' ? '#EF4444' : ($c === 'green' ? '#10B981' : '#8B5CF6') }}">
                                    {{ $c }}
                                </span>
                            @endforeach
                        </td>
                        <td>
                            @if($r->manual_override)
                                <span class="badge bg-warning text-dark">MANUAL OVERRIDE</span>
                            @else
                                <span class="badge bg-light text-muted border">AUTO RTP</span>
                            @endif
                        </td>
                        <td class="text-secondary small">{{ $r->settled_at ? $r->settled_at->format('H:i:s, M d') : ($r->created_at ? $r->created_at->format('H:i:s, M d') : '') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center py-4">No period results settled yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    // Auto refresh settled periods table every 6 seconds to show live results without manual refresh
    setInterval(function() {
        if (!document.hidden) {
            fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTable = doc.querySelector('.table-responsive');
                    const currentTable = document.querySelector('.table-responsive');
                    if (newTable && currentTable) {
                        currentTable.innerHTML = newTable.innerHTML;
                    }
                })
                .catch(() => {});
        }
    }, 6000);
</script>
@endpush
@endsection
