@extends('layouts.admin')

@section('title', 'Crash Settings - Admin')

@section('content')
<div class="container-fluid py-4">
    <h3 class="fw-bold text-primary mb-4"><i class="bi bi-gear me-2"></i>Crash Rocket Game Settings</h3>

    @if(session('success'))
        <div class="alert alert-success rounded-4">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 max-w-2xl">
        <div class="card-body p-4">
            <form action="{{ route('admin.crash-admin.settings') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold">Round Duration (Seconds)</label>
                    <input type="number" name="round_seconds" class="form-control" value="{{ $settings->round_seconds }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Betting Duration (Seconds)</label>
                    <input type="number" name="betting_seconds" class="form-control" value="{{ $settings->betting_seconds }}">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Min Bet Amount (₹)</label>
                        <input type="number" name="min_bet" class="form-control" value="{{ $settings->min_bet }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Max Bet Amount (₹)</label>
                        <input type="number" name="max_bet" class="form-control" value="{{ $settings->max_bet }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Target RTP (%)</label>
                    <input type="number" step="0.1" name="rtp_percentage" class="form-control" value="{{ $settings->rtp_percentage }}">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Game Status</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ $settings->is_active ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$settings->is_active ? 'selected' : '' }}>Disabled (Maintenance)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-lg fw-bold w-100">SAVE SETTINGS</button>
            </form>
        </div>
    </div>
</div>
@endsection
