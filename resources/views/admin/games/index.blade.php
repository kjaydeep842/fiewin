@extends('layouts.admin')

@section('page-title', 'Game Engine Control & RTP Management')
@section('page-subtitle', 'Configure house edge percentage and force period winning numbers')

@section('content')
<div class="row g-3 mb-4">
    @foreach($games as $game)
    <div class="col-md-6">
        <div class="admin-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0" style="color: #111827;">
                    <i class="bi {{ $game->icon }} text-warning me-2"></i>{{ $game->name }}
                </h6>
                <form action="{{ route('admin.games.toggle', $game->id) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="btn btn-sm {{ $game->is_active ? 'btn-outline-success' : 'btn-outline-secondary' }} rounded-pill px-3"
                        style="font-size: 0.78rem;">
                        {{ $game->is_active ? '● ENABLED' : '○ DISABLED' }}
                    </button>
                </form>
            </div>

            <!-- RTP Adjustment -->
            <form action="{{ route('admin.games.rtp', $game->id) }}" method="POST" class="mb-3">
                @csrf
                <label class="form-label text-muted small fw-semibold">RTP (RETURN TO PLAYER) %</label>
                <div class="input-group">
                    <input type="number" name="rtp_percentage"
                           class="form-control admin-input fw-bold"
                           value="{{ $game->rtp_percentage }}" step="0.5" min="0" max="100" required>
                    <span class="input-group-text bg-light text-muted border">%</span>
                    <button type="submit" class="btn btn-admin-primary px-3">Update RTP</button>
                </div>
                <small class="text-muted" style="font-size: 0.75rem;">
                    House Edge: <strong class="text-danger">{{ 100 - $game->rtp_percentage }}%</strong>
                </small>
            </form>

            @if($game->code === 'fast_parity' || $game->code === 'parity')
            <!-- Manual Override -->
            <div class="p-3 rounded-3 border border-warning border-opacity-50"
                 style="background: rgba(245,158,11,0.05);">
                <h6 class="fw-bold mb-2" style="color: #b45309; font-size: 0.85rem;">
                    <i class="bi bi-gear-fill me-1"></i>Manual Result Override
                </h6>
                <form action="{{ route('admin.games.override', $game->id) }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="text" name="period_number"
                                   class="form-control admin-input form-control-sm"
                                   placeholder="Period #" required>
                        </div>
                        <div class="col-6">
                            <select name="manual_number" class="form-select admin-input form-select-sm" required>
                                <option value="">Choose Number (0-9)</option>
                                @for($n = 0; $n <= 9; $n++)
                                    <option value="{{ $n }}">Winning Number {{ $n }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm w-100 mt-2 fw-bold rounded-pill">
                        <i class="bi bi-lightning-fill me-1"></i>FORCE WINNING NUMBER
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection
