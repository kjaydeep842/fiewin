@extends('layouts.app')

@section('content')

<!-- Page Header -->
<div class="gh-card p-3 mb-3">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('home') }}" class="btn btn-sm btn-light border rounded-circle flex-shrink-0">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-rocket-takeoff-fill text-danger me-1"></i>{{ $game->name }}
            </h6>
            <small class="text-muted" style="font-size: 0.72rem;">Watch the multiplier fly! Cash out before it crashes!</small>
        </div>
    </div>
</div>

<!-- Rocket Flight Chart & Live Multiplier -->
<div class="gh-card mb-3 position-relative overflow-hidden" style="min-height: 240px; background: linear-gradient(180deg, #0f172a 0%, #020617 100%); border-radius: 16px;">
    <div class="position-absolute top-50 start-50 translate-middle text-center" style="z-index: 2;">
        <h1 id="crashMultiplierText" class="display-2 fw-bold font-monospace text-warning mb-1"
            style="text-shadow: 0 0 30px rgba(255,214,0,0.6); letter-spacing: -2px;">1.00x</h1>
        <div id="crashStatusBadge"
             class="badge bg-success bg-opacity-25 text-success fs-6 px-3 py-2 rounded-pill">
            <i class="bi bi-rocket-fill me-1"></i>ROCKET READY
        </div>
    </div>
    <canvas id="crashCanvas" class="w-100 d-block" height="220"></canvas>
</div>

<!-- Controls -->
<div class="gh-card p-3 mb-3">
    <div class="row g-2 mb-3">
        <div class="col-6">
            <label class="form-label text-secondary small fw-semibold mb-1">BET AMOUNT (₹)</label>
            <input type="number" id="crashBetAmount"
                   class="form-control fw-bold"
                   value="100"
                   min="{{ $game->min_entry_fee }}"
                   max="{{ $game->max_entry_fee }}">
        </div>
        <div class="col-6">
            <label class="form-label text-secondary small fw-semibold mb-1">AUTO CASHOUT</label>
            <div class="input-group">
                <input type="number" id="autoCashoutTarget"
                       class="form-control fw-bold"
                       value="2.00" step="0.10" min="1.1">
                <span class="input-group-text bg-light text-muted small">x</span>
            </div>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-6">
            <button class="btn gh-btn-primary w-100 py-3 fs-6 fw-bold rounded-3" id="betCrashBtn" onclick="placeCrashBet()">
                <i class="bi bi-rocket-fill me-1"></i>PLACE BET
            </button>
        </div>
        <div class="col-6">
            <button class="btn gh-btn-success w-100 py-3 fs-6 fw-bold rounded-3" id="cashoutCrashBtn" onclick="cashoutCrash()" disabled>
                <i class="bi bi-cash-stack me-1"></i>CASH OUT
            </button>
        </div>
    </div>
</div>

<!-- Quick Bet Presets -->
<div class="gh-card p-3 mb-3">
    <small class="text-secondary fw-semibold d-block mb-2" style="font-size: 0.72rem;">QUICK BET AMOUNT</small>
    <div class="d-flex gap-2">
        @foreach([10, 50, 100, 500, 1000] as $preset)
        <button type="button"
                class="btn btn-outline-primary btn-sm flex-fill rounded-pill py-1"
                onclick="document.getElementById('crashBetAmount').value = {{ $preset }}"
                style="font-size: 0.78rem;">₹{{ $preset }}</button>
        @endforeach
    </div>
</div>

<!-- My Bet History -->
<div class="gh-card p-3">
    <h6 class="fw-bold text-dark mb-3" style="font-size: 0.9rem;">
        <i class="bi bi-clock-history me-2 text-primary"></i>My Crash History
    </h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle small text-center mb-0">
            <thead class="table-light">
                <tr>
                    <th>Time</th>
                    <th>Bet (₹)</th>
                    <th>Cashed Out</th>
                    <th>Payout</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody id="crashHistoryBody">
                @forelse($myBets as $bet)
                <tr>
                    <td class="text-muted">{{ $bet->created_at->format('H:i:s') }}</td>
                    <td class="fw-bold">₹{{ number_format($bet->bet_amount, 2) }}</td>
                    <td class="fw-bold text-primary">
                        {{ isset($bet->bet_details['cashout_multiplier']) ? $bet->bet_details['cashout_multiplier'] . 'x' : '-' }}
                    </td>
                    <td class="fw-bold {{ $bet->status === 'won' ? 'text-success' : 'text-danger' }}">
                        {{ $bet->status === 'won' ? '+₹' . number_format($bet->win_amount, 2) : '₹0.00' }}
                    </td>
                    <td>
                        @if($bet->status === 'won')
                            <span class="badge bg-success rounded-pill px-2">WON</span>
                        @elseif($bet->status === 'lost')
                            <span class="badge bg-danger rounded-pill px-2">CRASHED</span>
                        @else
                            <span class="badge bg-warning text-dark rounded-pill px-2">FLYING</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-muted py-3">No bets yet. Place your first bet!</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- WIN MODAL -->
<div class="modal fade" id="crashWinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow overflow-hidden text-center">
            <div class="p-3 text-white" style="background: linear-gradient(135deg, #10B981, #059669);">
                <div class="fs-1 mb-1"><i class="bi bi-rocket-takeoff-fill text-warning"></i></div>
                <h5 class="fw-bold mb-0">CASHED OUT!</h5>
            </div>
            <div class="modal-body p-3">
                <p class="text-secondary small mb-2">
                    You cashed out at <span id="crashWinMult" class="fw-bold text-dark">1.00x</span> multiplier!
                </p>
                <div class="p-2 bg-light rounded-3 border mb-3">
                    <small class="text-secondary d-block">TOTAL WINNINGS</small>
                    <h3 id="crashWinAmount" class="fw-bold text-success mb-0 font-monospace">+₹0.00</h3>
                </div>
                <button type="button" class="btn gh-btn-success w-100 py-2 rounded-pill" data-bs-dismiss="modal">
                    CONTINUE
                </button>
            </div>
        </div>
    </div>
</div>

<!-- LOSS MODAL -->
<div class="modal fade" id="crashLossModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow overflow-hidden text-center">
            <div class="p-3 text-white" style="background: linear-gradient(135deg, #EF4444, #DC2626);">
                <div class="fs-2 mb-1"><i class="bi bi-x-octagon-fill"></i></div>
                <h5 class="fw-bold mb-0">ROCKET CRASHED!</h5>
            </div>
            <div class="modal-body p-3">
                <p class="text-secondary small mb-3">
                    Rocket crashed at <span id="crashLossMult" class="fw-bold text-danger">0.00x</span>.
                    Cash out faster next time!
                </p>
                <button type="button" class="btn btn-outline-danger w-100 py-2 rounded-pill" data-bs-dismiss="modal">
                    TRY AGAIN
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const crashWinModal  = new bootstrap.Modal(document.getElementById('crashWinModal'));
    const crashLossModal = new bootstrap.Modal(document.getElementById('crashLossModal'));

    let crashMultiplier = 1.00;
    let crashInterval;
    let inFlight       = false;
    let hasBetted      = false;
    let activeBetId    = null;
    let crashPoint     = 1.5;
    let autoCashoutEnabled = false;

    function updateTopWalletBalance(balStr) {
        document.querySelectorAll('.font-monospace').forEach(el => {
            if (el.innerText.includes('₹') && !el.id.includes('Win') && !el.id.includes('Loss')) {
                el.innerText = '₹' + balStr;
            }
        });
    }

    async function placeCrashBet() {
        if (inFlight) { alert('Wait for the current round to finish.'); return; }

        const betBtn = document.getElementById('betCrashBtn');
        const amount = parseFloat(document.getElementById('crashBetAmount').value);
        const minBet = {{ $game->min_entry_fee }};
        const maxBet = {{ $game->max_entry_fee }};

        if (isNaN(amount) || amount < minBet || amount > maxBet) {
            alert(`Bet amount must be between ₹${minBet} and ₹${maxBet}`);
            return;
        }

        betBtn.disabled = true;
        betBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>PLACING...';

        const formData = new FormData();
        formData.append('game_id', "{{ $game->id }}");
        formData.append('period_number', 'CRASH_' + Date.now());
        formData.append('bet_amount', amount);
        formData.append('bet_type', 'crash_multiplier');

        try {
            const res  = await fetch("{{ route('games.bet') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json();

            if (!data.success) {
                alert(data.message || 'Error placing bet');
                betBtn.disabled = false;
                betBtn.innerHTML = '<i class="bi bi-rocket-fill me-1"></i>PLACE BET';
                return;
            }

            activeBetId = data.bet?.id || null;
            hasBetted   = true;
            autoCashoutEnabled = true;
            updateTopWalletBalance(data.new_balance);
            startRocketFlight();

        } catch (err) {
            console.error(err);
            alert('Failed to place bet. Please try again.');
            betBtn.disabled = false;
            betBtn.innerHTML = '<i class="bi bi-rocket-fill me-1"></i>PLACE BET';
        }
    }

    function startRocketFlight() {
        inFlight        = true;
        crashMultiplier = 1.00;
        crashPoint      = parseFloat((1.2 + Math.random() * 7.5).toFixed(2));

        const cashoutBtn   = document.getElementById('cashoutCrashBtn');
        const statusBadge  = document.getElementById('crashStatusBadge');
        const multText     = document.getElementById('crashMultiplierText');

        cashoutBtn.disabled = false;
        statusBadge.className = 'badge bg-success bg-opacity-25 text-success fs-6 px-3 py-2 rounded-pill';
        statusBadge.innerHTML = '<i class="bi bi-rocket-fill me-1"></i>ROCKET IN FLIGHT';

        const canvas = document.getElementById('crashCanvas');
        const ctx    = canvas.getContext('2d');
        canvas.width = canvas.offsetWidth;
        let x = 0;

        crashInterval = setInterval(() => {
            crashMultiplier = parseFloat((crashMultiplier + 0.04).toFixed(2));
            x += 3;

            // Draw flight path
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.beginPath();
            ctx.moveTo(0, canvas.height);
            ctx.quadraticCurveTo(x / 2, canvas.height - 30, x, canvas.height - (x * 0.75));
            ctx.strokeStyle = '#22C55E';
            ctx.lineWidth   = 3;
            ctx.shadowColor = '#22C55E';
            ctx.shadowBlur  = 8;
            ctx.stroke();

            multText.innerText = crashMultiplier.toFixed(2) + 'x';

            // Auto cashout check
            const autoTarget = parseFloat(document.getElementById('autoCashoutTarget').value);
            if (hasBetted && autoCashoutEnabled && !isNaN(autoTarget) && crashMultiplier >= autoTarget) {
                autoCashoutEnabled = false;
                cashoutCrash();
                return;
            }

            // Crash check
            if (crashMultiplier >= crashPoint) {
                clearInterval(crashInterval);
                inFlight = false;

                statusBadge.className = 'badge bg-danger bg-opacity-25 text-danger fs-6 px-3 py-2 rounded-pill';
                statusBadge.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i>CRASHED @ ' + crashMultiplier.toFixed(2) + 'x';
                cashoutBtn.disabled = true;

                const betBtn = document.getElementById('betCrashBtn');
                betBtn.disabled = false;
                betBtn.innerHTML = '<i class="bi bi-rocket-fill me-1"></i>PLACE BET';

                if (hasBetted) {
                    hasBetted = false;
                    document.getElementById('crashLossMult').innerText = crashMultiplier.toFixed(2) + 'x';
                    crashLossModal.show();
                }
            }
        }, 100);
    }

    async function cashoutCrash() {
        if (!inFlight || !hasBetted) return;

        clearInterval(crashInterval);
        inFlight = false;
        hasBetted = false;
        autoCashoutEnabled = false;

        const cashoutBtn  = document.getElementById('cashoutCrashBtn');
        const betBtn      = document.getElementById('betCrashBtn');
        cashoutBtn.disabled = true;

        const formData = new FormData();
        formData.append('bet_id',    activeBetId);
        formData.append('multiplier', crashMultiplier.toFixed(2));

        try {
            const res  = await fetch("{{ route('games.crash.cashout') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                updateTopWalletBalance(data.new_balance);
                document.getElementById('crashWinMult').innerText   = crashMultiplier.toFixed(2) + 'x';
                document.getElementById('crashWinAmount').innerText = '+₹' + data.win_amount;
                crashWinModal.show();
            } else {
                alert(data.message || 'Cashout failed');
            }
        } catch (err) {
            console.error(err);
            alert('Cashout request failed. Please try again.');
        }

        betBtn.disabled = false;
        betBtn.innerHTML = '<i class="bi bi-rocket-fill me-1"></i>PLACE BET';
    }
</script>
@endpush
@endsection
