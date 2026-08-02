@extends('layouts.admin')

@section('content')
<div class="container-fluid p-4" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-gear-fill text-primary me-2"></i>Andar Bahar Settings</h4>
            <p class="text-muted small mb-0">Configure RTP, odds, round timers, and bet limits.</p>
        </div>
        <a href="{{ route('admin.andar-bahar.dashboard') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success fw-bold alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <form action="{{ route('admin.andar-bahar.settings') }}" method="POST">
            @csrf

            <!-- Active Toggle -->
            <div class="mb-4 pb-3 border-bottom">
                <label class="form-label fw-bold">Game Status</label>
                <select name="is_active" class="form-select form-select-lg">
                    <option value="1" {{ $settings->is_active ? 'selected' : '' }}>ENABLED (Active for players)</option>
                    <option value="0" {{ !$settings->is_active ? 'selected' : '' }}>DISABLED (Under Maintenance)</option>
                </select>
            </div>

            <!-- RTP Setting -->
            <div class="mb-4 pb-3 border-bottom">
                <label class="form-label fw-bold">Return to Player (RTP %)</label>
                <div class="input-group input-group-lg">
                    <input type="number" step="0.5" name="rtp_percentage" class="form-control fw-bold" value="{{ $settings->rtp_percentage }}" min="50" max="100">
                    <span class="input-group-text">%</span>
                </div>
                <small class="text-muted">Default: 96.00%. Lowering RTP increases house margin.</small>
            </div>

            <!-- Payout Odds -->
            <div class="mb-4 pb-3 border-bottom">
                <h6 class="fw-bold text-primary mb-3">Payout Multipliers</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label font-monospace small">ANDAR ODDS (2.0X)</label>
                        <input type="number" step="0.05" name="andar_odds" class="form-control fw-bold" value="{{ $settings->andar_odds }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label font-monospace small">BAHAR ODDS (2.0X)</label>
                        <input type="number" step="0.05" name="bahar_odds" class="form-control fw-bold" value="{{ $settings->bahar_odds }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label font-monospace small">TIE ODDS (9.0X)</label>
                        <input type="number" step="0.5" name="tie_odds" class="form-control fw-bold" value="{{ $settings->tie_odds }}">
                    </div>
                </div>
            </div>

            <!-- Round Timers -->
            <div class="mb-4 pb-3 border-bottom">
                <h6 class="fw-bold text-primary mb-3">Round Duration & Timers</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small font-monospace">TOTAL ROUND DURATION (SECONDS)</label>
                        <input type="number" name="round_seconds" class="form-control fw-bold" value="{{ $settings->round_seconds }}" min="30">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small font-monospace">BETTING OPEN DURATION (SECONDS)</label>
                        <input type="number" name="betting_seconds" class="form-control fw-bold" value="{{ $settings->betting_seconds }}" min="15">
                    </div>
                </div>
            </div>

            <!-- Bet Limits -->
            <div class="mb-4">
                <h6 class="fw-bold text-primary mb-3">Bet Entry Limits</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small font-monospace">MINIMUM BET (₹)</label>
                        <input type="number" step="1" name="min_bet" class="form-control fw-bold" value="{{ $settings->min_bet }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small font-monospace">MAXIMUM BET (₹)</label>
                        <input type="number" step="100" name="max_bet" class="form-control fw-bold" value="{{ $settings->max_bet }}">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow">
                SAVE SETTINGS
            </button>
        </form>
    </div>
</div>
@endsection
