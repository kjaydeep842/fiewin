@extends('layouts.app')

@section('content')
<!-- Wallet Balance Card — fully responsive -->
<div class="gh-card mb-3"
     style="background: linear-gradient(135deg, #1E88E5 0%, #1565C0 100%); color:#fff; border-radius:16px; overflow:hidden;">

    {{-- Top row: label + balance --}}
    <div class="px-3 pt-3 pb-2">
        <span class="d-block opacity-75" style="font-size:0.68rem; letter-spacing:0.06em; font-weight:600;">TOTAL BALANCE</span>
        <div class="d-flex align-items-baseline gap-2 flex-wrap">
            <span class="fw-bold font-monospace"
                  style="font-size: clamp(1.15rem, 5vw, 1.75rem); letter-spacing:-0.01em; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100%;">
                ₹{{ number_format(auth()->user()->wallet?->total_balance ?? 0, 2) }}
            </span>
        </div>
    </div>

    {{-- Bottom row: two equal full-width buttons --}}
    <div class="d-flex border-top border-white border-opacity-25">
        <a href="{{ route('wallet.index') }}"
           class="flex-fill text-center py-2 fw-bold text-white text-decoration-none"
           style="font-size:0.82rem; background:rgba(255,255,255,0.18); border-right:1px solid rgba(255,255,255,0.2); letter-spacing:0.04em;">
            <i class="bi bi-plus-circle me-1"></i>DEPOSIT
        </a>
        <a href="{{ route('wallet.index') }}"
           class="flex-fill text-center py-2 fw-bold text-white text-decoration-none"
           style="font-size:0.82rem; background:rgba(255,255,255,0.08); letter-spacing:0.04em;">
            <i class="bi bi-arrow-up-circle me-1"></i>WITHDRAW
        </a>
    </div>
</div>


<!-- Image Hero Slider Carousel -->
<div id="homeHeroCarousel" class="carousel slide mb-3" data-bs-ride="carousel">
    <div class="carousel-inner rounded-4 shadow-sm" style="overflow: hidden;">
        <div class="carousel-item active" style="background: linear-gradient(135deg, #8B5CF6, #6D28D9); padding: 20px; color: #fff;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-warning text-dark font-monospace fw-bold mb-1">PROMOTION</span>
                    <h5 class="fw-bold mb-1">100% Welcome Match</h5>
                    <p class="small opacity-75 mb-0" style="font-size: 0.75rem;">Double your wallet balance on your first deposit!</p>
                </div>
                <div class="display-4 text-warning"><i class="bi bi-gift-fill"></i></div>
            </div>
        </div>
        <div class="carousel-item" style="background: linear-gradient(135deg, #22C55E, #15803D); padding: 20px; color: #fff;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-light text-dark font-monospace fw-bold mb-1">30s COLOR PREDICTION</span>
                    <h5 class="fw-bold mb-1">Fast-Parity Room</h5>
                    <p class="small opacity-75 mb-0" style="font-size: 0.75rem;">Win 9X on Number predictions & 2X on Colors!</p>
                </div>
                <div class="display-4 text-white"><i class="bi bi-lightning-fill"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- 4-Column Quick Reward Buttons Grid -->
<div class="row g-2 mb-3 text-center">
    <style>
        .quick-action-btn {
            padding: 10px 4px 8px 4px !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            text-decoration: none;
            color: #1a1a1a;
        }
        .quick-action-btn .qa-icon {
            font-size: 1.4rem;
            line-height: 1;
            display: block;
        }
        .quick-action-btn .qa-label {
            font-size: 0.62rem;
            font-weight: 600;
            line-height: 1.1;
            white-space: nowrap;
            display: block;
            letter-spacing: -0.01em;
        }
        @media (max-width: 360px) {
            .quick-action-btn .qa-label {
                font-size: 0.56rem;
            }
            .quick-action-btn .qa-icon {
                font-size: 1.2rem;
            }
        }
    </style>
    <div class="col-3">
        <a href="{{ route('promotion.index') }}" class="gh-card quick-action-btn">
            <span class="qa-icon text-warning"><i class="bi bi-calendar-check-fill"></i></span>
            <span class="qa-label">Daily Check-in</span>
        </a>
    </div>
    <div class="col-3">
        <a href="{{ route('promotion.index') }}" class="gh-card quick-action-btn">
            <span class="qa-icon text-success"><i class="bi bi-award-fill"></i></span>
            <span class="qa-label">Task Reward</span>
        </a>
    </div>
    <div class="col-3">
        <a href="{{ route('referral.index') }}" class="gh-card quick-action-btn">
            <span class="qa-icon text-primary"><i class="bi bi-people-fill"></i></span>
            <span class="qa-label">Invite Earn</span>
        </a>
    </div>
    <div class="col-3">
        <a href="{{ route('wallet.index') }}" class="gh-card quick-action-btn">
            <span class="qa-icon text-danger"><i class="bi bi-wallet2"></i></span>
            <span class="qa-label">Recharge</span>
        </a>
    </div>
</div>


{{-- 2-Column Responsive Games Grid (dynamic — all active games) --}}
<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-fire text-danger me-1"></i>Featured Games</h6>
    <a href="{{ route('games.index') }}" class="text-primary text-decoration-none small fw-semibold">View All <i class="bi bi-chevron-right"></i></a>
</div>

<div class="row g-2 mb-3">
    @php
    // Badge config per game code
    $gameBadges = [
        'fast_parity' => ['label' => 'HOT',  'class' => 'bg-danger'],
        'parity'      => ['label' => 'HOT',  'class' => 'bg-danger'],
        'mines'       => ['label' => 'NEW',  'class' => 'bg-warning text-dark'],
        'crash'       => ['label' => 'HOT',  'class' => 'bg-danger'],
        'jet'         => ['label' => 'HOT',  'class' => 'bg-danger'],
        'spin_wheel'  => ['label' => '50X',  'class' => 'bg-info text-dark'],
        'dice'        => ['label' => '5.5X', 'class' => 'bg-success'],
    ];
    // Icon color per game code
    $gameIconColors = [
        'fast_parity' => 'text-success',
        'parity'      => 'text-primary',
        'mines'       => 'text-warning',
        'crash'       => 'text-danger',
        'jet'         => 'text-danger',
        'spin_wheel'  => 'text-info',
        'dice'        => 'text-primary',
    ];
    @endphp

    @foreach($featuredGames as $game)
    <div class="col-6">
        <a href="{{ route('games.show', $game->code) }}"
           class="gh-card p-3 text-decoration-none d-block h-100 text-dark position-relative">

            {{-- Badge --}}
            @if(isset($gameBadges[$game->code]))
            <span class="position-absolute top-0 end-0 badge {{ $gameBadges[$game->code]['class'] }} m-2"
                  style="font-size: 0.6rem;">
                {{ $gameBadges[$game->code]['label'] }}
            </span>
            @endif

            {{-- Icon --}}
            <div class="fs-2 mb-1 {{ $gameIconColors[$game->code] ?? 'text-primary' }}">
                <i class="bi {{ $game->icon }}"></i>
            </div>

            {{-- Name & Description --}}
            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;">{{ $game->name }}</h6>
            <span class="text-muted small d-block" style="font-size: 0.7rem; min-height: 18px;">
                {{ Str::limit($game->rules ?? '', 30) }}
            </span>

            {{-- Min bet + PLAY button --}}
            <div class="mt-2 d-flex justify-content-between align-items-center">
                <span class="badge bg-light text-primary border" style="font-size: 0.65rem;">
                    Min ₹{{ number_format($game->min_entry_fee, 0) }}
                </span>
                <span class="btn btn-sm gh-btn-primary py-0 px-2" style="font-size: 0.72rem;">PLAY</span>
            </div>
        </a>
    </div>
    @endforeach
</div>



<!-- Referral Banner Card -->
<div class="gh-card p-3 mb-3" style="background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%); border: 1px solid #A7F3D0;">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-success mb-1">3-TIER MLM</span>
            <h6 class="fw-bold text-dark mb-1">Invite Friends & Earn Lifetime</h6>
            <p class="small text-muted mb-0" style="font-size: 0.72rem;">Earn 3% Level 1, 2% Level 2, and 1% Level 3 on every bet!</p>
        </div>
        <a href="{{ route('referral.index') }}" class="btn btn-sm gh-btn-success rounded-pill px-3 text-decoration-none">INVITE</a>
    </div>
</div>

<!-- Live Winners Card Table -->
<div class="gh-card p-3">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-trophy-fill text-warning me-1"></i>Recent Winners</h6>
        <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 0.65rem;">LIVE</span>
    </div>
    <div class="table-responsive">
        <table class="table table-borderless table-sm align-middle text-nowrap mb-0" style="font-size: 0.8rem;">
            <tbody>
                @foreach($liveWinners as $w)
                    <tr class="border-bottom border-light">
                        <td class="text-secondary"><i class="bi bi-person-circle me-1"></i>{{ $w['user'] }}</td>
                        <td><span class="badge bg-light text-primary border" style="font-size: 0.65rem;">{{ $w['game'] }}</span></td>
                        <td class="fw-bold text-success">+₹{{ number_format($w['amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
