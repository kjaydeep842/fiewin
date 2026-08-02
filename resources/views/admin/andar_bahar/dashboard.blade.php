@extends('layouts.admin')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-suit-spade-fill text-primary me-2"></i>Andar Bahar Dashboard</h4>
            <p class="text-muted small mb-0">Overview of today's bets, house profits, RTP status, and live manual overrides.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.andar-bahar.settings') }}" class="btn btn-outline-primary"><i class="bi bi-gear-fill me-1"></i>Settings</a>
            <a href="{{ route('admin.andar-bahar.reports') }}" class="btn btn-primary"><i class="bi bi-file-earmark-bar-graph me-1"></i>Reports</a>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <small class="text-muted fw-semibold text-uppercase">TODAY'S BETS</small>
                <div class="fs-4 fw-bold text-dark mt-1">₹{{ number_format($todayBetsTotal, 2) }}</div>
                <small class="text-primary fw-bold mt-1 d-block">{{ $todayBetsCount }} Total Bets</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <small class="text-muted fw-semibold text-uppercase">TODAY'S WINNINGS</small>
                <div class="fs-4 fw-bold text-danger mt-1">₹{{ number_format($todayWinsTotal, 2) }}</div>
                <small class="text-muted mt-1 d-block">Paid to winners</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <small class="text-muted fw-semibold text-uppercase">HOUSE NET PROFIT</small>
                <div class="fs-4 fw-bold {{ $houseProfit >= 0 ? 'text-success' : 'text-danger' }} mt-1">
                    ₹{{ number_format($houseProfit, 2) }}
                </div>
                <small class="text-muted mt-1 d-block">Target RTP: {{ $settings->rtp_percentage }}%</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <small class="text-muted fw-semibold text-uppercase">ACTIVE PLAYERS</small>
                <div class="fs-4 fw-bold text-info mt-1">{{ $uniquePlayersCount }}</div>
                <small class="text-muted mt-1 d-block">{{ $pendingBetsCount }} Pending Bets (₹{{ number_format($pendingBetsAmount, 2) }})</small>
            </div>
        </div>
    </div>

    <!-- Live Round & Manual Override Panel -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-broadcast text-danger me-2"></i>Current Round Status</h5>
                <div class="p-3 bg-light rounded-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted font-monospace">Period Number:</span>
                        <span class="fw-bold text-primary fs-5">{{ $currentRound->period_number }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted font-monospace">Open Card:</span>
                        <span class="badge bg-dark text-white fs-6 px-3 py-2">{{ $currentRound->open_card }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted font-monospace">Round Status:</span>
                        <span class="badge bg-success text-uppercase">{{ $currentRound->status }}</span>
                    </div>
                </div>

                <small class="text-muted d-block mb-3">RTP engine naturally balances winner selection. Override allows forced admin outcome.</small>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-controller text-warning me-2"></i>Manual Result Override</h5>
                <div class="mb-3">
                    <small class="text-muted d-block mb-2">Current Active Override:</small>
                    @if($settings->manual_override_winner)
                        <div class="alert alert-warning fw-bold d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-exclamation-triangle-fill me-2"></i>FORCED WINNER: {{ strtoupper($settings->manual_override_winner) }}</span>
                            <form action="{{ route('admin.andar-bahar.override') }}" method="POST">
                                @csrf
                                <input type="hidden" name="winner" value="clear">
                                <button type="submit" class="btn btn-sm btn-outline-dark">Clear</button>
                            </form>
                        </div>
                    @else
                        <span class="badge bg-secondary p-2">AUTOMATED RTP ACTIVE (NO OVERRIDE)</span>
                    @endif
                </div>

                <label class="form-label font-monospace text-muted small">SET NEXT ROUND WINNER:</label>
                <div class="row g-2">
                    <div class="col-4">
                        <form action="{{ route('admin.andar-bahar.override') }}" method="POST">
                            @csrf
                            <input type="hidden" name="winner" value="andar">
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">FORCE ANDAR</button>
                        </form>
                    </div>
                    <div class="col-4">
                        <form action="{{ route('admin.andar-bahar.override') }}" method="POST">
                            @csrf
                            <input type="hidden" name="winner" value="tie">
                            <button type="submit" class="btn btn-warning w-100 py-2 fw-bold text-dark">FORCE TIE</button>
                        </form>
                    </div>
                    <div class="col-4">
                        <form action="{{ route('admin.andar-bahar.override') }}" method="POST">
                            @csrf
                            <input type="hidden" name="winner" value="bahar">
                            <button type="submit" class="btn btn-danger w-100 py-2 fw-bold">FORCE BAHAR</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Settled Rounds Table -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-info me-2"></i>Recent Settled Rounds</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="table-light">
                    <tr>
                        <th>Period</th>
                        <th>Open Card</th>
                        <th>Winner</th>
                        <th>Winning Card</th>
                        <th>Deals</th>
                        <th>Override</th>
                        <th>Settled Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentRounds as $r)
                        <tr>
                            <td class="fw-bold font-monospace">{{ $r->period_number }}</td>
                            <td><span class="badge bg-dark text-white px-2 py-1">{{ $r->open_card }}</span></td>
                            <td>
                                <span class="badge {{ $r->winner === 'andar' ? 'bg-primary' : ($r->winner === 'bahar' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                    {{ strtoupper($r->winner) }}
                                </span>
                            </td>
                            <td class="fw-bold">{{ $r->winning_card }}</td>
                            <td>{{ $r->deal_count }} cards</td>
                            <td>
                                {!! $r->manual_override ? '<span class="badge bg-warning text-dark">YES</span>' : '<span class="badge bg-light text-muted border">NO</span>' !!}
                            </td>
                            <td class="text-muted">{{ $r->settled_at ? $r->settled_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
