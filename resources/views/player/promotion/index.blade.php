@extends('layouts.app')

@section('content')
<!-- Daily Check-in Card -->
<div class="gh-card p-4 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-1"><i class="bi bi-calendar-check-fill text-warning me-2"></i>7-Day Daily Check-in</h5>
            <p class="text-secondary small mb-0">Claim daily consecutive bonus rewards up to ₹70!</p>
        </div>
        <form action="{{ route('promotion.checkin') }}" method="POST">
            @csrf
            <button type="submit" class="btn gh-btn-success px-4 py-2 fw-bold rounded-pill" {{ $todayClaimed ? 'disabled' : '' }}>
                {{ $todayClaimed ? 'CLAIMED TODAY' : 'CLAIM REWARD' }}
            </button>
        </form>
    </div>

    <!-- 7-Day Rewards Grid -->
    <div class="row g-1 g-sm-2 text-center mt-2">
        @for($day = 1; $day <= 7; $day++)
            @php $amt = $day * 10; @endphp
            <div class="col" style="flex: 0 0 14.28%; max-width: 14.28%;">
                <div class="p-1 p-sm-2 rounded-3 border {{ ($day <= $checkinHistory->count()) ? 'bg-success bg-opacity-10 border-success text-success' : 'bg-light border-secondary text-secondary' }}">
                    <div class="fw-bold text-truncate mb-1" style="font-size: 0.62rem;">Day {{ $day }}</div>
                    <div class="fs-5 fs-sm-4"><i class="bi bi-gift-fill"></i></div>
                    <div class="fw-bold text-primary text-truncate" style="font-size: 0.65rem;">₹{{ $amt }}</div>
                </div>
            </div>
        @endfor
    </div>
</div>

<!-- Redeem Coupon Code Card -->
<div class="gh-card p-4 mb-3">
    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-ticket-perforated-fill text-primary me-2"></i>Redeem Coupon Code</h6>
    <form action="{{ route('promotion.coupon') }}" method="POST">
        @csrf
        <div class="input-group">
            <input type="text" name="code" class="form-control text-uppercase fw-bold" placeholder="ENTER COUPON CODE (e.g. GAMEHUB50)" required>
            <button type="submit" class="btn gh-btn-primary px-4 rounded-end-3">REDEEM</button>
        </div>
    </form>
</div>

<!-- Active Promotions List -->
<h6 class="fw-bold text-dark mb-2"><i class="bi bi-megaphone-fill text-warning me-2"></i>Active Events & Bonuses</h6>
<div class="row g-2">
    @foreach($promotions as $promo)
        <div class="col-md-6">
            <div class="gh-card p-3 d-flex align-items-center gap-3">
                <div class="fs-2 text-warning p-2 bg-light rounded-3 border"><i class="bi bi-award-fill"></i></div>
                <div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.9rem;">{{ $promo->title }}</h6>
                    <p class="text-secondary small mb-1" style="font-size: 0.78rem;">{{ $promo->description }}</p>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success">Active Bonus</span>
                </div>
            </div>
        </div>
    @endforeach
</div>

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
