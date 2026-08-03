@extends('layouts.app')

@section('title', 'Crash Rocket - Fiewin')

@section('content')
<div class="container py-3 max-w-md mx-auto">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('games.index') }}" class="btn btn-sm btn-outline-secondary rounded-circle">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="fw-bold mb-0 text-primary">
                <i class="bi bi-rocket-takeoff-fill me-2"></i>Crash Rocket
            </h5>
        </div>
        <div class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 rounded-pill fs-6">
            <i class="bi bi-wallet2 me-1"></i>
            <span id="userWalletBalance">₹{{ number_format(auth()->user()->wallet->main_balance ?? 0, 2) }}</span>
        </div>
    </div>

    <!-- History Pills -->
    <div class="card border-0 shadow-sm rounded-4 mb-3 bg-dark text-white">
        <div class="card-body p-2 d-flex align-items-center overflow-x-auto text-nowrap flex-nowrap" id="crashHistoryPills">
            <span class="text-muted small">Loading history...</span>
        </div>
    </div>

    <!-- Launch Arena Canvas -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-3 bg-dark position-relative">
        <!-- Top Period Badge Overlay -->
        <div class="position-absolute top-0 end-0 p-2 z-3" style="pointer-events: none;">
            <span class="badge bg-dark bg-opacity-75 text-warning border border-warning px-2 py-1 rounded-pill shadow-sm font-monospace fw-bold" style="font-size: 0.72rem;">
                Period #<span id="crashCurrentRoundId">-</span>
            </span>
        </div>
        <canvas id="crashCanvas" style="width: 100%; height: 320px; display: block;"></canvas>
        
        <!-- Center Multiplier Display -->
        <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
            <div id="crashMultiplierText" class="display-2 fw-black text-primary">1.00x</div>
            <div id="crashStatusBadge" class="mt-2"></div>
        </div>
    </div>

    <!-- Active Bet Live Card (Visible when user has a running bet) -->
    <div id="crashActiveBetCard" class="card border-0 shadow-sm rounded-4 mb-3 bg-primary-subtle border-start border-4 border-primary d-none">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-1 mb-2">
                <span class="badge bg-primary px-2 py-1 flex-shrink-0" style="font-size: 0.72rem;"><i class="bi bi-broadcast me-1"></i>ACTIVE ROCKET BET</span>
                <span class="text-muted small fw-bold font-monospace" style="font-size: 0.72rem;">Period #<span id="crashActiveRoundId">-</span></span>
            </div>
            <div class="row text-center g-2 my-1">
                <div class="col-4">
                    <small class="text-muted d-block fw-bold">STAKE</small>
                    <span class="fw-bold text-dark fs-6" id="crashActiveStake">₹0.00</span>
                </div>
                <div class="col-4">
                    <small class="text-muted d-block fw-bold">LIVE PROFIT</small>
                    <span class="fw-bold text-success fs-6" id="crashActiveLiveProfit">+₹0.00</span>
                </div>
                <div class="col-4">
                    <small class="text-muted d-block fw-bold">PAYOUT</small>
                    <span class="fw-bold text-primary fs-6" id="crashActivePayout">₹0.00</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Betting Form Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-3">
            <!-- Stake Row -->
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold mb-1">BET AMOUNT (₹)</label>
                <div class="input-group input-group-lg border rounded-3 overflow-hidden shadow-sm">
                    <span class="input-group-text bg-light border-0 fw-bold text-muted px-3">₹</span>
                    <input type="number" id="crashBetAmount" class="form-control bg-light border-0 fw-bold text-dark fs-5" value="100" min="10" max="50000">
                    <button id="btnHalfBetCrash" class="btn btn-light fw-bold border-0 border-start text-dark px-3 text-nowrap" type="button" style="font-size: 0.9rem;">1/2</button>
                    <button id="btnDoubleBetCrash" class="btn btn-light fw-bold border-0 border-start text-dark px-3 text-nowrap" type="button" style="font-size: 0.9rem;">2X</button>
                </div>
            </div>

            <!-- Auto Cashout Row -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label text-muted small fw-bold mb-0">AUTO CASHOUT (MULTIPLIER)</label>
                    <small class="text-primary fw-bold" id="crashAutoStatusText">OFF</small>
                </div>
                <div class="input-group mb-2">
                    <span class="input-group-text bg-light border-0 fw-bold"><i class="bi bi-magic text-primary"></i></span>
                    <input type="number" step="0.10" id="crashAutoCashoutInput" class="form-control bg-light border-0 fw-bold" placeholder="e.g. 2.00 (Optional)" min="1.01" max="500">
                </div>
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-secondary flex-fill crash-auto-chip" data-val="1.50">1.50x</button>
                    <button class="btn btn-sm btn-outline-secondary flex-fill crash-auto-chip" data-val="2.00">2.00x</button>
                    <button class="btn btn-sm btn-outline-secondary flex-fill crash-auto-chip" data-val="5.00">5.00x</button>
                    <button class="btn btn-sm btn-outline-secondary flex-fill crash-auto-chip" data-val="10.00">10.00x</button>
                    <button class="btn btn-sm btn-outline-danger flex-fill crash-auto-chip" data-val="">OFF</button>
                </div>
            </div>

            <!-- Action Buttons -->
            <button id="betCrashBtn" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow py-3">
                <i class="bi bi-rocket-fill me-2"></i>PLACE ROCKET BET
            </button>
            <button id="cashoutCrashBtn" class="btn btn-warning btn-lg w-100 rounded-pill fw-bold shadow d-none text-dark py-3 border border-2 border-white">
                <i class="bi bi-cash-stack me-2"></i>CASH OUT NOW
            </button>
        </div>
    </div>

    <!-- Tabs for My Orders & Live Players -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 py-2">
            <ul class="nav nav-pills nav-justified bg-light p-1 rounded-pill" id="crashOrderTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold py-2 rounded-pill" id="crashMyOrdersTabBtn" data-bs-toggle="tab" data-bs-target="#crashMyOrdersTab" type="button">
                        <i class="bi bi-receipt me-1"></i>My Orders
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-2 rounded-pill" id="crashLivePlayersTabBtn" data-bs-toggle="tab" data-bs-target="#crashLivePlayersTab" type="button">
                        <i class="bi bi-people-fill me-1"></i>Live Players
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-3 tab-content">
            <!-- My Orders Tab -->
            <div class="tab-pane fade show active" id="crashMyOrdersTab" role="tabpanel">
                <div id="crashMyOrdersList">
                    <div class="text-muted text-center py-2">Loading your Rocket orders...</div>
                </div>
            </div>
            <!-- Live Players Tab -->
            <div class="tab-pane fade" id="crashLivePlayersTab" role="tabpanel">
                <div id="crashLiveBetsList">
                    <div class="text-muted text-center py-2">Loading active players...</div>
                </div>
            </div>
        </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/crash-engine.js') }}"></script>
<script src="{{ asset('js/crash-game-manager.js') }}"></script>
<script>
    (function() {
        function initCrashGame() {
            if (!window.activeCrashManager) {
                window.activeCrashManager = new CrashGameManager();
            }
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCrashGame);
        } else {
            initCrashGame();
        }
    })();
</script>
@endpush
