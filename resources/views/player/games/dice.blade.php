@extends('layouts.app')

@section('content')

{{-- Page Header --}}
<div class="gh-card p-3 mb-3">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('home') }}" class="btn btn-sm btn-light border rounded-circle flex-shrink-0">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-dice-5-fill text-primary me-1"></i>{{ $game->name }}
            </h6>
            <small class="text-muted" style="font-size: 0.72rem;">Pick a number or range — roll the dice and win big!</small>
        </div>
    </div>
</div>

{{-- Dice Roll Arena --}}
<div class="gh-card p-4 mb-3 text-center">

    {{-- Animated Dice Display --}}
    <div class="mb-3 d-flex align-items-center justify-content-center gap-3">
        <div id="diceDisplay" class="dice-face" data-face="1">
            <svg id="diceSvg" viewBox="0 0 100 100" width="100" height="100">
                <rect x="2" y="2" width="96" height="96" rx="16" fill="url(#diceGrad)" stroke="#E5E7EB" stroke-width="2"/>
                <defs>
                    <linearGradient id="diceGrad" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#EFF6FF"/>
                        <stop offset="100%" stop-color="#DBEAFE"/>
                    </linearGradient>
                </defs>
                <g id="diceDots"></g>
            </svg>
        </div>
        <div class="text-center">
            <div id="diceResultLabel" class="fw-bold text-muted" style="font-size: 0.78rem;">ROLL TO SEE</div>
            <div id="diceResultNumber" class="display-4 fw-bold font-monospace text-dark">?</div>
        </div>
    </div>

    {{-- Bet Type Selector --}}
    <div class="mb-3">
        <small class="text-secondary fw-semibold d-block mb-2" style="font-size: 0.72rem;">CHOOSE YOUR BET TYPE</small>
        <div class="d-flex gap-2 flex-wrap justify-content-center">
            <button onclick="selectBetType('over')" id="btn-bet-over"
                class="btn btn-outline-success rounded-pill px-3 fw-bold active-bet-btn"
                style="font-size: 0.82rem;">
                <i class="bi bi-arrow-up-circle me-1"></i>OVER 3 &nbsp;<span class="badge bg-success ms-1">1.9x</span>
            </button>
            <button onclick="selectBetType('under')" id="btn-bet-under"
                class="btn btn-outline-danger rounded-pill px-3 fw-bold"
                style="font-size: 0.82rem;">
                <i class="bi bi-arrow-down-circle me-1"></i>UNDER 4 &nbsp;<span class="badge bg-danger ms-1">1.9x</span>
            </button>
            <button onclick="selectBetType('exact')" id="btn-bet-exact"
                class="btn btn-outline-warning rounded-pill px-3 fw-bold"
                style="font-size: 0.82rem;">
                <i class="bi bi-pin-angle me-1"></i>EXACT &nbsp;<span class="badge bg-warning text-dark ms-1">5.5x</span>
            </button>
        </div>
    </div>

    {{-- Exact Number Picker (shown only when exact selected) --}}
    <div id="exactNumberPicker" class="mb-3 d-none">
        <small class="text-secondary fw-semibold d-block mb-2" style="font-size: 0.72rem;">PICK YOUR NUMBER (1–6)</small>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            @for($n = 1; $n <= 6; $n++)
            <button onclick="selectExactNumber({{ $n }})" id="exact-num-{{ $n }}"
                class="btn btn-outline-secondary rounded-3 fw-bold fs-5"
                style="width: 48px; height: 48px; padding: 0;">{{ $n }}</button>
            @endfor
        </div>
    </div>

    {{-- Bet Amount --}}
    <div class="mb-3">
        <small class="text-secondary fw-semibold d-block mb-2" style="font-size: 0.72rem;">BET AMOUNT (₹)</small>
        <div class="d-flex gap-2 justify-content-center mb-2 flex-wrap">
            @foreach([10, 50, 100, 500, 1000] as $preset)
            <button onclick="setDiceBet({{ $preset }})"
                class="btn btn-outline-primary btn-sm rounded-pill px-3"
                style="font-size: 0.78rem;">₹{{ $preset }}</button>
            @endforeach
        </div>
        <div class="input-group mx-auto" style="max-width: 220px;">
            <span class="input-group-text bg-light fw-bold">₹</span>
            <input type="number" id="diceBetAmount" class="form-control text-center fw-bold fs-5"
                   value="50" min="{{ $game->min_entry_fee }}" max="{{ $game->max_entry_fee }}">
        </div>
    </div>

    {{-- Payout Preview --}}
    <div class="mb-3 p-2 rounded-3 border" style="background: #F9FAFB;">
        <div class="d-flex justify-content-between align-items-center px-1" style="font-size: 0.82rem;">
            <span class="text-muted">Bet:</span>
            <span id="previewBet" class="fw-bold text-dark">₹50.00</span>
        </div>
        <div class="d-flex justify-content-between align-items-center px-1 mt-1" style="font-size: 0.82rem;">
            <span class="text-muted">Max Win:</span>
            <span id="previewWin" class="fw-bold text-success">₹95.00</span>
        </div>
    </div>

    {{-- Roll Button --}}
    <button id="rollDiceBtn" onclick="rollDice()"
        class="btn gh-btn-primary w-100 py-3 fs-5 fw-bold rounded-3">
        <i class="bi bi-dice-5-fill me-2"></i>ROLL THE DICE
    </button>
</div>

{{-- My Dice History --}}
<div class="gh-card p-3">
    <h6 class="fw-bold text-dark mb-3" style="font-size: 0.9rem;">
        <i class="bi bi-clock-history me-2 text-primary"></i>My Dice History
    </h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle small text-center mb-0">
            <thead class="table-light">
                <tr>
                    <th>Time</th>
                    <th>Bet Type</th>
                    <th>Bet (₹)</th>
                    <th>Rolled</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody id="diceHistoryBody">
                @forelse($myBets as $bet)
                <tr>
                    <td class="text-muted">{{ $bet->created_at->format('H:i:s') }}</td>
                    <td><span class="badge bg-light text-dark border text-uppercase">{{ $bet->bet_type }}</span></td>
                    <td class="fw-bold">₹{{ number_format($bet->bet_amount, 2) }}</td>
                    <td class="fw-bold text-primary font-monospace">
                        {{ $bet->bet_details['rolled'] ?? '?' }}
                    </td>
                    <td>
                        @if($bet->status === 'won')
                            <span class="badge bg-success rounded-pill">+₹{{ number_format($bet->win_amount, 2) }}</span>
                        @elseif($bet->status === 'lost')
                            <span class="badge bg-danger rounded-pill">LOST</span>
                        @else
                            <span class="badge bg-warning text-dark rounded-pill">PENDING</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-muted py-3">No rolls yet. Place your first bet!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- WIN MODAL --}}
<div class="modal fade" id="diceWinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow overflow-hidden text-center">
            <div class="p-3 text-white" style="background: linear-gradient(135deg, #10B981, #059669);">
                <div class="fs-1 mb-1"><i class="bi bi-trophy-fill text-warning"></i></div>
                <h5 class="fw-bold mb-0">WINNER!</h5>
            </div>
            <div class="modal-body p-3">
                <p class="text-secondary small mb-2">
                    Rolled <span id="winModalRolled" class="fw-bold text-dark fs-5">6</span> — You won!
                </p>
                <div class="p-2 bg-light rounded-3 border mb-3">
                    <small class="text-secondary d-block">TOTAL WINNINGS</small>
                    <h3 id="winModalAmount" class="fw-bold text-success mb-0 font-monospace">+₹0.00</h3>
                </div>
                <button type="button" class="btn gh-btn-success w-100 py-2 rounded-pill" data-bs-dismiss="modal">
                    ROLL AGAIN
                </button>
            </div>
        </div>
    </div>
</div>

{{-- LOSS MODAL --}}
<div class="modal fade" id="diceLossModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow overflow-hidden text-center">
            <div class="p-3 text-white" style="background: linear-gradient(135deg, #EF4444, #DC2626);">
                <div class="fs-2 mb-1"><i class="bi bi-x-octagon-fill"></i></div>
                <h5 class="fw-bold mb-0">BETTER LUCK NEXT TIME!</h5>
            </div>
            <div class="modal-body p-3">
                <p class="text-secondary small mb-3">
                    Rolled <span id="lossModalRolled" class="fw-bold text-danger fs-5">1</span> — Not your number!
                </p>
                <button type="button" class="btn btn-outline-danger w-100 py-2 rounded-pill" data-bs-dismiss="modal">
                    TRY AGAIN
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.active-bet-btn { font-weight: 700 !important; }
.exact-num-active {
    background: #1E88E5 !important;
    color: #fff !important;
    border-color: #1E88E5 !important;
}

/* Dice face bounce animation */
@keyframes diceShake {
    0%   { transform: rotate(0deg) scale(1); }
    15%  { transform: rotate(-12deg) scale(1.1); }
    30%  { transform: rotate(10deg) scale(1.15); }
    45%  { transform: rotate(-8deg) scale(1.1); }
    60%  { transform: rotate(6deg) scale(1.05); }
    75%  { transform: rotate(-4deg) scale(1.02); }
    90%  { transform: rotate(2deg) scale(1); }
    100% { transform: rotate(0deg) scale(1); }
}

.dice-rolling {
    animation: diceShake 0.7s ease-in-out;
}
</style>

@push('scripts')
<script>
    const diceWinModal  = new bootstrap.Modal(document.getElementById('diceWinModal'));
    const diceLossModal = new bootstrap.Modal(document.getElementById('diceLossModal'));

    // ── State ────────────────────────────────────────────────────────────────
    let selectedBetType   = 'over';   // 'over' | 'under' | 'exact'
    let selectedExactNum  = 1;        // 1-6 (only used when betType === 'exact')
    const MULTIPLIERS = { over: 1.9, under: 1.9, exact: 5.5 };

    // ── Dot positions for each dice face ────────────────────────────────────
    const DOT_MAP = {
        1: [[50, 50]],
        2: [[25, 25], [75, 75]],
        3: [[25, 25], [50, 50], [75, 75]],
        4: [[25, 25], [75, 25], [25, 75], [75, 75]],
        5: [[25, 25], [75, 25], [50, 50], [25, 75], [75, 75]],
        6: [[25, 22], [75, 22], [25, 50], [75, 50], [25, 78], [75, 78]]
    };

    function renderDiceFace(n) {
        const dotsEl = document.getElementById('diceDots');
        dotsEl.innerHTML = '';
        (DOT_MAP[n] || []).forEach(([cx, cy]) => {
            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.setAttribute('cx', cx);
            circle.setAttribute('cy', cy);
            circle.setAttribute('r', 7);
            circle.setAttribute('fill', '#1E88E5');
            dotsEl.appendChild(circle);
        });
    }

    // Initial render
    renderDiceFace(1);

    // ── Bet type selection ──────────────────────────────────────────────────
    function selectBetType(type) {
        selectedBetType = type;

        ['over', 'under', 'exact'].forEach(t => {
            const btn = document.getElementById('btn-bet-' + t);
            btn.classList.remove('btn-success', 'btn-danger', 'btn-warning',
                                  'btn-outline-success', 'btn-outline-danger', 'btn-outline-warning');
            const colorMap = { over: 'outline-success', under: 'outline-danger', exact: 'outline-warning' };
            btn.classList.add('btn-' + colorMap[t]);
        });

        // Highlight active
        const activeMap = { over: ['outline-success', 'success'], under: ['outline-danger', 'danger'], exact: ['outline-warning', 'warning'] };
        const btn = document.getElementById('btn-bet-' + type);
        btn.classList.remove('btn-' + activeMap[type][0]);
        btn.classList.add('btn-' + activeMap[type][1]);

        document.getElementById('exactNumberPicker').classList.toggle('d-none', type !== 'exact');
        updatePayoutPreview();
    }

    function selectExactNumber(n) {
        selectedExactNum = n;
        for (let i = 1; i <= 6; i++) {
            const btn = document.getElementById('exact-num-' + i);
            btn.classList.toggle('exact-num-active', i === n);
        }
    }

    function setDiceBet(val) {
        document.getElementById('diceBetAmount').value = val;
        updatePayoutPreview();
    }

    function updatePayoutPreview() {
        const bet  = parseFloat(document.getElementById('diceBetAmount').value) || 0;
        const mult = MULTIPLIERS[selectedBetType] || 1.9;
        document.getElementById('previewBet').innerText  = '₹' + bet.toFixed(2);
        document.getElementById('previewWin').innerText  = '₹' + (bet * mult).toFixed(2);
    }

    document.getElementById('diceBetAmount').addEventListener('input', updatePayoutPreview);
    updatePayoutPreview();

    // ── Roll logic ───────────────────────────────────────────────────────────
    async function rollDice() {
        const btn    = document.getElementById('rollDiceBtn');
        const amount = parseFloat(document.getElementById('diceBetAmount').value);
        const minBet = {{ $game->min_entry_fee }};
        const maxBet = {{ $game->max_entry_fee }};

        if (isNaN(amount) || amount < minBet || amount > maxBet) {
            alert(`Bet must be between ₹${minBet} and ₹${maxBet}`);
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>ROLLING...';

        // Build bet_type string for server
        let betTypeStr = selectedBetType === 'exact'
            ? 'exact_' + selectedExactNum
            : selectedBetType;  // 'over' or 'under'

        const formData = new FormData();
        formData.append('game_id',      "{{ $game->id }}");
        formData.append('period_number', 'DICE_' + Date.now());
        formData.append('bet_amount',    amount);
        formData.append('bet_type',      betTypeStr);

        try {
            // 1. Place bet (deduct balance)
            const res  = await fetch("{{ route('games.bet') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json();

            if (!data.success) {
                alert(data.message || 'Failed to place bet');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-dice-5-fill me-2"></i>ROLL THE DICE';
                return;
            }

            updateTopWalletBalance(data.new_balance);

            // 2. Animate rolling
            const diceEl    = document.getElementById('diceDisplay');
            const resultNum = document.getElementById('diceResultNumber');
            const resultLbl = document.getElementById('diceResultLabel');

            diceEl.classList.add('dice-rolling');
            resultNum.innerText = '?';
            resultLbl.innerText = 'ROLLING...';

            // Flash random faces during animation
            let flashCount = 0;
            const flashInterval = setInterval(() => {
                const rnd = Math.ceil(Math.random() * 6);
                renderDiceFace(rnd);
                flashCount++;
                if (flashCount >= 12) clearInterval(flashInterval);
            }, 60);

            // 3. After 800ms animation, settle with server
            await new Promise(r => setTimeout(r, 800));
            diceEl.classList.remove('dice-rolling');

            const settleForm = new FormData();
            settleForm.append('bet_id', data.bet.id);

            const settleRes  = await fetch("{{ route('games.dice.settle') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                body: settleForm
            });
            const settleData = await settleRes.json();

            if (!settleData.success) {
                alert(settleData.message || 'Settlement error');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-dice-5-fill me-2"></i>ROLL THE DICE';
                return;
            }

            // 4. Show final result
            const rolled = settleData.rolled;
            clearInterval(flashInterval);
            renderDiceFace(rolled);
            resultNum.innerText = rolled;

            const won = settleData.status === 'won';
            resultLbl.innerText = won ? '🎉 YOU WIN!' : '❌ YOU LOST';
            resultLbl.className = 'fw-bold ' + (won ? 'text-success' : 'text-danger');

            updateTopWalletBalance(settleData.new_balance);

            // 5. Add to history table instantly
            prependHistoryRow(betTypeStr, amount, rolled, settleData);

            // 6. Show modal
            if (won) {
                document.getElementById('winModalRolled').innerText  = rolled;
                document.getElementById('winModalAmount').innerText  = '+₹' + settleData.win_amount;
                diceWinModal.show();
            } else {
                document.getElementById('lossModalRolled').innerText = rolled;
                diceLossModal.show();
            }

        } catch (err) {
            console.error(err);
            alert('Network error. Please try again.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-dice-5-fill me-2"></i>ROLL THE DICE';
        }
    }

    function prependHistoryRow(betType, amount, rolled, settle) {
        const tbody = document.getElementById('diceHistoryBody');
        const now   = new Date().toLocaleTimeString('en-GB', { hour12: false });
        const won   = settle.status === 'won';

        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="text-muted">${now}</td>
            <td><span class="badge bg-light text-dark border text-uppercase">${betType}</span></td>
            <td class="fw-bold">₹${parseFloat(amount).toFixed(2)}</td>
            <td class="fw-bold text-primary font-monospace">${rolled}</td>
            <td>${won
                ? `<span class="badge bg-success rounded-pill">+₹${settle.win_amount}</span>`
                : `<span class="badge bg-danger rounded-pill">LOST</span>`
            }</td>`;

        // Remove "no bets yet" row if present
        const empty = tbody.querySelector('td[colspan]');
        if (empty) empty.closest('tr').remove();

        tbody.insertBefore(row, tbody.firstChild);
    }

    function updateTopWalletBalance(balStr) {
        // Primary: target by ID added to the layout header
        const topEl = document.getElementById('topWalletBalance');
        if (topEl) {
            topEl.innerText = '₹' + balStr;
        }
        // Fallback: any .font-monospace element showing a ₹ balance
        document.querySelectorAll('.font-monospace').forEach(el => {
            if (el !== topEl && el.innerText.includes('₹') && /₹[\d,]+/.test(el.innerText)) {
                el.innerText = '₹' + balStr;
            }
        });
    }
</script>
@endpush
@endsection
