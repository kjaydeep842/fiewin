<header class="sticky-top bg-dark bg-opacity-95 border-bottom border-secondary border-opacity-25 px-3 py-2">
    <div class="container d-flex justify-content-between align-items-center">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="d-flex align-items-center text-decoration-none text-white fw-bold fs-4">
            <span class="p-1 px-2 rounded-3 me-2" style="background: linear-gradient(135deg, #6366f1, #d500f9);"><i class="bi bi-controller"></i></span>
            <span>Game<span style="color: var(--gh-accent-green);">Hub</span></span>
        </a>

        @auth
            <!-- Wallet balance pill & Deposit button -->
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('wallet.index') }}" class="text-decoration-none px-3 py-1 rounded-pill d-flex align-items-center gap-2" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);">
                    <i class="bi bi-wallet2 text-warning fs-5"></i>
                    <div class="lh-1">
                        <small class="text-muted d-block" style="font-size: 0.65rem;">BALANCE</small>
                        <span class="fw-bold text-white fs-6">₹{{ number_format(auth()->user()->wallet?->main_balance ?? 0, 2) }}</span>
                    </div>
                </a>
                <a href="{{ route('wallet.index') }}" class="btn btn-sm gh-btn-success d-flex align-items-center gap-1">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>DEPOSIT</span>
                </a>
            </div>
        @else
            <div class="d-flex gap-2">
                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light rounded-pill px-3">Login</a>
                <a href="{{ route('register') }}" class="btn btn-sm gh-btn-primary rounded-pill px-3">Register</a>
            </div>
        @endauth
    </div>
</header>
