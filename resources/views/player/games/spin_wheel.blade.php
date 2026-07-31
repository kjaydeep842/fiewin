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
                <i class="bi bi-arrow-repeat text-info me-1"></i>{{ $game->name }}
            </h6>
            <small class="text-muted" style="font-size: 0.72rem;">Spin the lucky wheel and hit up to 50x multipliers!</small>
        </div>
    </div>
</div>

{{-- Wheel + Controls Card --}}
<div class="gh-card p-4 mb-3 text-center">

    {{-- Multiplier Sectors Info --}}
    <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
        @php
        $sectors = [
            ['label' => '2X',  'color' => '#6366f1'],
            ['label' => '5X',  'color' => '#22C55E'],
            ['label' => '10X', 'color' => '#EAB308'],
            ['label' => '0X',  'color' => '#EF4444'],
            ['label' => '3X',  'color' => '#D946EF'],
            ['label' => '50X', 'color' => '#06B6D4'],
        ];
        @endphp
        @foreach($sectors as $s)
        <span class="badge rounded-pill px-2 py-1" style="background:{{ $s['color'] }}; font-size:0.7rem;">{{ $s['label'] }}</span>
        @endforeach
    </div>

    {{-- Wheel Canvas --}}
    <div class="position-relative d-inline-block mb-3">
        {{-- Pointer --}}
        <div class="position-absolute top-0 start-50 translate-middle-x text-warning"
             style="font-size: 1.6rem; margin-top: -12px; z-index: 3; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.25));">
            <i class="bi bi-caret-down-fill"></i>
        </div>
        <canvas id="wheelCanvas" width="280" height="280"
                class="rounded-circle shadow"
                style="border: 4px solid #EAB308; display: block; max-width: 100%; height: auto; margin: 0 auto;"></canvas>
    </div>

    {{-- Result display --}}
    <div id="spinResultBanner" class="mb-3 d-none">
        <span id="spinResultText" class="badge fs-6 px-4 py-2 rounded-pill"></span>
    </div>

    {{-- Bet Amount --}}
    <div class="mb-3">
        <small class="text-secondary fw-semibold d-block mb-2" style="font-size: 0.72rem;">BET AMOUNT (₹)</small>
        <div class="d-flex gap-2 justify-content-center flex-wrap mb-2">
            @foreach([10, 50, 100, 500, 1000] as $p)
            <button onclick="document.getElementById('spinBetAmount').value={{ $p }}"
                    class="btn btn-outline-primary btn-sm rounded-pill px-3"
                    style="font-size: 0.78rem;">₹{{ $p }}</button>
            @endforeach
        </div>
        <div class="input-group mx-auto" style="max-width: 200px;">
            <span class="input-group-text bg-light fw-bold">₹</span>
            <input type="number" id="spinBetAmount"
                   class="form-control text-center fw-bold fs-5"
                   value="50"
                   min="{{ $game->min_entry_fee }}"
                   max="{{ $game->max_entry_fee }}">
        </div>
    </div>

    {{-- Payout preview --}}
    <div class="mx-auto mb-3 p-2 rounded-3 border" style="max-width: 260px; background:#F9FAFB; font-size:0.82rem;">
        <div class="d-flex justify-content-between px-1">
            <span class="text-muted">Your bet:</span>
            <span id="previewBet" class="fw-bold">₹50.00</span>
        </div>
        <div class="d-flex justify-content-between px-1 mt-1">
            <span class="text-muted">Max win (50x):</span>
            <span id="previewMax" class="fw-bold text-success">₹2,500.00</span>
        </div>
    </div>

    {{-- Spin Button --}}
    <button id="spinBtn" onclick="spinWheel()"
            class="btn gh-btn-primary w-100 py-3 fs-5 fw-bold rounded-3">
        <i class="bi bi-arrow-repeat me-2"></i>SPIN NOW
    </button>
</div>

{{-- My Spin History --}}
<div class="gh-card p-3">
    <h6 class="fw-bold text-dark mb-3" style="font-size: 0.9rem;">
        <i class="bi bi-clock-history me-2 text-primary"></i>My Spin History
    </h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle small text-center mb-0">
            <thead class="table-light">
                <tr>
                    <th>Time</th>
                    <th>Bet (₹)</th>
                    <th>Multiplier</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody id="spinHistoryBody">
                @forelse($myBets as $bet)
                <tr>
                    <td class="text-muted">{{ $bet->created_at->format('H:i:s') }}</td>
                    <td class="fw-bold">₹{{ number_format($bet->bet_amount, 2) }}</td>
                    <td>
                        <span class="badge rounded-pill"
                              style="background:{{ $bet->multiplier >= 10 ? '#EAB308' : ($bet->multiplier >= 3 ? '#6366f1' : ($bet->multiplier > 0 ? '#22C55E' : '#EF4444')) }}">
                            {{ $bet->multiplier > 0 ? $bet->multiplier . 'X' : '0X' }}
                        </span>
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
                <tr><td colspan="4" class="text-muted py-3">No spins yet. Spin to win!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- WIN MODAL --}}
<div class="modal fade" id="spinWinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow overflow-hidden text-center">
            <div class="p-3 text-white" style="background: linear-gradient(135deg, #10B981, #059669);">
                <div class="fs-1 mb-1"><i class="bi bi-trophy-fill text-warning"></i></div>
                <h5 class="fw-bold mb-0">CONGRATULATIONS!</h5>
            </div>
            <div class="modal-body p-3">
                <p class="text-secondary small mb-2">
                    You landed on <span id="spinWinMult" class="fw-bold text-dark fs-5">2X</span> multiplier!
                </p>
                <div class="p-2 bg-light rounded-3 border mb-3">
                    <small class="text-secondary d-block">TOTAL WINNINGS</small>
                    <h3 id="spinWinAmount" class="fw-bold text-success mb-0 font-monospace">+₹0.00</h3>
                </div>
                <button type="button" class="btn gh-btn-success w-100 py-2 rounded-pill" data-bs-dismiss="modal">
                    SPIN AGAIN
                </button>
            </div>
        </div>
    </div>
</div>

{{-- LOSS MODAL --}}
<div class="modal fade" id="spinLossModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow overflow-hidden text-center">
            <div class="p-3 text-white" style="background: linear-gradient(135deg, #EF4444, #DC2626);">
                <div class="fs-2 mb-1"><i class="bi bi-emoji-frown-fill"></i></div>
                <h5 class="fw-bold mb-0">0X — UNLUCKY!</h5>
            </div>
            <div class="modal-body p-3">
                <p class="text-secondary small mb-3">You hit 0X multiplier. Spin again for big wins!</p>
                <button type="button" class="btn btn-outline-danger w-100 py-2 rounded-pill" data-bs-dismiss="modal">
                    TRY AGAIN
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const spinWinModal  = new bootstrap.Modal(document.getElementById('spinWinModal'));
    const spinLossModal = new bootstrap.Modal(document.getElementById('spinLossModal'));

    // ── Wheel Config ──────────────────────────────────────────────────────────
    const sectors = [
        { label: '2X',  color: '#6366f1', mult: 2  },
        { label: '5X',  color: '#22C55E', mult: 5  },
        { label: '10X', color: '#EAB308', mult: 10 },
        { label: '0X',  color: '#EF4444', mult: 0  },
        { label: '3X',  color: '#D946EF', mult: 3  },
        { label: '50X', color: '#06B6D4', mult: 50 },
    ];

    const canvas     = document.getElementById('wheelCanvas');
    const ctx        = canvas.getContext('2d');
    const CX         = canvas.width  / 2;
    const CY         = canvas.height / 2;
    const RADIUS     = CX - 6;
    let   currentAngle = 0;
    let   activeBetId  = null;
    let   isSpinning   = false;

    function drawWheel(angle) {
        const arc = (2 * Math.PI) / sectors.length;
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        sectors.forEach((s, i) => {
            const start = angle + i * arc;
            const end   = start + arc;

            // Sector fill
            ctx.beginPath();
            ctx.moveTo(CX, CY);
            ctx.arc(CX, CY, RADIUS, start, end);
            ctx.closePath();
            ctx.fillStyle = s.color;
            ctx.fill();

            // Sector border
            ctx.strokeStyle = 'rgba(255,255,255,0.5)';
            ctx.lineWidth   = 2;
            ctx.stroke();

            // Label
            ctx.save();
            ctx.translate(CX, CY);
            ctx.rotate(start + arc / 2);
            ctx.textAlign    = 'right';
            ctx.fillStyle    = '#fff';
            ctx.font         = 'bold 17px system-ui, sans-serif';
            ctx.shadowColor  = 'rgba(0,0,0,0.35)';
            ctx.shadowBlur   = 4;
            ctx.fillText(s.label, RADIUS - 10, 6);
            ctx.restore();
        });

        // Centre circle
        ctx.beginPath();
        ctx.arc(CX, CY, 18, 0, 2 * Math.PI);
        ctx.fillStyle   = '#fff';
        ctx.shadowColor = 'rgba(0,0,0,0.2)';
        ctx.shadowBlur  = 8;
        ctx.fill();
        ctx.shadowBlur  = 0;
    }

    drawWheel(currentAngle);

    // Live payout preview
    function updatePreview() {
        const bet = parseFloat(document.getElementById('spinBetAmount').value) || 0;
        document.getElementById('previewBet').innerText = '₹' + bet.toFixed(2);
        document.getElementById('previewMax').innerText = '₹' + (bet * 50).toFixed(2);
    }
    document.getElementById('spinBetAmount').addEventListener('input', updatePreview);
    updatePreview();

    // ── Spin ─────────────────────────────────────────────────────────────────
    async function spinWheel() {
        if (isSpinning) return;

        const betAmount = parseFloat(document.getElementById('spinBetAmount').value);
        const minBet    = {{ $game->min_entry_fee }};
        const maxBet    = {{ $game->max_entry_fee }};

        if (isNaN(betAmount) || betAmount < minBet || betAmount > maxBet) {
            alert(`Bet must be between ₹${minBet} and ₹${maxBet}`);
            return;
        }

        const btn = document.getElementById('spinBtn');
        isSpinning    = true;
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>SPINNING...';

        // Reset result banner
        const banner = document.getElementById('spinResultBanner');
        banner.classList.add('d-none');

        // Place bet (deduct balance)
        const formData = new FormData();
        formData.append('game_id',      "{{ $game->id }}");
        formData.append('period_number', 'SPIN_' + Date.now());
        formData.append('bet_amount',    betAmount);
        formData.append('bet_type',      'spin_wheel');

        try {
            const res  = await fetch("{{ route('games.bet') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json();

            if (!data.success) {
                alert(data.message || 'Error placing bet');
                resetBtn(btn);
                return;
            }

            activeBetId = data.bet.id;
            updateTopWalletBalance(data.new_balance);

            // Pick a sector randomly
            const sectorIdx      = Math.floor(Math.random() * sectors.length);
            const selectedSector = sectors[sectorIdx];

            // Animate wheel
            const totalSpins  = 5 + Math.random() * 3;
            const arcPerSector = (2 * Math.PI) / sectors.length;
            const targetOffset = -(sectorIdx + 0.5) * arcPerSector; // land pointer on sector centre
            const targetAngle  = currentAngle + totalSpins * 2 * Math.PI + targetOffset;
            const startTime    = performance.now();
            const DURATION     = 3200; // ms

            function easeOut(t) { return 1 - Math.pow(1 - t, 4); }

            let lastTickAngle = currentAngle;
            function animate(now) {
                const elapsed  = now - startTime;
                const progress = Math.min(elapsed / DURATION, 1);
                const angle    = currentAngle + (targetAngle - currentAngle) * easeOut(progress);
                drawWheel(angle);

                if (Math.abs(angle - lastTickAngle) > 0.35) {
                    if (window.soundManager) window.soundManager.play('wheelTick');
                    lastTickAngle = angle;
                }

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    currentAngle = ((targetAngle % (2 * Math.PI)) + 2 * Math.PI) % (2 * Math.PI);
                    drawWheel(currentAngle);
                    settleResult(selectedSector, btn);
                }
            }

            requestAnimationFrame(animate);

        } catch (err) {
            console.error(err);
            alert('Network error. Please try again.');
            resetBtn(btn);
        }
    }

    // ── Settle ────────────────────────────────────────────────────────────────
    async function settleResult(sector, btn) {
        const formData = new FormData();
        formData.append('bet_id',    activeBetId);
        formData.append('multiplier', sector.mult);

        try {
            const res  = await fetch("{{ route('games.spin.settle') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                updateTopWalletBalance(data.new_balance);

                // Show result banner on wheel card
                const banner  = document.getElementById('spinResultBanner');
                const bannerT = document.getElementById('spinResultText');
                const won = sector.mult > 0;

                banner.classList.remove('d-none');
                bannerT.className = 'badge fs-6 px-4 py-2 rounded-pill ' + (won ? 'bg-success gh-glow-success' : 'bg-danger gh-red-alert-pulse');
                bannerT.innerText = won
                    ? `🎉 ${sector.label} — +₹${data.win_amount}`
                    : `💥 ${sector.label} — Better Luck Next Time!`;

                if (won) {
                    if (window.soundManager) window.soundManager.play('win');
                    if (window.animationManager) {
                        window.animationManager.triggerConfetti(60);
                        window.animationManager.animateCoinsToWallet(banner);
                    }
                } else {
                    if (window.soundManager) window.soundManager.play('lose');
                    if (window.animationManager) window.animationManager.shakeScreen();
                }

                // Prepend to history table
                prependHistoryRow(sector, data);

                // Show modal
                if (won) {
                    document.getElementById('spinWinMult').innerText   = sector.label;
                    document.getElementById('spinWinAmount').innerText = '+₹' + data.win_amount;
                    spinWinModal.show();
                } else {
                    spinLossModal.show();
                }
            }
        } catch (err) {
            console.error(err);
        } finally {
            resetBtn(btn);
        }
    }

    function prependHistoryRow(sector, settle) {
        const tbody = document.getElementById('spinHistoryBody');
        const now   = new Date().toLocaleTimeString('en-GB', { hour12: false });
        const bet   = parseFloat(document.getElementById('spinBetAmount').value);
        const won   = sector.mult > 0;

        // Determine badge colour
        const bgMap = { 50: '#06B6D4', 10: '#EAB308', 5: '#22C55E', 3: '#D946EF', 2: '#6366f1', 0: '#EF4444' };
        const color = bgMap[sector.mult] || '#6c757d';

        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="text-muted">${now}</td>
            <td class="fw-bold">₹${bet.toFixed(2)}</td>
            <td><span class="badge rounded-pill" style="background:${color}">${sector.label}</span></td>
            <td>${won
                ? `<span class="badge bg-success rounded-pill">+₹${settle.win_amount}</span>`
                : `<span class="badge bg-danger rounded-pill">LOST</span>`
            }</td>`;

        const empty = tbody.querySelector('td[colspan]');
        if (empty) empty.closest('tr').remove();
        tbody.insertBefore(row, tbody.firstChild);
    }

    function resetBtn(btn) {
        isSpinning    = false;
        btn.disabled  = false;
        btn.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i>SPIN NOW';
    }

    function updateTopWalletBalance(balStr) {
        const topEl = document.getElementById('topWalletBalance');
        if (topEl) topEl.innerText = '₹' + balStr;
        document.querySelectorAll('.font-monospace').forEach(el => {
            if (el !== topEl && el.innerText.includes('₹') && /₹[\d,]+/.test(el.innerText)) {
                el.innerText = '₹' + balStr;
            }
        });
    }
</script>
@endpush
@endsection
