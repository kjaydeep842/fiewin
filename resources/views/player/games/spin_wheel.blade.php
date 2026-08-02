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
            ['label' => '2X',  'color' => '#6366f1', 'mult' => 2],
            ['label' => '5X',  'color' => '#22C55E', 'mult' => 5],
            ['label' => '10X', 'color' => '#EAB308', 'mult' => 10],
            ['label' => '0X',  'color' => '#EF4444', 'mult' => 0],
            ['label' => '3X',  'color' => '#D946EF', 'mult' => 3],
            ['label' => '50X', 'color' => '#06B6D4', 'mult' => 50],
        ];
        @endphp
        @foreach($sectors as $s)
        <span class="badge rounded-pill px-2 py-1" style="background:{{ $s['color'] }}; font-size:0.7rem;">{{ $s['label'] }}</span>
        @endforeach
    </div>

    {{-- Wheel Canvas + Pointer --}}
    <div class="position-relative d-inline-block mb-3" style="width:280px; max-width:100%;">
        {{-- Pointer arrow at TOP center --}}
        <div class="position-absolute text-warning"
             style="top:-10px; left:50%; transform:translateX(-50%); font-size:1.8rem; z-index:5; filter:drop-shadow(0 2px 6px rgba(0,0,0,0.4)); line-height:1;">
            ▼
        </div>
        <canvas id="wheelCanvas" width="280" height="280"
                style="border-radius:50%; border:4px solid #EAB308; display:block; max-width:100%; height:auto; margin:0 auto; box-shadow:0 8px 32px rgba(0,0,0,0.18);"></canvas>
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
            <button onclick="document.getElementById('spinBetAmount').value={{ $p }}; updatePreview();"
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

    // ──────────────────────────────────────────────────────────────────────────
    // SECTOR DEFINITIONS — order matches drawing order (clockwise from top)
    // Pointer is at TOP of the canvas = angle -π/2 (270°)
    // ──────────────────────────────────────────────────────────────────────────
    const SECTORS = [
        { label: '2X',  color: '#6366f1', mult: 2  },
        { label: '5X',  color: '#22C55E', mult: 5  },
        { label: '10X', color: '#EAB308', mult: 10 },
        { label: '0X',  color: '#EF4444', mult: 0  },
        { label: '3X',  color: '#D946EF', mult: 3  },
        { label: '50X', color: '#06B6D4', mult: 50 },
    ];
    const NUM_SECTORS  = SECTORS.length;
    const ARC          = (2 * Math.PI) / NUM_SECTORS;  // 60° per sector

    // Canvas setup
    const canvas   = document.getElementById('wheelCanvas');
    const ctx      = canvas.getContext('2d');
    const CX       = canvas.width  / 2;
    const CY       = canvas.height / 2;
    const RADIUS   = CX - 6;

    // The "zero" rotation offset so sector 0 starts right under pointer (top).
    // Pointer is at -π/2. Sector 0 centre should be at -π/2.
    // Sector i occupies [rotOffset + i*ARC, rotOffset + (i+1)*ARC].
    // Centre of sector i = rotOffset + i*ARC + ARC/2.
    // For sector 0 centre to be at -π/2: rotOffset + ARC/2 = -π/2
    //   => rotOffset = -π/2 - ARC/2
    const POINTER_ANGLE = -Math.PI / 2;  // top of canvas

    let   rotationAngle = 0;  // current wheel rotation (accumulated)
    let   activeBetId   = null;
    let   isSpinning    = false;

    // ──────────────────────────────────────────────────────────────────────────
    // drawWheel: draws the wheel rotated by `rotation` radians
    // ──────────────────────────────────────────────────────────────────────────
    function drawWheel(rotation) {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        SECTORS.forEach((s, i) => {
            // Each sector starts at POINTER_ANGLE - ARC/2 + i*ARC + rotation
            // This puts sector 0 centre exactly at pointer when rotation=0
            const startAngle = POINTER_ANGLE - ARC / 2 + i * ARC + rotation;
            const endAngle   = startAngle + ARC;

            // Sector fill
            ctx.beginPath();
            ctx.moveTo(CX, CY);
            ctx.arc(CX, CY, RADIUS, startAngle, endAngle);
            ctx.closePath();
            ctx.fillStyle = s.color;
            ctx.fill();

            // White border
            ctx.strokeStyle = 'rgba(255,255,255,0.6)';
            ctx.lineWidth   = 2.5;
            ctx.stroke();

            // Label text (positioned at sector midpoint)
            const midAngle = startAngle + ARC / 2;
            const textR    = RADIUS * 0.68;
            const tx       = CX + textR * Math.cos(midAngle);
            const ty       = CY + textR * Math.sin(midAngle);

            ctx.save();
            ctx.translate(tx, ty);
            ctx.rotate(midAngle + Math.PI / 2);
            ctx.textAlign    = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle    = '#fff';
            ctx.font         = 'bold 16px system-ui, sans-serif';
            ctx.shadowColor  = 'rgba(0,0,0,0.45)';
            ctx.shadowBlur   = 5;
            ctx.fillText(s.label, 0, 0);
            ctx.restore();
        });

        // Centre circle
        ctx.beginPath();
        ctx.arc(CX, CY, 16, 0, 2 * Math.PI);
        ctx.fillStyle   = '#fff';
        ctx.shadowColor = 'rgba(0,0,0,0.25)';
        ctx.shadowBlur  = 10;
        ctx.fill();
        ctx.shadowBlur  = 0;
        ctx.strokeStyle = '#EAB308';
        ctx.lineWidth   = 3;
        ctx.stroke();
    }

    // Initial draw
    drawWheel(rotationAngle);

    // ──────────────────────────────────────────────────────────────────────────
    // computeTargetRotation:
    //   Given the winning sector index, compute the exact final rotation so
    //   the pointer (at POINTER_ANGLE = -π/2) lands exactly on the sector centre.
    //
    //   Sector i centre at rotation R = POINTER_ANGLE - ARC/2 + i*ARC + R + ARC/2
    //                                 = POINTER_ANGLE + i*ARC + R
    //   We want sector centre == POINTER_ANGLE:
    //     POINTER_ANGLE + i*ARC + R = POINTER_ANGLE  (mod 2π)
    //     R = -i*ARC  (mod 2π)
    //
    //   We add full 360° spins for drama, and a small random offset within
    //   the sector so it doesn't always land perfectly centred (feels real).
    // ──────────────────────────────────────────────────────────────────────────
    function computeTargetRotation(winnerIdx) {
        const jitter         = (Math.random() - 0.5) * ARC * 0.35; // ±17.5% of sector width
        const sectorRotation = -winnerIdx * ARC + jitter;           // raw rotation to land on sector
        const fullSpins      = (5 + Math.floor(Math.random() * 4)) * 2 * Math.PI; // 5-8 full spins

        // Normalise current angle to [0, 2π)
        const curNorm = ((rotationAngle % (2 * Math.PI)) + 2 * Math.PI) % (2 * Math.PI);

        // Target must be > current (spin forward), normalised to same base
        const rawTarget = ((sectorRotation % (2 * Math.PI)) + 2 * Math.PI) % (2 * Math.PI);
        const diff      = (rawTarget - curNorm + 2 * Math.PI) % (2 * Math.PI);

        return rotationAngle + fullSpins + diff;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // getSectorAtPointer: read which sector is under the pointer at given rotation
    // ──────────────────────────────────────────────────────────────────────────
    function getSectorAtPointer(rotation) {
        // Pointer is at POINTER_ANGLE (-π/2).
        // Sector i start angle = POINTER_ANGLE - ARC/2 + i*ARC + rotation
        // Find which sector contains POINTER_ANGLE:
        //   POINTER_ANGLE - ARC/2 + i*ARC + rotation ≤ POINTER_ANGLE < POINTER_ANGLE - ARC/2 + (i+1)*ARC + rotation
        //   Simplifying: -ARC/2 + i*ARC + rotation ≤ 0 < ARC/2 + i*ARC + rotation
        //
        // Easier: offset = (POINTER_ANGLE - (POINTER_ANGLE - ARC/2 + rotation)) / ARC
        //                = (ARC/2 - rotation) / ARC
        const offset = ((ARC / 2 - rotation) % (2 * Math.PI) + 2 * Math.PI) % (2 * Math.PI);
        const idx    = Math.floor(offset / ARC) % NUM_SECTORS;
        return idx;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // updatePreview
    // ──────────────────────────────────────────────────────────────────────────
    function updatePreview() {
        const bet = parseFloat(document.getElementById('spinBetAmount').value) || 0;
        document.getElementById('previewBet').innerText = '₹' + bet.toFixed(2);
        document.getElementById('previewMax').innerText = '₹' + (bet * 50).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    document.getElementById('spinBetAmount').addEventListener('input', updatePreview);
    updatePreview();

    // ──────────────────────────────────────────────────────────────────────────
    // MAIN SPIN
    // ──────────────────────────────────────────────────────────────────────────
    async function spinWheel() {
        if (isSpinning) return;

        const betAmount = parseFloat(document.getElementById('spinBetAmount').value);
        const minBet    = {{ $game->min_entry_fee }};
        const maxBet    = {{ $game->max_entry_fee }};

        if (isNaN(betAmount) || betAmount < minBet || betAmount > maxBet) {
            alert(`Bet must be between ₹${minBet} and ₹${maxBet}`);
            return;
        }

        const btn    = document.getElementById('spinBtn');
        isSpinning   = true;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>SPINNING...';

        // Hide old result
        document.getElementById('spinResultBanner').classList.add('d-none');

        // Step 1: Pick winner sector HERE on frontend deterministically
        //         (server will validate/override the multiplier server-side)
        const winnerIdx    = Math.floor(Math.random() * NUM_SECTORS);
        const winnerSector = SECTORS[winnerIdx];

        // Step 2: Place bet
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

            // Step 3: Animate wheel to winner sector
            const targetRotation = computeTargetRotation(winnerIdx);
            const startRotation  = rotationAngle;
            const startTime      = performance.now();
            const DURATION       = 3500; // ms

            function easeOut(t) {
                // Cubic ease-out for smooth deceleration
                return 1 - Math.pow(1 - t, 3);
            }

            let lastTickAngle = startRotation;

            function animate(now) {
                const elapsed  = now - startTime;
                const progress = Math.min(elapsed / DURATION, 1);
                const eased    = easeOut(progress);
                const angle    = startRotation + (targetRotation - startRotation) * eased;

                drawWheel(angle);

                // Tick sound
                if (Math.abs(angle - lastTickAngle) > 0.3) {
                    if (window.soundManager) window.soundManager.play('wheelTick');
                    lastTickAngle = angle;
                }

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    // Snap to exact final angle
                    rotationAngle = targetRotation;
                    drawWheel(rotationAngle);

                    // Verify which sector is actually under pointer
                    const actualIdx    = getSectorAtPointer(rotationAngle);
                    const actualSector = SECTORS[actualIdx];

                    // Step 4: Settle with the ACTUAL sector under pointer
                    settleResult(actualSector, btn);
                }
            }

            requestAnimationFrame(animate);

        } catch (err) {
            console.error(err);
            alert('Network error. Please try again.');
            resetBtn(btn);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SETTLE — sends result to server
    // ──────────────────────────────────────────────────────────────────────────
    async function settleResult(sector, btn) {
        const formData = new FormData();
        formData.append('bet_id',     activeBetId);
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

                // Show result banner
                const banner  = document.getElementById('spinResultBanner');
                const bannerT = document.getElementById('spinResultText');
                const won     = sector.mult > 0;

                banner.classList.remove('d-none');
                bannerT.className = 'badge fs-6 px-4 py-2 rounded-pill ' +
                    (won ? 'bg-success' : 'bg-danger');
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

                // Prepend history row
                prependHistoryRow(sector, data);

                // Show correct modal
                if (won) {
                    document.getElementById('spinWinMult').innerText   = sector.label;
                    document.getElementById('spinWinAmount').innerText = '+₹' + data.win_amount;
                    spinWinModal.show();
                } else {
                    spinLossModal.show();
                }
            } else {
                alert(data.message || 'Settlement error.');
            }
        } catch (err) {
            console.error('Settle error:', err);
            alert('Settlement network error. Please contact support.');
        } finally {
            resetBtn(btn);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    function prependHistoryRow(sector, settle) {
        const tbody = document.getElementById('spinHistoryBody');
        const now   = new Date().toLocaleTimeString('en-GB', { hour12: false });
        const bet   = parseFloat(document.getElementById('spinBetAmount').value);
        const won   = sector.mult > 0;

        const bgMap = { 50: '#06B6D4', 10: '#EAB308', 5: '#22C55E', 3: '#D946EF', 2: '#6366f1', 0: '#EF4444' };
        const color = bgMap[sector.mult] ?? '#6c757d';

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
    }
</script>
@endpush
@endsection
