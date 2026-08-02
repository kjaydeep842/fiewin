@extends('layouts.app')

@section('content')
<style>
    /* CSS Animations for Mines Arena */
    @keyframes tileFlipIn {
        0% { transform: rotateY(0deg) scale(1); }
        50% { transform: rotateY(90deg) scale(0.9); }
        100% { transform: rotateY(0deg) scale(1); }
    }

    @keyframes gemGlowPulse {
        0% { box-shadow: 0 0 5px rgba(16, 185, 129, 0.4); transform: scale(1); }
        50% { box-shadow: 0 0 20px rgba(16, 185, 129, 0.9); transform: scale(1.05); }
        100% { box-shadow: 0 0 5px rgba(16, 185, 129, 0.4); transform: scale(1); }
    }

    @keyframes mineExplode {
        0% { transform: scale(1); box-shadow: 0 0 0 rgba(239, 68, 68, 0.8); }
        50% { transform: scale(1.15); box-shadow: 0 0 25px rgba(239, 68, 68, 1); }
        100% { transform: scale(1); box-shadow: 0 0 5px rgba(239, 68, 68, 0.5); }
    }

    .tile-flip {
        animation: tileFlipIn 0.35s ease-in-out;
    }

    .gem-glow {
        animation: gemGlowPulse 1.2s infinite ease-in-out;
        background: linear-gradient(135deg, #10B981, #059669) !important;
        border-color: #047857 !important;
        color: #FFFFFF !important;
    }

    .mine-explode {
        animation: mineExplode 0.5s ease-in-out;
        background: linear-gradient(135deg, #EF4444, #DC2626) !important;
        border-color: #B91C1C !important;
        color: #FFFFFF !important;
    }

    .mine-tile {
        transition: all 0.2s ease;
        aspect-ratio: 1 / 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
        width: 100%;
    }

    .mine-tile:not(:disabled):hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }
</style>

<div class="gh-card p-3 mb-3 text-center">
    <h5 class="fw-bold text-dark mb-1"><i class="bi bi-pin-angle-fill text-warning me-2"></i>Mines Arena</h5>
    <p class="text-secondary small mb-0">Select mine count, place bet, and click tiles to reveal gems!</p>
</div>

<!-- Controls & Multiplier Header -->
<div class="row g-2 mb-3 align-items-center">
    <div class="col-6">
        <div class="gh-card p-3 text-center">
            <small class="text-secondary d-block mb-1" style="font-size: 0.68rem;">CURRENT MULTIPLIER</small>
            <span id="currentMultiplierDisplay" class="fw-bold fs-4 text-success font-monospace">1.00x</span>
        </div>
    </div>
    <div class="col-6">
        <div class="gh-card p-3 text-center">
            <small class="text-secondary d-block mb-1" style="font-size: 0.68rem;">POTENTIAL PROFIT</small>
            <span id="currentProfitDisplay" class="fw-bold fs-4 text-primary font-monospace">₹0.00</span>
        </div>
    </div>
</div>

<!-- 5x5 Mines Grid -->
<div class="gh-card p-3 mb-3">
    <div class="row g-2 justify-content-center" id="minesGrid">
        <!-- Rendered dynamically by MinesGameEngine -->
    </div>
</div>

<!-- Bet Controls & Cash Out -->
<div class="gh-card p-3">
    <div class="row g-2 mb-3">
        <div class="col-6">
            <label class="form-label text-secondary small fw-semibold">BET AMOUNT (₹)</label>
            <input type="number" id="minesBetAmount" class="form-control fw-bold" value="10" min="{{ $game->min_entry_fee }}" max="{{ $game->max_entry_fee }}">
        </div>
        <div class="col-6">
            <label class="form-label text-secondary small fw-semibold">MINES COUNT</label>
            <select id="minesCount" class="form-select fw-bold">
                <option value="1">1 Mine (Low Risk)</option>
                <option value="3" selected>3 Mines (Medium Risk)</option>
                <option value="5">5 Mines (High Risk)</option>
                <option value="10">10 Mines (Extreme Risk)</option>
                <option value="15">15 Mines (Insane Risk)</option>
                <option value="20">20 Mines (God Mode)</option>
            </select>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-6">
            <button class="btn gh-btn-primary w-100 py-2 fs-6 fw-bold rounded-pill" id="startMinesBtn">START GAME</button>
        </div>
        <div class="col-6">
            <button class="btn gh-btn-success w-100 py-2 fs-6 fw-bold rounded-pill" id="cashoutBtn" disabled>CASH OUT</button>
        </div>
    </div>
</div>

<!-- MY GAME HISTORY CARD -->
<div class="gh-card p-3 mt-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>My Mines History</h6>
        <button class="btn btn-sm btn-light border rounded-circle" id="refreshHistoryBtn" title="Refresh History">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle small text-center mb-0">
            <thead class="table-light">
                <tr>
                    <th>Time</th>
                    <th>Bet (₹)</th>
                    <th>Mines</th>
                    <th>Mult</th>
                    <th>Payout (₹)</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody id="minesHistoryTableBody">
                @forelse($myBets as $bet)
                <tr>
                    <td class="text-secondary">{{ $bet->created_at->format('H:i:s') }}</td>
                    <td class="fw-bold">₹{{ number_format($bet->bet_amount, 2) }}</td>
                    <td><span class="badge bg-secondary">{{ $bet->bet_details['mines_count'] ?? 3 }} Bomb</span></td>
                    <td class="fw-bold text-primary">{{ number_format($bet->multiplier, 2) }}x</td>
                    <td class="fw-bold {{ $bet->status === 'won' ? 'text-success' : 'text-danger' }}">
                        {{ $bet->status === 'won' ? '+₹' . number_format($bet->win_amount, 2) : '₹0.00' }}
                    </td>
                    <td>
                        @if($bet->status === 'won')
                            <span class="badge bg-success">WON</span>
                        @elseif($bet->status === 'lost')
                            <span class="badge bg-danger">LOST</span>
                        @else
                            <span class="badge bg-warning text-dark">PLAYING</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr id="emptyHistoryRow">
                    <td colspan="6" class="text-muted py-3">No game history yet. Start playing!</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- WINNER MODAL -->
<div class="modal fade" id="minesWinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow overflow-hidden text-center">
            <div class="p-3 text-white" style="background: linear-gradient(135deg, #10B981, #059669);">
                <div class="fs-1 mb-1"><i class="bi bi-gem text-warning"></i></div>
                <h5 class="fw-bold mb-0">CASHED OUT!</h5>
            </div>
            <div class="modal-body p-3">
                <p class="text-secondary small mb-2">You successfully cashed out at <span id="minesWinMult" class="fw-bold text-dark">1.00x</span> multiplier!</p>
                <div class="p-2 bg-light rounded-3 border mb-3">
                    <small class="text-secondary d-block">PROFIT CREDITED</small>
                    <h3 id="minesWinAmount" class="fw-extrabold text-success mb-0 font-monospace">+₹0.00</h3>
                </div>
                <button type="button" class="btn gh-btn-success w-100 py-2 rounded-pill" id="winPlayAgainBtn">PLAY AGAIN</button>
            </div>
        </div>
    </div>
</div>

<!-- LOSER MODAL -->
<div class="modal fade" id="minesLossModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow overflow-hidden text-center">
            <div class="p-3 text-white" style="background: linear-gradient(135deg, #EF4444, #DC2626);">
                <div class="fs-2 mb-1"><i class="bi bi-bomb-fill"></i></div>
                <h5 class="fw-bold mb-0">BOOM! YOU HIT A MINE</h5>
            </div>
            <div class="modal-body p-3">
                <p class="text-secondary small mb-3">Better luck next time! Try picking safer tiles.</p>
                <button type="button" class="btn btn-outline-danger w-100 py-2 rounded-pill" id="lossTryAgainBtn">TRY AGAIN</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
class MinesGameEngine {
    constructor(config) {
        this.gameId = config.gameId;
        this.csrfToken = config.csrfToken;
        this.routes = config.routes;
        this.minBet = config.minBet;
        this.maxBet = config.maxBet;

        this.activeBetId = null;
        this.isPlaying = false;
        this.isStarting = false;
        this.isProcessingClick = false;
        this.currentMultiplier = 1.00;
        this.currentProfit = 0.00;

        this.initDOM();
        this.bindEvents();
        this.resetBoard();
    }

    initDOM() {
        this.gridContainer = document.getElementById('minesGrid');
        this.startBtn = document.getElementById('startMinesBtn');
        this.cashoutBtn = document.getElementById('cashoutBtn');
        this.betInput = document.getElementById('minesBetAmount');
        this.minesSelect = document.getElementById('minesCount');
        this.multiplierDisplay = document.getElementById('currentMultiplierDisplay');
        this.profitDisplay = document.getElementById('currentProfitDisplay');

        this.winModalElement = document.getElementById('minesWinModal');
        this.lossModalElement = document.getElementById('minesLossModal');
        this.winModal = new bootstrap.Modal(this.winModalElement);
        this.lossModal = new bootstrap.Modal(this.lossModalElement);
        this.winPlayAgainBtn = document.getElementById('winPlayAgainBtn');
        this.lossTryAgainBtn = document.getElementById('lossTryAgainBtn');
        this.refreshHistoryBtn = document.getElementById('refreshHistoryBtn');
    }

    bindEvents() {
        this.startBtn.onclick = () => this.startGame();
        this.cashoutBtn.onclick = () => this.cashOut();

        this.betInput.oninput = () => {
            if (!this.isPlaying) {
                let amt = parseFloat(String(this.betInput.value).replace(/,/g, '')) || 0.00;
                this.updateHeaderDisplay(1.00, amt);
            }
        };

        if (this.refreshHistoryBtn) {
            this.refreshHistoryBtn.onclick = () => this.fetchHistory();
        }

        this.winPlayAgainBtn.onclick = () => {
            this.winModal.hide();
            this.resetBoard();
            setTimeout(() => this.startGame(), 100);
        };

        this.lossTryAgainBtn.onclick = () => {
            this.lossModal.hide();
            this.resetBoard();
            setTimeout(() => this.startGame(), 100);
        };

        this.winModalElement.addEventListener('hidden.bs.modal', () => {
            if (!this.isPlaying && !this.isStarting) this.resetBoard();
        });

        this.lossModalElement.addEventListener('hidden.bs.modal', () => {
            if (!this.isPlaying && !this.isStarting) this.resetBoard();
        });
    }

    destroyBoard() {
        if (this.gridContainer) {
            this.gridContainer.innerHTML = '';
        }
    }

    renderBoard() {
        this.destroyBoard();
        const fragment = document.createDocumentFragment();

        for (let i = 0; i < 25; i++) {
            const col = document.createElement('div');
            col.className = 'col-2';
            col.style.cssText = 'flex: 0 0 20%; max-width: 20%;';

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-light border w-100 py-3 rounded-3 text-dark fw-bold fs-4 mine-tile shadow-sm';
            btn.dataset.index = i;
            btn.disabled = !this.isPlaying;
            btn.innerHTML = '<i class="bi bi-square-fill text-muted opacity-50"></i>';

            btn.addEventListener('click', (e) => this.clickTile(i, e.currentTarget));

            col.appendChild(btn);
            fragment.appendChild(col);
        }

        this.gridContainer.appendChild(fragment);
    }

    resetBoard() {
        this.isPlaying = false;
        this.isStarting = false;
        this.isProcessingClick = false;
        this.activeBetId = null;
        this.currentMultiplier = 1.00;
        this.currentProfit = 0.00;

        this.unlockControls();
        this.startBtn.disabled = false;
        this.cashoutBtn.disabled = true;
        this.cashoutBtn.classList.remove('gh-glow-success');
        if (this.gridContainer) this.gridContainer.classList.remove('gh-red-alert-pulse');

        let initialBet = parseFloat(String(this.betInput.value).replace(/,/g, '')) || 0.00;
        this.updateHeaderDisplay(1.00, initialBet);
        this.renderBoard();
    }

    lockControls() {
        this.betInput.disabled = true;
        this.minesSelect.disabled = true;
    }

    unlockControls() {
        this.betInput.disabled = false;
        this.minesSelect.disabled = false;
    }

    updateHeaderDisplay(multiplier, profit) {
        let multVal = parseFloat(String(multiplier).replace(/,/g, '')) || 1.00;
        let profitVal = parseFloat(String(profit).replace(/,/g, '')) || 0.00;
        this.multiplierDisplay.innerText = multVal.toFixed(2) + 'x';
        this.profitDisplay.innerText = '₹' + profitVal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    updateWalletDisplay(balance) {
        if (window.updateTopWalletBalance) {
            window.updateTopWalletBalance(balance);
        } else {
            const el = document.getElementById('topWalletBalance');
            if (el) {
                let balVal = parseFloat(String(balance).replace(/,/g, '')) || 0.00;
                el.innerText = '₹' + balVal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }
    }

    async startGame() {
        if (this.isPlaying || this.isStarting) return;

        const betAmount = parseFloat(String(this.betInput.value).replace(/,/g, ''));
        const minesCount = parseInt(this.minesSelect.value);

        if (isNaN(betAmount) || betAmount < this.minBet || betAmount > this.maxBet) {
            alert(`Bet amount must be between ₹${this.minBet} and ₹${this.maxBet}`);
            return;
        }

        this.isStarting = true;
        this.startBtn.disabled = true;
        this.cashoutBtn.disabled = true;
        this.lockControls();

        try {
            const response = await fetch(this.routes.start, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    game_id: this.gameId,
                    bet_amount: betAmount,
                    mines_count: minesCount
                })
            });

            const data = await response.json();
            if (!data.success) {
                alert(data.message || 'Failed to start game.');
                this.isStarting = false;
                this.isPlaying = false;
                this.unlockControls();
                this.startBtn.disabled = false;
                return;
            }

            this.isPlaying = true;
            this.isStarting = false;
            this.activeBetId = data.bet_id;
            this.updateWalletDisplay(data.new_balance);

            // Re-render fresh board cleanly
            this.renderBoard();
            this.cashoutBtn.disabled = false;
            this.cashoutBtn.classList.add('gh-glow-success');
            this.updateHeaderDisplay(1.00, betAmount);

        } catch (err) {
            console.error('Start Game Error:', err);
            alert('Server error occurred while starting game.');
            this.isStarting = false;
            this.isPlaying = false;
            this.unlockControls();
            this.startBtn.disabled = false;
        }
    }

    async clickTile(index, buttonEl) {
        if (!this.isPlaying || !this.activeBetId || buttonEl.disabled || this.isProcessingClick) return;

        this.isProcessingClick = true;
        buttonEl.disabled = true;
        buttonEl.classList.add('tile-flip');

        try {
            const response = await fetch(this.routes.click, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    bet_id: this.activeBetId,
                    tile_index: index
                })
            });

            const data = await response.json();
            if (!data.success) {
                alert(data.message || 'Error revealing tile');
                buttonEl.disabled = false;
                buttonEl.classList.remove('tile-flip');
                this.isProcessingClick = false;
                return;
            }

            if (data.is_mine) {
                // HIT MINE -> Explosion Animation & Game Over
                buttonEl.className = 'btn mine-tile mine-explode w-100 py-3 rounded-3 fs-4';
                buttonEl.innerHTML = '<i class="bi bi-bomb-fill"></i>';

                if (window.soundManager) window.soundManager.play('explosion');
                if (window.animationManager) window.animationManager.shakeScreen();

                this.gameOver(data);

            } else if (data.is_gem) {
                // REVEAL SAFE GEM -> Diamond Glow Animation
                buttonEl.className = 'btn mine-tile gem-glow w-100 py-3 rounded-3 fs-4';
                buttonEl.innerHTML = '<i class="bi bi-gem"></i>';

                if (window.soundManager) window.soundManager.play('crystal');

                this.currentMultiplier = parseFloat(String(data.multiplier).replace(/,/g, ''));
                this.currentProfit = parseFloat(String(data.current_profit).replace(/,/g, ''));
                this.updateHeaderDisplay(this.currentMultiplier, this.currentProfit);

                if (data.status === 'won') {
                    // Auto Won (Cleared all safe tiles)
                    this.isPlaying = false;
                    this.cashoutBtn.disabled = true;
                    this.startBtn.disabled = false;
                    this.unlockControls();
                    this.updateWalletDisplay(data.new_balance);

                    let winVal = parseFloat(String(data.win_amount).replace(/,/g, '')) || 0.00;
                    document.getElementById('minesWinMult').innerText = data.multiplier + 'x';
                    document.getElementById('minesWinAmount').innerText = '+₹' + winVal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    this.winModal.show();
                    if (window.soundManager) window.soundManager.play('win');
                    if (window.animationManager) {
                        window.animationManager.triggerConfetti(60);
                        window.animationManager.animateCoinsToWallet(buttonEl);
                    }
                }
            }
        } catch (err) {
            console.error('Click Tile Error:', err);
            buttonEl.disabled = false;
            buttonEl.classList.remove('tile-flip');
        } finally {
            this.isProcessingClick = false;
        }
    }

    async cashOut() {
        if (!this.isPlaying || !this.activeBetId) return;

        this.cashoutBtn.disabled = true;

        try {
            const response = await fetch(this.routes.cashout, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    bet_id: this.activeBetId
                })
            });

            const data = await response.json();
            if (!data.success) {
                alert(data.message || 'Cashout failed');
                this.cashoutBtn.disabled = false;
                return;
            }

            this.isPlaying = false;
            this.unlockControls();
            this.startBtn.disabled = false;
            this.updateWalletDisplay(data.new_balance);

            // Reveal all mines returned by server
            if (data.mine_positions && Array.isArray(data.mine_positions)) {
                data.mine_positions.forEach(mIdx => {
                    const mineBtn = this.gridContainer.querySelector(`.mine-tile[data-index="${mIdx}"]`);
                    if (mineBtn && !mineBtn.classList.contains('gem-glow')) {
                        mineBtn.className = 'btn btn-outline-danger w-100 py-3 rounded-3 fs-4';
                        mineBtn.innerHTML = '<i class="bi bi-bomb-fill"></i>';
                    }
                });
            }

            let cashoutWinVal = parseFloat(String(data.win_amount).replace(/,/g, '')) || 0.00;
            document.getElementById('minesWinMult').innerText = data.multiplier + 'x';
            document.getElementById('minesWinAmount').innerText = '+₹' + cashoutWinVal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            this.winModal.show();
            if (window.soundManager) window.soundManager.play('cashout');
            if (window.animationManager) {
                window.animationManager.triggerConfetti(60);
                window.animationManager.animateCoinsToWallet(this.cashoutBtn);
            }
            this.fetchHistory();

        } catch (err) {
            console.error('Cashout Error:', err);
            this.cashoutBtn.disabled = false;
        }
    }

    gameOver(data) {
        this.isPlaying = false;
        this.unlockControls();
        this.startBtn.disabled = false;
        this.cashoutBtn.disabled = true;

        // Reveal all mines returned by server
        if (data.mine_positions && Array.isArray(data.mine_positions)) {
            data.mine_positions.forEach(mIdx => {
                const mineBtn = this.gridContainer.querySelector(`.mine-tile[data-index="${mIdx}"]`);
                if (mineBtn && !mineBtn.classList.contains('mine-explode')) {
                    mineBtn.className = 'btn btn-danger w-100 py-3 rounded-3 text-white fs-4';
                    mineBtn.innerHTML = '<i class="bi bi-bomb-fill"></i>';
                }
            });
        }

        // Disable remaining unrevealed tiles
        this.gridContainer.querySelectorAll('.mine-tile').forEach(tile => {
            tile.disabled = true;
        });

        this.lossModal.show();
        this.fetchHistory();
    }

    async fetchHistory() {
        try {
            const response = await fetch(this.routes.history);
            const data = await response.json();
            if (data.success && Array.isArray(data.history)) {
                this.renderHistory(data.history);
            }
        } catch (err) {
            console.error('Fetch History Error:', err);
        }
    }

    renderHistory(historyItems) {
        const tbody = document.getElementById('minesHistoryTableBody');
        if (!tbody) return;

        if (historyItems.length === 0) {
            tbody.innerHTML = '<tr id="emptyHistoryRow"><td colspan="6" class="text-muted py-3">No game history yet. Start playing!</td></tr>';
            return;
        }

        let html = '';
        historyItems.forEach(item => {
            let statusBadge = '<span class="badge bg-warning text-dark">PLAYING</span>';
            let payoutClass = 'text-dark';
            let payoutText = '₹0.00';

            if (item.status === 'won') {
                statusBadge = '<span class="badge bg-success">WON</span>';
                payoutClass = 'text-success';
                payoutText = '+₹' + item.win_amount;
            } else if (item.status === 'lost') {
                statusBadge = '<span class="badge bg-danger">LOST</span>';
                payoutClass = 'text-danger';
                payoutText = '₹0.00';
            }

            html += `
                <tr>
                    <td class="text-secondary">${item.time}</td>
                    <td class="fw-bold">₹${item.bet_amount}</td>
                    <td><span class="badge bg-secondary">${item.mines_count} Bomb</span></td>
                    <td class="fw-bold text-primary">${item.multiplier}</td>
                    <td class="fw-bold ${payoutClass}">${payoutText}</td>
                    <td>${statusBadge}</td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.minesGame = new MinesGameEngine({
        gameId: "{{ $game->id }}",
        csrfToken: "{{ csrf_token() }}",
        minBet: {{ $game->min_entry_fee }},
        maxBet: {{ $game->max_entry_fee }},
        routes: {
            start: "{{ route('games.mines.start') }}",
            click: "{{ route('games.mines.click') }}",
            cashout: "{{ route('games.mines.cashout') }}",
            history: "{{ route('games.mines.history') }}"
        }
    });
});
</script>
@endpush
@endsection
