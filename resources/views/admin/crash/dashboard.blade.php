@extends('layouts.admin')

@section('title', 'Crash Rocket Admin Control')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-primary fw-bold">
                <i class="bi bi-rocket-takeoff-fill me-2"></i>Crash Rocket Control Center
            </h1>
            <p class="text-muted small mb-0">Independent Crash Rocket engine monitoring & manual control</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.crash-admin.settings') }}" class="btn btn-outline-secondary">
                <i class="bi bi-gear me-1"></i>Settings
            </a>
            <a href="{{ route('admin.crash-admin.reports') }}" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-bar-graph me-1"></i>Reports
            </a>
        </div>
    </div>

    <!-- Overview Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="text-muted small fw-bold">TODAY'S ROCKET STAKES</div>
                <div class="fs-3 fw-black text-dark mt-1">₹{{ number_format($todayBetsTotal, 2) }}</div>
                <div class="small text-muted">{{ $todayBetsCount }} total bets</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="text-muted small fw-bold">TODAY'S WINNINGS</div>
                <div class="fs-3 fw-black text-danger mt-1">₹{{ number_format($todayWinsTotal, 2) }}</div>
                <div class="small text-muted">Paid to players</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="text-muted small fw-bold">HOUSE NET PROFIT</div>
                <div class="fs-3 fw-black {{ $houseProfit >= 0 ? 'text-success' : 'text-danger' }} mt-1">
                    ₹{{ number_format($houseProfit, 2) }}
                </div>
                <div class="small text-muted">Stakes - Winnings</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="text-muted small fw-bold">ACTIVE PLAYERS</div>
                <div class="fs-3 fw-black text-primary mt-1">{{ $uniquePlayersCount }}</div>
                <div class="small text-muted">Unique bettors today</div>
            </div>
        </div>
    </div>

    <!-- Manual Target Override Control -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 fw-bold py-3">
            <i class="bi bi-sliders me-2 text-primary"></i>Next Crash Rocket Target Override
        </div>
        <div class="card-body">
            <form action="{{ route('admin.crash-admin.override') }}" method="POST" class="row g-3 align-items-center">
                @csrf
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-bold">FORCE MULTIPLIER TARGET (e.g., 1.50, 10.00, 50.00)</label>
                    <input type="number" step="0.01" min="1.01" max="500" name="multiplier" class="form-control form-control-lg" placeholder="Leave empty for provably fair random" value="{{ $settings->manual_override_multiplier }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">SET TARGET</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
