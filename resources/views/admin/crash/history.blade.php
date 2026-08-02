@extends('layouts.admin')

@section('title', 'Crash Round History - Admin')

@section('content')
<div class="container-fluid py-4">
    <h3 class="fw-bold text-primary mb-4"><i class="bi bi-clock-history me-2"></i>Crash Rocket Round History</h3>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Round ID</th>
                            <th>Crash Multiplier</th>
                            <th>Status</th>
                            <th>Ended At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $r)
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td class="fw-bold text-primary">{{ $r->round_id }}</td>
                            <td><span class="badge bg-primary fs-6">{{ $r->crash_multiplier }}x</span></td>
                            <td><span class="badge bg-secondary">{{ $r->status }}</span></td>
                            <td>{{ $r->ended_at ? $r->ended_at->format('Y-m-d H:i:s') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $results->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
