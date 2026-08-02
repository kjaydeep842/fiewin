@extends('layouts.admin')

@section('content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-clock-history text-primary me-2"></i>Andar Bahar Round Logs</h4>
            <p class="text-muted small mb-0">Detailed historical list of all settled rounds and deal sequences.</p>
        </div>
        <a href="{{ route('admin.andar-bahar.dashboard') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="table-light">
                    <tr>
                        <th>Period</th>
                        <th>Open Card</th>
                        <th>Winner</th>
                        <th>Winning Card</th>
                        <th>Deals</th>
                        <th>Provably Fair Hash</th>
                        <th>Override</th>
                        <th>Settled Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $r)
                        <tr>
                            <td class="fw-bold font-monospace">{{ $r->period_number }}</td>
                            <td><span class="badge bg-dark text-white px-2 py-1 fs-6">{{ $r->open_card }}</span></td>
                            <td>
                                <span class="badge {{ $r->winner === 'andar' ? 'bg-primary' : ($r->winner === 'bahar' ? 'bg-danger' : 'bg-warning text-dark') }} fs-6">
                                    {{ strtoupper($r->winner) }}
                                </span>
                            </td>
                            <td class="fw-bold fs-6">{{ $r->winning_card }}</td>
                            <td>{{ $r->deal_count }} cards</td>
                            <td><small class="text-muted font-monospace">{{ substr($r->provably_fair_hash, 0, 16) }}...</small></td>
                            <td>
                                {!! $r->manual_override ? '<span class="badge bg-warning text-dark">YES</span>' : '<span class="badge bg-light text-muted border">NO</span>' !!}
                            </td>
                            <td class="text-muted">{{ $r->settled_at ? $r->settled_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $results->links() }}
        </div>
    </div>
</div>
@endsection
