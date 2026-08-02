@extends('layouts.admin')

@section('title', 'Jet Reports - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-success mb-0"><i class="bi bi-file-earmark-bar-graph me-2"></i>Jet Flight Reports</h3>
        <a href="{{ route('admin.jet.reports', ['range' => $range, 'export' => 'csv']) }}" class="btn btn-outline-success fw-bold">
            <i class="bi bi-download me-1"></i>EXPORT CSV
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Bet ID</th>
                            <th>Round ID</th>
                            <th>User</th>
                            <th>Bet Amount</th>
                            <th>Cashout Mult</th>
                            <th>Profit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bets as $b)
                        <tr>
                            <td>#{{ $b->id }}</td>
                            <td>{{ $b->round_id }}</td>
                            <td>{{ $b->user->name ?? 'User_'.$b->user_id }}</td>
                            <td>₹{{ number_format($b->bet_amount, 2) }}</td>
                            <td>{{ $b->cashout_multiplier ? $b->cashout_multiplier . 'x' : '-' }}</td>
                            <td class="{{ $b->profit > 0 ? 'text-success fw-bold' : 'text-muted' }}">₹{{ number_format($b->profit, 2) }}</td>
                            <td><span class="badge {{ $b->status === 'cashed_out' ? 'bg-success' : 'bg-danger' }}">{{ strtoupper($b->status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
