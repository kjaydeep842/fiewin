@extends('layouts.app')

@section('content')

<!-- Page Header & Mode Switcher (White Glassmorphism Card) -->
<div class="gh-card p-3 mb-3 bg-white border border-light shadow-sm rounded-4">
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('home') }}" class="btn btn-sm btn-light border rounded-circle flex-shrink-0">
                <i class="bi bi-arrow-left text-dark"></i>
            </a>
            <div>
                <h6 class="fw-bold mb-0 text-dark" id="gameModeTitle">
                    <i class="bi bi-airplane-engines-fill text-success me-2"></i>Jet Flight Arena
                </h6>
                <small class="text-secondary" style="font-size: 0.72rem;">Live Multiplayer Crash Engine</small>
            </div>
        </div>

        <!-- Mode Toggle Tabs & History Link -->
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('games.crash.history') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                <i class="bi bi-journal-text me-1"></i>My Orders
            </a>
            <div class="btn-group btn-group-sm p-1 rounded-pill bg-light border" role="group">
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold" id="btnModeJet" onclick="gameManager.switchMode('jet')">
                    ✈️ Jet
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold" id="btnModeRocket" onclick="gameManager.switchMode('rocket')">
                    🚀 Rocket
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Round History Pill Bar -->
<div class="d-flex align-items-center gap-2 overflow-x-auto pb-2 mb-3 no-scrollbar" id="historyPillContainer">
    <span class="badge bg-white text-secondary border shadow-sm flex-shrink-0 py-2 px-3" style="font-size: 0.72rem;">
        <i class="bi bi-clock-history me-1 text-primary"></i>HISTORY
    </span>
    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success fw-bold px-3 py-1">14.50x</span>
    <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger fw-bold px-3 py-1">1.12x</span>
    <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning fw-bold px-3 py-1">1.85x</span>
    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success fw-bold px-3 py-1">4.82x</span>
    <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger fw-bold px-3 py-1">1.05x</span>
    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success fw-bold px-3 py-1">22.40x</span>
</div>

<!-- Flight Area (Dark Sky Night Canvas ~40-45% Height) -->
<div class="card mb-3 position-relative overflow-hidden border-0 shadow-lg" style="height: 310px; background: #0B0F19; border-radius: 24px;">
    <!-- Live Floating Multiplier Overlay -->
    <div class="position-absolute top-50 start-50 translate-middle text-center" style="z-index: 5; pointer-events: none;">
        <h1 id="crashMultiplierText" class="display-1 fw-black font-monospace text-warning mb-1"
            style="text-shadow: 0 0 35px rgba(245, 158, 11, 0.8); letter-spacing: -3px;">1.00x</h1>
        <div id="crashStatusBadge" class="badge bg-primary bg-opacity-10 text-primary fs-6 px-4 py-2 rounded-pill border border-primary">
            BETTING OPEN
        </div>
    </div>

    <!-- 60FPS Flight Canvas Renderer -->
    <canvas id="crashCanvas" class="w-100 d-block" height="310"></canvas>
</div>

<!-- Game Statistics Card (White Glassmorphism) -->
<div class="gh-card p-3 mb-3 bg-white border border-light shadow-sm rounded-4">
    <div class="row g-2 text-center" style="font-size: 0.78rem;">
        <div class="col-3 border-end">
            <span class="text-secondary d-block" style="font-size: 0.65rem;">ONLINE PLAYERS</span>
            <span class="fw-bold text-dark fs-6" id="statOnlinePlayers">1,420</span>
        </div>
        <div class="col-3 border-end">
            <span class="text-secondary d-block" style="font-size: 0.65rem;">HIGHEST TODAY</span>
            <span class="fw-bold text-success fs-6">84.50x</span>
        </div>
        <div class="col-3 border-end">
            <span class="text-secondary d-block" style="font-size: 0.65rem;">AVG MULTIPLIER</span>
            <span class="fw-bold text-primary fs-6">3.12x</span>
        </div>
        <div class="col-3">
            <span class="text-secondary d-block" style="font-size: 0.65rem;">TOTAL POOL</span>
            <span class="fw-bold text-warning fs-6">₹142,500</span>
        </div>
    </div>
</div>

<!-- Bet Panel Card (White Glassmorphism) -->
<div class="gh-card p-3 mb-3 bg-white border border-light shadow-sm rounded-4">
    <div class="row g-2 mb-3">
        <div class="col-6">
            <label class="form-label text-secondary small fw-bold mb-1">BET AMOUNT (₹)</label>
            <div class="input-group">
                <input type="number" id="crashBetAmount"
                       class="form-control form-control-lg fw-bold text-dark border-secondary border-opacity-25"
                       value="100"
                       min="{{ $game->min_entry_fee }}"
                       max="{{ $game->max_entry_fee }}">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnHalfBet">1/2</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDoubleBet">2X</button>
            </div>
        </div>
        <div class="col-6">
            <label class="form-label text-secondary small fw-bold mb-1">AUTO CASHOUT</label>
            <div class="input-group">
                <input type="number" id="autoCashoutTarget"
                       class="form-control form-control-lg fw-bold text-dark border-secondary border-opacity-25"
                       value="2.00" step="0.10" min="1.1">
                <span class="input-group-text bg-light text-secondary fw-bold">x</span>
            </div>
        </div>
    </div>

    <!-- Quick Bet Chips -->
    <div class="d-flex gap-1 gap-sm-2 mb-3">
        @foreach([10, 50, 100, 500, 1000] as $preset)
        <button type="button"
                class="btn btn-sm btn-outline-primary flex-fill rounded-pill py-1 fw-bold"
                onclick="document.getElementById('crashBetAmount').value = {{ $preset }}; if(window.soundManager) window.soundManager.play('click');"
                style="font-size: 0.75rem;">₹{{ $preset }}</button>
        @endforeach
    </div>

    <!-- Place Bet / Cash Out Action Buttons -->
    <div class="row g-2">
        <div class="col-6">
            <button class="btn gh-btn-primary w-100 py-3 fs-6 fw-bold rounded-4 shadow-sm" id="betCrashBtn">
                <i class="bi bi-rocket-fill me-1"></i>PLACE BET
            </button>
        </div>
        <div class="col-6">
            <button class="btn gh-btn-success w-100 py-3 fs-6 fw-bold rounded-4 shadow-sm" id="cashoutCrashBtn" disabled>
                <i class="bi bi-cash-stack me-1"></i>CASH OUT
            </button>
        </div>
    </div>
</div>

<!-- Live Players Panel (White Glassmorphism) -->
<div class="gh-card p-3 mb-3 bg-white border border-light shadow-sm rounded-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-bold text-dark mb-0" style="font-size: 0.9rem;">
            <i class="bi bi-people-fill me-2 text-primary"></i>Live Round Players
        </h6>
        <div class="d-flex gap-2">
            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">
                ✈️ Flying: <strong id="statFlyingPlayers">0</strong>
            </span>
            <span class="badge bg-success bg-opacity-10 text-success border border-success">
                💰 Cashed: <strong id="statCashedPlayers">0</strong>
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle small text-center mb-0">
            <thead class="table-light">
                <tr class="text-secondary">
                    <th class="text-start">User</th>
                    <th>Bet (₹)</th>
                    <th>Status</th>
                    <th>Profit</th>
                </tr>
            </thead>
            <tbody id="livePlayersBody">
                <!-- Dynamically updated by CrashGameManager -->
            </tbody>
        </table>
    </div>
</div>

<!-- Win Result Modal -->
<div class="modal fade" id="crashWinModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4 border-0 shadow-lg rounded-4" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #fff;">
            <div class="fs-1 mb-2">🎉</div>
            <h4 class="fw-bold mb-1">CASHED OUT!</h4>
            <div class="display-6 fw-bold font-monospace mb-2" id="crashWinMult">1.50x</div>
            <div class="bg-white bg-opacity-20 rounded-3 p-2 mb-3">
                <small class="d-block text-white opacity-75" style="font-size: 0.75rem;">YOU WON</small>
                <span class="fs-3 fw-bold text-warning" id="crashWinAmount">+₹0.00</span>
            </div>
            <button type="button" class="btn btn-light fw-bold rounded-pill w-100 text-success" data-bs-dismiss="modal">CONTINUE WATCHING</button>
        </div>
    </div>
</div>

<!-- Loss Result Modal -->
<div class="modal fade" id="crashLossModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4 border-0 shadow-lg rounded-4" style="background: linear-gradient(135deg, #DC2626 0%, #991B1B 100%); color: #fff;">
            <div class="fs-1 mb-2">💥</div>
            <h4 class="fw-bold mb-1">CRASHED!</h4>
            <p class="small mb-3 opacity-75">Round crashed at <span id="crashLossMult" class="fw-bold font-monospace">1.00x</span></p>
            <button type="button" class="btn btn-light fw-bold rounded-pill w-100 text-danger" data-bs-dismiss="modal">NEXT ROUND</button>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/crash-engine.js') }}"></script>
<script src="{{ asset('js/crash-game-manager.js') }}"></script>
<script>
    let gameManager;

    document.addEventListener('DOMContentLoaded', function() {
        gameManager = new CrashGameManager({
            gameId: "{{ $game->id }}",
            minBet: {{ $game->min_entry_fee }},
            maxBet: {{ $game->max_entry_fee }},
            csrfToken: "{{ csrf_token() }}",
            routes: {
                state: "{{ route('games.crash.state') }}",
                bet: "{{ route('games.crash.bet') }}",
                cashout: "{{ route('games.crash.cashout') }}"
            }
        });
    });

    function updateTopWalletBalance(newBal) {
        const topEl = document.getElementById('topWalletBalance');
        if (topEl) {
            topEl.innerText = '₹' + parseFloat(newBal).toFixed(2);
        }
    }
</script>
@endpush
@endsection
