/**
 * Crash Game Manager (Dedicated Space Rocket 🚀 Flight Manager)
 */
class CrashGameManager {
    constructor() {
        this.renderer = null;
        this.pollTimer = null;
        this.animFrame = null;
        this.userBet = null;
        this.serverState = null;
        this.init();
    }

    init() {
        this.renderer = new CrashCanvasRenderer('crashCanvas');
        this.bindEvents();
        this.wsManager = new FiewinWebSocketManager('crash', window.USER_ID || null);
        this.wsManager.on('GameStateUpdated', (state) => {
            this.updateUI(state);
            this.fetchServerState();
        });
        this.wsManager.on('RequestPollingSync', () => this.fetchServerState());
        // Always tick the server-side round engine, even when WS is connected
        this.startStateSyncTicker();
        this.startSmoothFlightLoop();
    }

    startStateSyncTicker() {
        if (this.syncTicker) clearInterval(this.syncTicker);
        this.fetchServerState(); // Immediate initial state load
        this.syncTicker = setInterval(() => this.fetchServerState(), 1000); // 1s sync ticker
    }

    bindEvents() {
        const betBtn = document.getElementById('betCrashBtn');
        const cashoutBtn = document.getElementById('cashoutCrashBtn');

        if (betBtn) {
            betBtn.addEventListener('click', () => this.placeBet());
        }
        if (cashoutBtn) {
            cashoutBtn.addEventListener('click', () => this.cashOut());
        }

        // Quick amount buttons
        document.querySelectorAll('[data-crash-amount]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const amountInput = document.getElementById('crashBetAmount');
                if (amountInput) {
                    amountInput.value = e.target.getAttribute('data-crash-amount');
                }
            });
        });

        // Half / Double buttons
        const halfBtn = document.getElementById('btnHalfBetCrash');
        const doubleBtn = document.getElementById('btnDoubleBetCrash');

        if (halfBtn) {
            halfBtn.addEventListener('click', () => {
                const input = document.getElementById('crashBetAmount');
                if (input) input.value = Math.max(10, Math.floor((parseFloat(input.value) || 10) / 2));
            });
        }
        if (doubleBtn) {
            doubleBtn.addEventListener('click', () => {
                const input = document.getElementById('crashBetAmount');
                if (input) input.value = Math.min(500000, Math.floor((parseFloat(input.value) || 10) * 2));
            });
        }

        // Preset Auto Cashout multipliers
        const autoInput = document.getElementById('crashAutoCashoutInput');
        document.querySelectorAll('.crash-auto-chip').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const val = btn.getAttribute('data-val');
                if (autoInput) autoInput.value = val;
                const statusTxt = document.getElementById('crashAutoStatusText');
                if (statusTxt) statusTxt.textContent = val ? val + 'x' : 'OFF';
            });
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) this.fetchServerState();
        });
        window.addEventListener('focus', () => this.fetchServerState());
        window.addEventListener('online', () => this.fetchServerState());
    }

    startStatePolling() {
        this.fetchServerState();
        this.pollTimer = setInterval(() => this.fetchServerState(), 1000);
    }

    async fetchServerState() {
        try {
            const response = await fetch('/games/crash/state', {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) return;
            const data = await response.json();
            if (data.success) {
                this.updateUI(data);
            }
        } catch (e) {
            console.error('Crash State Sync Error:', e);
        }
    }

    updateUI(state) {
        if (!state) return;

        // Safely extract properties whether state is full HTTP sync or WS event payload
        const roundObj = state.round || (state.status ? state : (this.serverState?.round || {}));
        const roundStatus = roundObj.status || state.status || this.serverState?.round?.status || 'BETTING_OPEN';
        const crashMult = roundObj.crash_multiplier ?? state.crash_multiplier ?? (this.serverState?.round?.crash_multiplier ?? '1.00');
        const currentMultiplier = state.current_multiplier ?? state.multiplier ?? (this.serverState?.current_multiplier ?? '1.00');
        const secondsRemaining = state.seconds_remaining ?? (this.serverState?.seconds_remaining ?? 0);

        const prevStatus = this.serverState?.round?.status || this.serverState?.status;
        const newStatus = roundStatus;

        const multDisplay = document.getElementById('crashMultiplierText');
        const statusBadge = document.getElementById('crashStatusBadge');
        const userBalDisplay = document.getElementById('userWalletBalance');
        const currentRoundIdEl = document.getElementById('crashCurrentRoundId');

        if (currentRoundIdEl && roundObj.round_id) {
            currentRoundIdEl.textContent = roundObj.round_id;
        }

        if (userBalDisplay && state.user_balance !== undefined) {
            userBalDisplay.textContent = '₹' + state.user_balance;
        }

        if (multDisplay) {
            multDisplay.textContent = currentMultiplier + 'x';
            if (roundStatus === 'CRASHED') {
                multDisplay.className = 'display-2 fw-black text-danger';
            } else {
                multDisplay.className = 'display-2 fw-black text-primary';
            }
        }

        if (statusBadge) {
            if (roundStatus === 'BETTING_OPEN') {
                const secsStr = String(Math.max(0, parseInt(secondsRemaining) || 0)).padStart(2, '0');
                statusBadge.innerHTML = `<span class="badge bg-primary px-3 py-2 fs-6">NEXT LAUNCH: ${secsStr}s</span>`;
            } else if (roundStatus === 'FLYING') {
                statusBadge.innerHTML = `<span class="badge bg-success px-3 py-2 fs-6">ROCKET ASCENDING</span>`;
            } else {
                const secsStr = String(Math.max(0, parseInt(secondsRemaining) || 0)).padStart(2, '0');
                statusBadge.innerHTML = `<span class="badge bg-danger px-3 py-2 fs-6">CRASHED @ ${crashMult}x • NEXT LAUNCH: ${secsStr}s</span>`;
            }
        }

        // --- Sound Triggers ---
        if (window.soundManager) {
            if (roundStatus === 'BETTING_OPEN' && secondsRemaining <= 5 && secondsRemaining > 0) {
                window.soundManager.play('launchCountdown');
            }
            if (prevStatus === 'BETTING_OPEN' && newStatus === 'FLYING') {
                window.soundManager.play('engineIgnition');
            }
            if (prevStatus === 'FLYING' && newStatus === 'CRASHED') {
                window.soundManager.play('alarm');
            }
            if (prevStatus === 'CRASHED' && newStatus === 'BETTING_OPEN') {
                window.soundManager.play('notification');
            }
        }

        let userBetData = null;
        if (state.user_bet !== undefined && state.user_bet !== null) {
            userBetData = state.user_bet;
        } else if (state.player && state.player.has_active_bet) {
            userBetData = {
                id: state.player.bet_id,
                round_id: roundObj.round_id || (this.serverState?.round?.round_id),
                bet_amount: state.player.bet_amount,
                auto_cashout: state.player.auto_cashout,
                status: state.player.status
            };
        }

        if (userBetData && userBetData.status === 'flying') {
            this.userBet = userBetData;
            this.updateActiveBetCard(userBetData, currentMultiplier);
        } else if (state.user_bet === null || (state.player && !state.player.has_active_bet)) {
            this.userBet = null;
            this.hideActiveBetCard();
        }

        this.toggleBetControls(roundStatus);

        if (state.history) {
            this.renderHistory(state.history);
        }

        if (state.live_bets) {
            this.renderLiveBets(state.live_bets);
        }

        if (state.my_orders) {
            this.renderMyOrders(state.my_orders);
        }

        // Preserve normalized state in serverState
        this.serverState = {
            ...(this.serverState || {}),
            ...state,
            user_bet: this.userBet,
            round: {
                ...(this.serverState?.round || {}),
                ...(state.round || {}),
                status: roundStatus,
                crash_multiplier: crashMult
            },
            current_multiplier: currentMultiplier,
            seconds_remaining: secondsRemaining
        };
    }

    updateActiveBetCard(bet, currentMultStr) {
        const card = document.getElementById('crashActiveBetCard');
        if (!card) return;

        if (bet.status === 'flying') {
            card.classList.remove('d-none');
            const roundIdEl = document.getElementById('crashActiveRoundId');
            const stakeEl = document.getElementById('crashActiveStake');
            const profitEl = document.getElementById('crashActiveLiveProfit');
            const payoutEl = document.getElementById('crashActivePayout');

            const mult = parseFloat(currentMultStr || 1.00);
            const stake = parseFloat(String(bet.bet_amount).replace(/,/g, ''));
            const payout = (stake * mult).toFixed(2);
            const profit = (payout - stake).toFixed(2);

            if (roundIdEl) roundIdEl.textContent = bet.round_id || '-';
            if (stakeEl) stakeEl.textContent = '₹' + bet.bet_amount;
            if (profitEl) profitEl.textContent = '+₹' + profit;
            if (payoutEl) payoutEl.textContent = '₹' + payout;
        } else {
            card.classList.add('d-none');
        }
    }

    hideActiveBetCard() {
        const card = document.getElementById('crashActiveBetCard');
        if (card) card.classList.add('d-none');
    }

    toggleBetControls(status) {
        const betBtn = document.getElementById('betCrashBtn');
        const cashoutBtn = document.getElementById('cashoutCrashBtn');

        if (this.userBet && this.userBet.status === 'flying') {
            const isCurrentRound = (this.userBet.round_id === this.serverState?.round?.round_id);

            if (status === 'FLYING' && isCurrentRound) {
                if (betBtn) betBtn.classList.add('d-none');
                if (cashoutBtn) {
                    cashoutBtn.classList.remove('d-none');
                    cashoutBtn.disabled = false;
                    const mult = parseFloat(this.serverState ? this.serverState.current_multiplier : 1.00);
                    const winAmt = (parseFloat(String(this.userBet.bet_amount).replace(/,/g, '')) * mult).toFixed(2);
                    cashoutBtn.innerHTML = `<i class="bi bi-cash-stack me-2"></i>CASH OUT ₹${winAmt} (${mult.toFixed(2)}x)`;
                }
            } else {
                if (cashoutBtn) {
                    cashoutBtn.classList.add('d-none');
                    cashoutBtn.disabled = false;
                }
                if (betBtn) {
                    betBtn.classList.remove('d-none');
                    betBtn.disabled = true;
                    betBtn.className = 'btn btn-warning btn-lg w-100 rounded-pill fw-bold shadow text-dark';
                    betBtn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>BET PLACED FOR NEXT ROUND';
                }
            }
        } else {
            if (cashoutBtn) {
                cashoutBtn.classList.add('d-none');
                cashoutBtn.disabled = false;
            }
            if (betBtn) {
                betBtn.classList.remove('d-none');
                betBtn.className = 'btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow py-3';
                betBtn.disabled = false;
                betBtn.innerHTML = '<i class="bi bi-rocket-fill me-2"></i>PLACE ROCKET BET';
            }
        }
    }

    renderHistory(history) {
        const container = document.getElementById('crashHistoryPills');
        if (!container) return;
        container.innerHTML = history.map(item => {
            const cm = item.crash_multiplier ?? '?';
            const badgeClass = item.color === 'green' ? 'bg-success' : (item.color === 'orange' ? 'bg-warning text-dark' : 'bg-danger');
            return `<span class="badge ${badgeClass} rounded-pill px-3 py-2 me-1 fs-6">${cm}x</span>`;
        }).join('');
    }

    renderLiveBets(bets) {
        const container = document.getElementById('crashLiveBetsList');
        if (!container) return;
        container.innerHTML = bets.map(b => {
            const stake = b.bet_amount ?? '0.00';
            const autoCo = b.auto_cashout ? `• Auto: ${b.auto_cashout}x` : '';
            const rightCol = b.cashout_multiplier
                ? `${b.cashout_multiplier}x (+\u20b9${b.profit ?? '0.00'})`
                : (b.status ?? 'flying');
            return `
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div>
                    <span class="fw-bold d-block">${b.username ?? 'Player'}</span>
                    <small class="text-muted">Stake \u20b9${stake} ${autoCo}</small>
                </div>
                <span class="${b.status === 'cashed_out' ? 'text-success fw-bold' : 'text-muted'}">
                    ${rightCol}
                </span>
            </div>`;
        }).join('');
    }

    renderMyOrders(orders) {
        const container = document.getElementById('crashMyOrdersList');
        if (!container) return;
        if (orders.length === 0) {
            container.innerHTML = '<div class="text-muted text-center py-3">No Rocket orders placed today.</div>';
            return;
        }
        container.innerHTML = orders.map(o => {
            const betAmt = parseFloat(o.bet_amount || 0);
            const multVal = Math.abs(parseFloat(o.cashout_multiplier || 0));
            const payout = (betAmt * (multVal > 0 ? multVal : 1)).toFixed(2);
            const netProfit = (payout - betAmt).toFixed(2);

            let statusBadge = '';
            let profitDisplay = '';

            if (o.status === 'cashed_out') {
                statusBadge = `<span class="badge bg-success px-2 py-1">${multVal.toFixed(2)}x</span>`;
                profitDisplay = `<div class="small fw-bold text-success">+₹${payout}</div>`;
            } else if (o.status === 'flying') {
                statusBadge = `<span class="badge bg-warning text-dark px-2 py-1">RUNNING</span>`;
                profitDisplay = `<div class="small fw-bold text-warning">IN FLIGHT</div>`;
            } else {
                statusBadge = `<span class="badge bg-danger px-2 py-1">LOST</span>`;
                profitDisplay = `<div class="small fw-bold text-muted">₹0.00</div>`;
            }

            return `
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 0.85rem;">${o.round_id}</div>
                        <small class="text-muted">₹${betAmt.toFixed(2)} ${o.auto_cashout !== '-' ? '• Auto: ' + o.auto_cashout + 'x' : ''} • ${o.time}</small>
                    </div>
                    <div class="text-end">
                        ${statusBadge}
                        ${profitDisplay}
                    </div>
                </div>
            `;
        }).join('');
    }

    showToast(message, type = 'success', customTitle = null) {
        let toastContainer = document.getElementById('gameToastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'gameToastContainer';
            toastContainer.style.cssText = 'position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; width: 90%; max-width: 380px; pointer-events: none;';
            document.body.appendChild(toastContainer);
        }

        let title = customTitle;
        let bgClass = 'bg-primary text-white';
        let icon = 'bi-check-circle-fill text-white';

        if (type === 'cashout') {
            title = title || 'CASHOUT SUCCESSFUL!';
            bgClass = 'bg-success text-white';
            icon = 'bi-trophy-fill text-warning';
        } else if (type === 'danger') {
            title = title || 'NOTICE';
            bgClass = 'bg-danger text-white';
            icon = 'bi-exclamation-circle-fill text-white';
        } else if (type === 'info') {
            title = title || 'INFO';
            bgClass = 'bg-info text-white';
            icon = 'bi-info-circle-fill text-white';
        } else {
            title = title || 'BET PLACED';
            bgClass = 'bg-primary text-white';
            icon = 'bi-rocket-fill text-white';
        }

        const toastEl = document.createElement('div');
        toastEl.className = `card border-0 shadow-lg rounded-4 overflow-hidden mb-2 ${bgClass}`;
        toastEl.style.cssText = 'pointer-events: auto; animation: fadeInDown 0.3s ease;';
        toastEl.innerHTML = `
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi ${icon} fs-2"></i>
                    <div>
                        <div class="fw-bold fs-6 mb-0">${title}</div>
                        <div class="small opacity-90">${message}</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="this.closest('.card').remove()"></button>
            </div>
        `;

        toastContainer.appendChild(toastEl);
        setTimeout(() => {
            toastEl.style.opacity = '0';
            toastEl.style.transition = 'opacity 0.5s ease';
            setTimeout(() => toastEl.remove(), 500);
        }, 3000);
    }

    async placeBet() {
        const input = document.getElementById('crashBetAmount');
        const autoInput = document.getElementById('crashAutoCashoutInput');
        const amount = parseFloat(input?.value || 0);
        const autoVal = autoInput && autoInput.value ? parseFloat(autoInput.value) : null;

        if (!amount || amount < 10) return this.showToast('Minimum bet is ₹10', 'danger');

        const betBtn = document.getElementById('betCrashBtn');
        if (betBtn) {
            betBtn.disabled = true;
            betBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>PLACING BET...';
        }
        if (window.soundManager) window.soundManager.play('click');

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch('/games/crash/bet', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    bet_amount: amount,
                    auto_cashout: autoVal
                })
            });

            const data = await res.json();
            if (data.success) {
                this.userBet = data.bet;
                this.showToast(data.message || 'Rocket bet placed successfully! Waiting for launch...', 'success', 'BET PLACED');
                if (data.new_balance) {
                    const userBalDisplay = document.getElementById('userWalletBalance');
                    if (userBalDisplay) userBalDisplay.textContent = '₹' + data.new_balance;
                }
                this.fetchServerState();
            } else {
                if (window.soundManager) window.soundManager.play('lose');
                this.showToast(data.message || 'Failed to place bet', 'danger');
                if (betBtn) {
                    betBtn.disabled = false;
                    betBtn.innerHTML = '<i class="bi bi-rocket-fill me-2"></i>PLACE ROCKET BET';
                }
            }
        } catch (e) {
            console.error('Crash Bet Error:', e);
            this.showToast('Network error, please try again.', 'danger');
            if (betBtn) {
                betBtn.disabled = false;
                betBtn.innerHTML = '<i class="bi bi-rocket-fill me-2"></i>PLACE ROCKET BET';
            }
        }
    }

    async cashOut() {
        // Prevent double cashout click
        if (this._cashingOut) return;
        this._cashingOut = true;

        const activeBet = this.userBet || (this.serverState?.user_bet) || (this.serverState?.player?.has_active_bet ? this.serverState.player : null);
        const betId = activeBet?.id || activeBet?.bet_id;

        if (!betId) {
            console.error('No active bet ID found for cashout.');
            this._cashingOut = false;
            return;
        }

        const currentMult = parseFloat(this.serverState?.current_multiplier || '1.00');

        const cashoutBtn = document.getElementById('cashoutCrashBtn');
        if (cashoutBtn) {
            cashoutBtn.disabled = true;
            cashoutBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>CASHING OUT...';
        }

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch('/games/crash/cashout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    bet_id: betId,
                    multiplier: currentMult
                })
            });

            const data = await res.json();
            if (data.success) {
                this.userBet = null;
                this._cashingOut = false;
                this.hideActiveBetCard();
                if (window.soundManager) window.soundManager.play('cashout');
                this.showToast(data.message, 'cashout', 'CASHOUT SUCCESSFUL!');
                if (data.new_balance) {
                    const userBalDisplay = document.getElementById('userWalletBalance');
                    if (userBalDisplay) userBalDisplay.textContent = '₹' + data.new_balance;
                    // Also update top header balance
                    const topBal = document.getElementById('topWalletBalance');
                    if (topBal) topBal.textContent = '₹' + data.new_balance;
                }
                this.fetchServerState();
            } else {
                this._cashingOut = false;
                if (window.soundManager) window.soundManager.play('lose');
                this.showToast(data.message || 'Cashout failed', 'danger');
                // Re-enable cashout button so player can retry
                if (cashoutBtn) {
                    cashoutBtn.disabled = false;
                    cashoutBtn.innerHTML = `<i class="bi bi-cash-stack me-2"></i>CASH OUT NOW`;
                }
                this.toggleBetControls(this.serverState?.round?.status || 'BETTING_OPEN');
            }
        } catch (e) {
            this._cashingOut = false;
            console.error('Crash Cashout Error:', e);
            this.showToast('Network error during cashout. Please try again.', 'danger');
            if (cashoutBtn) {
                cashoutBtn.disabled = false;
                cashoutBtn.innerHTML = `<i class="bi bi-cash-stack me-2"></i>CASH OUT NOW`;
            }
            this.toggleBetControls(this.serverState?.round?.status || 'BETTING_OPEN');
        }
    }

    startSmoothFlightLoop() {
        const loop = () => {
            try {
                if (this.serverState && this.renderer) {
                    const roundObj = this.serverState.round || {};
                    const status = roundObj.status || this.serverState.status || 'BETTING_OPEN';

                    let mult = parseFloat(this.serverState.current_multiplier || '1.00');

                    if (status === 'FLYING') {
                        if (!this.flightStartMs) {
                            this.flightStartMs = Date.now() - Math.max(0, (mult - 1.00) / 0.40 * 1000);
                        }
                        const elapsedSec = Math.max(0, (Date.now() - this.flightStartMs) / 1000);
                        mult = 1.00 + elapsedSec * 0.40;
                        const multDisplay = document.getElementById('crashMultiplierText');
                        if (multDisplay) multDisplay.textContent = mult.toFixed(2) + 'x';
                    } else {
                        this.flightStartMs = null;
                    }

                    const progress = Math.min((mult - 1) / 10, 1);
                    this.renderer.renderFrame(progress, status);

                    if (this.userBet && this.userBet.status === 'flying' && status === 'FLYING') {
                        const cashoutBtn = document.getElementById('cashoutCrashBtn');
                        if (cashoutBtn) {
                            const winAmt = (parseFloat(String(this.userBet.bet_amount).replace(/,/g, '')) * mult).toFixed(2);
                            cashoutBtn.innerHTML = `<i class="bi bi-cash-stack me-2"></i>CASH OUT ₹${winAmt} (${mult.toFixed(2)}x)`;
                        }
                        this.updateActiveBetCard(this.userBet, mult.toFixed(2));
                    }
                }
            } catch (e) {
                // Safeguard against transient state sync glitches
            }
            this.animFrame = requestAnimationFrame(loop);
        };
        loop();
    }

    destroy() {
        if (this.wsManager) this.wsManager.destroy();
        if (this.syncTicker) clearInterval(this.syncTicker);
        if (this.animFrame) cancelAnimationFrame(this.animFrame);
    }
}

window.CrashGameManager = CrashGameManager;
