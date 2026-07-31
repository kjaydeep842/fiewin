/**
 * CrashGameManager — Synchronized Real-Time Server State Crash Engine
 */

class CrashGameManager {
    constructor(config) {
        this.config = config; // routes, gameId, minBet, maxBet
        this.renderer = null;
        this.currentState = null; // 'BETTING_OPEN', 'FLYING', 'CRASHED'
        this.activeRoundId = null;
        this.clientMultiplier = 1.00;
        this.targetMultiplier = 1.00;
        this.userBet = null;
        this.pollInterval = null;
        this.flightLoop = null;

        this.init();
    }

    init() {
        this.renderer = new CrashCanvasRenderer('crashCanvas');
        this.bindEvents();
        this.startStatePolling();
        this.startSmoothFlightLoop();
    }

    bindEvents() {
        const betBtn = document.getElementById('betCrashBtn');
        const cashoutBtn = document.getElementById('cashoutCrashBtn');
        const halfBtn = document.getElementById('btnHalfBet');
        const doubleBtn = document.getElementById('btnDoubleBet');

        if (betBtn) betBtn.addEventListener('click', () => this.placeBet());
        if (cashoutBtn) cashoutBtn.addEventListener('click', () => this.cashOut());

        if (halfBtn) {
            halfBtn.addEventListener('click', () => {
                const input = document.getElementById('crashBetAmount');
                input.value = Math.max(this.config.minBet, Math.floor(parseFloat(input.value) / 2));
                if (window.soundManager) window.soundManager.play('click');
            });
        }

        if (doubleBtn) {
            doubleBtn.addEventListener('click', () => {
                const input = document.getElementById('crashBetAmount');
                input.value = Math.min(this.config.maxBet, Math.floor(parseFloat(input.value) * 2));
                if (window.soundManager) window.soundManager.play('click');
            });
        }
    }

    switchMode(mode) {
        if (this.renderer) this.renderer.setMode(mode);
        const title = document.getElementById('gameModeTitle');
        const btnRocket = document.getElementById('btnModeRocket');
        const btnJet = document.getElementById('btnModeJet');

        if (mode === 'rocket') {
            if (btnRocket) btnRocket.className = 'btn btn-sm btn-primary rounded-pill px-3 fw-bold';
            if (btnJet) btnJet.className = 'btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold';
            if (title) title.innerHTML = '<i class="bi bi-rocket-takeoff-fill text-danger me-2"></i>Rocket Launch Arena';
        } else {
            if (btnJet) btnJet.className = 'btn btn-sm btn-success rounded-pill px-3 fw-bold';
            if (btnRocket) btnRocket.className = 'btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold';
            if (title) title.innerHTML = '<i class="bi bi-airplane-engines-fill text-success me-2"></i>Jet Fighter Flight';
        }
        if (window.soundManager) window.soundManager.play('click');
    }

    /**
     * Poll server state every 1 second for perfect timestamp synchronization
     */
    startStatePolling() {
        this.fetchServerState();
        this.pollInterval = setInterval(() => {
            this.fetchServerState();
        }, 1000);
    }

    /**
     * Smooth 60 FPS local extrapolation tick loop for high performance multiplier count-up
     */
    startSmoothFlightLoop() {
        const tick = () => {
            if (this.currentState === 'FLYING') {
                if (this.clientMultiplier < this.targetMultiplier) {
                    this.clientMultiplier += 0.008; // Smooth count up between server polls
                } else {
                    this.clientMultiplier += 0.004;
                }

                const multText = document.getElementById('crashMultiplierText');
                if (multText) multText.innerText = this.clientMultiplier.toFixed(2) + 'x';
                if (this.renderer) this.renderer.setFlightState(this.clientMultiplier);
                if (window.soundManager) window.soundManager.updateEnginePitch(this.clientMultiplier);
            }
            requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    }

    async fetchServerState() {
        try {
            const res = await fetch(this.config.routes.state, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (data.success) {
                this.updateGameState(data);
            }
        } catch (err) {
            console.error('State sync error:', err);
        }
    }

    updateGameState(data) {
        const round = data.round;
        const statusBadge = document.getElementById('crashStatusBadge');
        const multText = document.getElementById('crashMultiplierText');
        const betBtn = document.getElementById('betCrashBtn');
        const cashoutBtn = document.getElementById('cashoutCrashBtn');

        if (data.user_balance && window.updateTopWalletBalance) {
            window.updateTopWalletBalance(data.user_balance);
        }

        // Update Round History Pills
        if (data.history) this.renderHistoryPills(data.history);

        // Update Live Player Panel
        if (data.live_bets) this.renderLiveBets(data.live_bets);

        // State Machine Handling
        if (round.status === 'BETTING_OPEN') {
            if (this.currentState !== 'BETTING_OPEN') {
                this.currentState = 'BETTING_OPEN';
                this.activeRoundId = round.id;
                this.userBet = null;
                this.clientMultiplier = 1.00;
                this.targetMultiplier = 1.00;
                if (this.renderer) this.renderer.resetState();
            }

            const secs = String(data.seconds_remaining).padStart(2, '0');
            if (statusBadge) {
                statusBadge.className = 'badge bg-primary bg-opacity-10 text-primary fs-6 px-4 py-2 rounded-pill border border-primary';
                statusBadge.innerText = `NEXT ROUND: ${secs}s`;
            }
            if (multText) multText.innerText = '1.00x';

            if (betBtn && !data.user_bet) {
                betBtn.disabled = false;
                betBtn.className = 'btn gh-btn-primary w-100 py-3 fs-6 fw-bold rounded-4 shadow-sm';
                betBtn.innerHTML = '<i class="bi bi-rocket-fill me-1"></i>PLACE BET';
            }
            if (cashoutBtn) {
                cashoutBtn.disabled = true;
                cashoutBtn.classList.remove('gh-glow-success');
                cashoutBtn.innerHTML = '<i class="bi bi-cash-stack me-1"></i>CASH OUT';
            }

        } else if (round.status === 'FLYING') {
            if (this.currentState !== 'FLYING') {
                this.currentState = 'FLYING';
                this.clientMultiplier = 1.00;
                if (window.soundManager) window.soundManager.startEngineSound();
            }

            this.targetMultiplier = parseFloat(data.current_multiplier);

            if (statusBadge) {
                statusBadge.className = 'badge bg-success bg-opacity-10 text-success fs-6 px-4 py-2 rounded-pill border border-success';
                statusBadge.innerHTML = '<i class="bi bi-airplane-engines-fill me-1"></i>IN FLIGHT';
            }

            if (betBtn) {
                betBtn.disabled = true;
            }

            // User Bet State in Flying Round
            if (data.user_bet) {
                this.userBet = data.user_bet;
                if (data.user_bet.status === 'flying') {
                    if (cashoutBtn) {
                        cashoutBtn.disabled = false;
                        cashoutBtn.className = 'btn gh-btn-success w-100 py-3 fs-6 fw-bold rounded-4 shadow-sm gh-glow-success';
                        cashoutBtn.innerHTML = `<i class="bi bi-cash-stack me-1"></i>CASH OUT`;
                    }
                } else if (data.user_bet.status === 'cashed_out') {
                    if (cashoutBtn) {
                        cashoutBtn.disabled = true;
                        cashoutBtn.className = 'btn btn-outline-success w-100 py-3 fs-6 fw-bold rounded-4 opacity-75';
                        cashoutBtn.innerHTML = `<i class="bi bi-check2-circle me-1"></i>CASHED OUT @ ${data.user_bet.cashout_multiplier}x`;
                        cashoutBtn.classList.remove('gh-glow-success');
                    }
                }
            }

        } else if (round.status === 'CRASHED') {
            if (this.currentState !== 'CRASHED') {
                this.currentState = 'CRASHED';
                if (this.renderer) this.renderer.triggerCrash(parseFloat(round.crash_multiplier));
            }

            if (statusBadge) {
                statusBadge.className = 'badge bg-danger bg-opacity-25 text-danger fs-6 px-4 py-2 rounded-pill border border-danger gh-red-alert-pulse';
                statusBadge.innerHTML = `<i class="bi bi-x-circle-fill me-1"></i>CRASHED AT ${round.crash_multiplier}x`;
            }
            if (multText) multText.innerText = round.crash_multiplier + 'x';

            if (betBtn) betBtn.disabled = true;
            if (cashoutBtn) {
                cashoutBtn.disabled = true;
                cashoutBtn.classList.remove('gh-glow-success');
            }
        }
    }

    async placeBet() {
        if (this.currentState !== 'BETTING_OPEN') return;

        const betAmount = parseFloat(document.getElementById('crashBetAmount').value);
        if (isNaN(betAmount) || betAmount < this.config.minBet || betAmount > this.config.maxBet) {
            alert(`Bet amount must be between ₹${this.config.minBet} and ₹${this.config.maxBet}`);
            return;
        }

        const betBtn = document.getElementById('betCrashBtn');
        betBtn.disabled = true;
        betBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>PLACING...';

        const formData = new FormData();
        formData.append('bet_amount', betAmount);

        try {
            const res = await fetch(this.config.routes.bet, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this.config.csrfToken, 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json();

            if (!data.success) {
                alert(data.message || 'Bet placement failed');
                betBtn.disabled = false;
                betBtn.innerHTML = '<i class="bi bi-rocket-fill me-1"></i>PLACE BET';
                return;
            }

            this.userBet = data.bet;
            if (window.updateTopWalletBalance) window.updateTopWalletBalance(data.new_balance);

            betBtn.className = 'btn btn-success w-100 py-3 fs-6 fw-bold rounded-4 shadow-sm';
            betBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>BET PLACED';
            if (window.soundManager) window.soundManager.play('click');

        } catch (err) {
            console.error(err);
            alert('Failed to place bet. Please try again.');
            betBtn.disabled = false;
            betBtn.innerHTML = '<i class="bi bi-rocket-fill me-1"></i>PLACE BET';
        }
    }

    async cashOut() {
        if (this.currentState !== 'FLYING' || !this.userBet || this.userBet.status !== 'flying') return;

        const cashoutBtn = document.getElementById('cashoutCrashBtn');
        cashoutBtn.disabled = true;

        const formData = new FormData();
        formData.append('bet_id', this.userBet.id);
        formData.append('multiplier', this.clientMultiplier.toFixed(2));

        try {
            const res = await fetch(this.config.routes.cashout, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this.config.csrfToken, 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                this.userBet.status = 'cashed_out';
                this.userBet.cashout_multiplier = data.multiplier;

                if (window.updateTopWalletBalance) window.updateTopWalletBalance(data.new_balance);
                document.getElementById('crashWinMult').innerText = data.multiplier + 'x';
                document.getElementById('crashWinAmount').innerText = '+₹' + data.win_amount;

                const winModal = new bootstrap.Modal(document.getElementById('crashWinModal'));
                winModal.show();

                if (this.renderer) this.renderer.triggerPlayerCashOut();
            } else {
                alert(data.message || 'Cashout failed');
            }
        } catch (err) {
            console.error(err);
            alert('Cashout request failed. Please try again.');
        }
    }

    renderHistoryPills(historyList) {
        const container = document.getElementById('historyPillContainer');
        if (!container) return;

        let html = `<span class="badge bg-white text-secondary border shadow-sm flex-shrink-0 py-2 px-3" style="font-size: 0.72rem;">
            <i class="bi bi-clock-history me-1 text-primary"></i>HISTORY
        </span>`;

        historyList.forEach(item => {
            const multVal = item.crash_multiplier || item.multiplier || item.win_multiplier || '1.00';
            let badgeClass = 'bg-danger bg-opacity-10 text-danger border-danger';
            const num = parseFloat(multVal);
            if (num >= 2.0) badgeClass = 'bg-success bg-opacity-10 text-success border-success';
            else if (num >= 1.5) badgeClass = 'bg-warning bg-opacity-10 text-warning border-warning';

            html += `<span class="badge rounded-pill ${badgeClass} border fw-bold px-3 py-1 me-1">${multVal}x</span>`;
        });

        container.innerHTML = html;
    }

    renderLiveBets(betsList) {
        const tbody = document.getElementById('livePlayersBody');
        const flyingCountEl = document.getElementById('statFlyingPlayers');
        const cashedCountEl = document.getElementById('statCashedPlayers');
        if (!tbody) return;

        let flyingCount = 0;
        let cashedCount = 0;
        let html = '';

        betsList.forEach(p => {
            if (p.status === 'flying') flyingCount++;
            if (p.status === 'cashed_out') cashedCount++;

            let statusBadge = '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning">✈️ Flying</span>';
            if (p.status === 'cashed_out') {
                statusBadge = `<span class="badge bg-success bg-opacity-10 text-success border border-success">💰 @ ${p.cashout_multiplier}x</span>`;
            } else if (p.status === 'lost') {
                statusBadge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger">💥 Crashed</span>';
            }

            html += `<tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=${p.username}" class="rounded-circle" width="24" height="24">
                        <span class="fw-bold text-dark small">${p.username}</span>
                    </div>
                </td>
                <td class="fw-bold">₹${p.bet_amount}</td>
                <td>${statusBadge}</td>
                <td class="fw-bold ${parseFloat(p.profit) > 0 ? 'text-success' : 'text-muted'}">
                    ${parseFloat(p.profit) > 0 ? '+₹' + p.profit : '-'}
                </td>
            </tr>`;
        });

        tbody.innerHTML = html || '<tr><td colspan="4" class="text-secondary py-3">No active bets in this round</td></tr>';
        if (flyingCountEl) flyingCountEl.innerText = flyingCount;
        if (cashedCountEl) cashedCountEl.innerText = cashedCount;
    }
}

window.CrashGameManager = CrashGameManager;
