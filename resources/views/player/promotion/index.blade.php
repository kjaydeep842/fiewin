@extends('layouts.app')

@section('content')
<style>
    /* ── Promotion Page — Mobile-First ── */
    .promo-header-card {
        background: linear-gradient(135deg, #1E88E5 0%, #7C3AED 100%);
        border-radius: 16px;
        padding: 18px 16px 14px;
        color: #fff;
        margin-bottom: 12px;
    }
    .promo-header-card h5 {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 2px;
    }
    .promo-header-card p {
        font-size: 0.74rem;
        opacity: 0.85;
        margin-bottom: 0;
    }
    .claim-btn {
        width: 100%;
        padding: 11px;
        font-size: 0.92rem;
        font-weight: 700;
        border-radius: 12px;
        border: none;
        letter-spacing: 0.03em;
        transition: all 0.2s;
    }
    .claim-btn:not(:disabled) {
        background: linear-gradient(135deg, #22C55E, #15803D);
        color: #fff;
        box-shadow: 0 4px 14px rgba(34,197,94,0.35);
    }
    .claim-btn:disabled {
        background: rgba(255,255,255,0.2);
        color: rgba(255,255,255,0.7);
        cursor: not-allowed;
    }

    /* ── 7-Day Grid ── */
    .checkin-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
        margin-top: 14px;
    }
    .checkin-day {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 7px 1px 6px;
        border-radius: 10px;
        border: 2px solid;
        gap: 1px;
        transition: transform 0.15s;
    }
    /* Claimed days — solid white card with green text */
    .checkin-day.claimed {
        background: #fff;
        border-color: #22C55E;
    }
    .checkin-day.claimed .day-label  { color: #15803D; }
    .checkin-day.claimed .day-icon   { color: #22C55E; }
    .checkin-day.claimed .day-amount { color: #15803D; font-weight: 800; }

    /* Today's day — gold highlight */
    .checkin-day.today {
        background: #fff;
        border-color: #F59E0B;
    }
    .checkin-day.today .day-label  { color: #92400E; }
    .checkin-day.today .day-icon   { color: #F59E0B; }
    .checkin-day.today .day-amount { color: #92400E; font-weight: 800; }

    /* Unclaimed future days — white semi-transparent */
    .checkin-day.unclaimed {
        background: rgba(255,255,255,0.15);
        border-color: rgba(255,255,255,0.35);
    }
    .checkin-day.unclaimed .day-label  { color: rgba(255,255,255,0.75); }
    .checkin-day.unclaimed .day-icon   { color: rgba(255,255,255,0.55); }
    .checkin-day.unclaimed .day-amount { color: rgba(255,255,255,0.9); font-weight: 700; }

    .checkin-day .day-label {
        font-size: 0.5rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        white-space: nowrap;
        line-height: 1;
    }
    .checkin-day .day-icon {
        font-size: 1rem;
        line-height: 1;
    }
    .checkin-day .day-amount {
        font-size: 0.6rem;
        white-space: nowrap;
        line-height: 1;
    }

    /* ── Coupon Card ── */
    .coupon-card {
        background: #fff;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        margin-bottom: 12px;
    }
    .coupon-card h6 {
        font-size: 0.88rem;
        font-weight: 700;
        margin-bottom: 12px;
    }
    .coupon-input {
        border: 2px solid #e2e8f0;
        border-radius: 10px 0 0 10px;
        padding: 10px 12px;
        font-size: 0.82rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        flex: 1;
        min-width: 0;
    }
    .coupon-input:focus {
        border-color: #1E88E5;
        box-shadow: none;
        outline: none;
    }
    .coupon-btn {
        border-radius: 0 10px 10px 0;
        background: linear-gradient(135deg, #1E88E5, #1565C0);
        color: #fff;
        font-weight: 700;
        font-size: 0.82rem;
        padding: 10px 16px;
        border: none;
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* ── Promo Cards ── */
    .promo-event-card {
        background: #fff;
        border-radius: 14px;
        padding: 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 10px;
    }
    .promo-event-icon {
        font-size: 1.6rem;
        background: linear-gradient(135deg, #FEF3C7, #FDE68A);
        border-radius: 12px;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .promo-event-body h6 {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 3px;
        line-height: 1.3;
    }
    .promo-event-body p {
        font-size: 0.74rem;
        color: #64748b;
        margin-bottom: 6px;
        line-height: 1.4;
    }

    @media (max-width: 360px) {
        .checkin-day .day-icon { font-size: 0.85rem; }
        .checkin-day { padding: 5px 1px; gap: 1px; }
        .checkin-day .day-label { font-size: 0.48rem; }
        .checkin-day .day-amount { font-size: 0.52rem; }
    }
</style>

{{-- ── DAILY CHECK-IN CARD ── --}}
<div class="promo-header-card">
    {{-- Top row: icon + title + subtitle --}}
    <div class="d-flex align-items-start gap-2 mb-3">
        <span style="font-size:1.4rem; flex-shrink:0;">📅</span>
        <div style="flex:1; min-width:0;">
            <h5 class="mb-1">7-Day Daily Check-in</h5>
            <p>Claim daily consecutive bonus rewards up to ₹70!</p>
        </div>
    </div>

    {{-- Claim button — full width --}}
    <form action="{{ route('promotion.checkin') }}" method="POST">
        @csrf
        <button type="submit" class="claim-btn" {{ $todayClaimed ? 'disabled' : '' }}>
            {{ $todayClaimed ? '✅ CLAIMED TODAY — Come Back Tomorrow!' : '🎁 CLAIM TODAY\'S REWARD' }}
        </button>
    </form>

    {{-- 7-Day grid --}}
    <div class="checkin-grid">
        @for($day = 1; $day <= 7; $day++)
            @php
                $amt     = $day * 10;
                $claimed = ($day <= $checkinHistory->count());
                $isToday = ($day === $checkinHistory->count() + 1) && !$todayClaimed;
                $dayClass = $claimed ? 'claimed' : ($isToday ? 'today' : 'unclaimed');
            @endphp
            <div class="checkin-day {{ $dayClass }}">
                <span class="day-label">Day {{ $day }}</span>
                <span class="day-icon">
                    <i class="bi bi-{{ $claimed ? 'check-circle-fill' : 'gift-fill' }}"></i>
                </span>
                <span class="day-amount">₹{{ $amt }}</span>
            </div>
        @endfor
    </div>
</div>

{{-- ── REDEEM COUPON ── --}}
<div class="coupon-card">
    <h6><i class="bi bi-ticket-perforated-fill text-primary me-2"></i>Redeem Coupon Code</h6>
    <form action="{{ route('promotion.coupon') }}" method="POST">
        @csrf
        <div class="d-flex w-100">
            <input type="text"
                   name="code"
                   class="coupon-input form-control"
                   placeholder="ENTER CODE (e.g. RIVEXA50)"
                   required>
            <button type="submit" class="coupon-btn">REDEEM</button>
        </div>
    </form>
</div>

{{-- ── ACTIVE EVENTS & BONUSES ── --}}
<h6 class="fw-bold text-dark mb-2" style="font-size:0.88rem;">
    <i class="bi bi-megaphone-fill text-warning me-2"></i>Active Events &amp; Bonuses
</h6>

@foreach($promotions as $promo)
<div class="promo-event-card">
    <div class="promo-event-icon">
        <i class="bi bi-award-fill text-warning"></i>
    </div>
    <div class="promo-event-body" style="flex:1; min-width:0;">
        <h6>{{ $promo->title }}</h6>
        <p>{{ $promo->description }}</p>
        <span class="badge text-bg-success" style="font-size:0.68rem;">✓ Active Bonus</span>
    </div>
</div>
@endforeach

@push('scripts')
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.soundManager) window.soundManager.play('reward');
        if (window.animationManager) {
            window.animationManager.triggerConfetti(80);
            window.animationManager.animateCoinsToWallet();
        }
    });
</script>
@endif
@endpush
@endsection
