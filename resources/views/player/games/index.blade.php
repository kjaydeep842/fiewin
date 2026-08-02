@extends('layouts.app')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-controller text-primary me-2"></i>All Games
        </h6>
        <small class="text-muted" style="font-size: 0.72rem;">Choose a game and start winning!</small>
    </div>
    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3">
        {{ $games->count() }} Games Available
    </span>
</div>

{{-- Games Grid --}}
@php
$badgeMap = [
    'fast_parity' => ['HOT',  'bg-danger'],
    'parity'      => ['HOT',  'bg-danger'],
    'mines'       => ['NEW',  'bg-warning text-dark'],
    'crash'       => ['HOT',  'bg-danger'],
    'jet'         => ['HOT',  'bg-danger'],
    'spin_wheel'  => ['50X',  'bg-info text-dark'],
    'dice'        => ['5.5X', 'bg-success'],
    'andar_bahar' => ['HOT',  'bg-primary'],
];
$iconColorMap = [
    'fast_parity' => 'text-success',
    'parity'      => 'text-primary',
    'mines'       => 'text-warning',
    'crash'       => 'text-danger',
    'jet'         => 'text-danger',
    'spin_wheel'  => 'text-info',
    'dice'        => 'text-primary',
    'andar_bahar' => 'text-info',
];
@endphp

<div class="row g-3">
    @foreach($games as $game)
    @php
        $badge     = $badgeMap[$game->code]      ?? null;
        $iconColor = $iconColorMap[$game->code]  ?? 'text-secondary';
    @endphp
    <div class="col-6">
        <a href="{{ route('games.show', $game->code) }}"
           class="gh-card p-3 d-block text-decoration-none h-100 position-relative"
           style="border-radius: 16px; transition: transform 0.18s, box-shadow 0.18s;"
           onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.10)'"
           onmouseleave="this.style.transform='translateY(0)';this.style.boxShadow=''">

            {{-- Badge --}}
            @if($badge)
            <span class="position-absolute top-0 end-0 badge {{ $badge[1] }} m-2 rounded-pill"
                  style="font-size: 0.6rem; letter-spacing: 0.5px;">
                {{ $badge[0] }}
            </span>
            @endif

            {{-- Icon --}}
            <div class="mb-2" style="font-size: 2rem; line-height: 1;">
                <i class="bi {{ $game->icon }} {{ $iconColor }}"></i>
            </div>

            {{-- Name --}}
            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem; line-height: 1.3;">
                {{ $game->name }}
            </h6>

            {{-- Short description --}}
            <p class="text-muted mb-2" style="font-size: 0.7rem; min-height: 28px; line-height: 1.4;">
                {{ Str::limit($game->rules ?? 'Play and win big!', 42) }}
            </p>

            {{-- Footer row --}}
            <div class="d-flex justify-content-between align-items-center mt-auto">
                <span class="badge bg-light text-primary border"
                      style="font-size: 0.65rem; font-weight: 600;">
                    Min ₹{{ number_format($game->min_entry_fee, 0) }}
                </span>
                <span class="badge rounded-pill text-white px-3 py-1"
                      style="background: linear-gradient(135deg, #1E88E5, #1565C0); font-size: 0.72rem;">
                    PLAY
                </span>
            </div>
        </a>
    </div>
    @endforeach
</div>

@endsection
