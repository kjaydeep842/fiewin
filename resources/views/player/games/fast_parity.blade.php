@extends('layouts.app')

@section('content')
<!-- Mode Switcher (30s Fast Parity vs 1m Parity) -->
<div class="d-flex justify-content-center gap-2 mb-3">
    <button id="btnMode30" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" onclick="switchGameMode(30)">
        <i class="bi bi-lightning-charge-fill me-1"></i>30s Fast Parity
    </button>
    <button id="btnMode60" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold shadow-sm" onclick="switchGameMode(60)">
        <i class="bi bi-clock-history me-1"></i>1m Parity
    </button>
</div>

<!-- Top Game Header -->
<div id="timerBoxContainer" class="gh-card p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('home') }}" class="btn btn-sm btn-light border rounded-circle"><i class="bi bi-arrow-left"></i></a>
            <div>
                <h6 class="fw-bold mb-0 text-dark" id="gameModeTitle">Fast Parity (30s)</h6>
                <small class="text-secondary" style="font-size: 0.72rem;">Period #<span id="periodNumber" class="fw-bold text-primary">{{ $currentPeriod }}</span></small>
            </div>
        </div>
        <div class="text-end">
            <small class="text-secondary d-block fw-semibold" style="font-size: 0.65rem;">COUNTDOWN</small>
            <div id="countdownTimer" class="fs-4 fw-bold text-danger font-monospace">00:30</div>
        </div>
    </div>
</div>

<!-- Last 5 Seconds Red Alert Ticker Banner -->
<div id="lastSecondsAlertBanner" class="gh-card p-2 mb-3 text-center d-none gh-red-alert-pulse" style="border: 2px solid #EF4444; background: rgba(239, 68, 68, 0.15);">
    <div class="d-flex align-items-center justify-content-center gap-2 text-danger fw-bold fs-6">
        <i class="bi bi-exclamation-triangle-fill fs-5 gh-timer-pulse"></i>
        <span>🚨 LAST 5 SECONDS! BETTING CLOSED 🚨</span>
    </div>
</div>

<!-- Last Period Live Result Banner -->
<div class="gh-card p-3 mb-3" style="background: linear-gradient(135deg, #1E88E5 0%, #1565C0 100%); color: #ffffff;">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <small class="opacity-75 d-block" style="font-size: 0.65rem;">LAST PERIOD RESULT</small>
            <div class="fw-bold text-white" style="font-size: 0.85rem;">Period #<span id="lastPeriodNumText">---</span></div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span id="lastResultNumBadge" class="display-6 fw-bold font-monospace bg-white text-dark rounded-circle px-3 py-1 shadow-sm">--</span>
            <div id="lastResultColorsBadge">
                <span class="badge bg-light text-dark">WAITING</span>
            </div>
        </div>
    </div>
</div>

<!-- Color Bet Buttons: Green (2X), Violet (4.5X), Red (2X) -->
<div class="row g-2 mb-3">
    <div class="col-4">
        <button class="btn gh-btn-success w-100 py-3 rounded-4 shadow-sm" onclick="selectBet('green')">
            <span class="d-block fw-bold fs-6">JOIN GREEN</span>
            <small class="d-block opacity-90 font-monospace" style="font-size: 0.7rem;">2X Payout</small>
        </button>
    </div>
    <div class="col-4">
        <button class="btn w-100 py-3 rounded-4 shadow-sm text-white" style="background: linear-gradient(135deg, #8B5CF6, #7C3AED);" onclick="selectBet('violet')">
            <span class="d-block fw-bold fs-6">JOIN VIOLET</span>
            <small class="d-block opacity-90 font-monospace" style="font-size: 0.7rem;">4.5X Payout</small>
        </button>
    </div>
    <div class="col-4">
        <button class="btn w-100 py-3 rounded-4 shadow-sm text-white" style="background: linear-gradient(135deg, #EF4444, #DC2626);" onclick="selectBet('red')">
            <span class="d-block fw-bold fs-6">JOIN RED</span>
            <small class="d-block opacity-90 font-monospace" style="font-size: 0.7rem;">2X Payout</small>
        </button>
    </div>
</div>

<!-- 0-9 Number Grid (9X Payout) -->
<div class="gh-card p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;"><i class="bi bi-grid-3x3-gap-fill text-primary me-1"></i>Select Number (9X Payout)</h6>
        <span class="badge bg-light text-primary border" style="font-size: 0.65rem;">9.0X MULTIPLIER</span>
    </div>
    <div class="row g-2">
        @for($num = 0; $num <= 9; $num++)
            @php
                $btnStyle = ($num == 0) ? 'border-danger text-danger' : (($num == 5) ? 'border-success text-success' : (($num % 2 == 1) ? 'border-success text-success' : 'border-danger text-danger'));
            @endphp
            <div class="col" style="flex: 0 0 20%; max-width: 20%;">
                <button class="btn btn-outline-secondary {{ $btnStyle }} w-100 py-2 rounded-3 fw-bold fs-5 shadow-sm" onclick="selectBet('{{ $num }}')">
                    {{ $num }}
                </button>
            </div>
        @endfor
    </div>
</div>

<!-- Modal Bet Confirmation -->
<div class="modal fade" id="betModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 350px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-light py-2">
                <h6 class="modal-title fw-bold text-dark" id="modalBetTypeTitle">Place Bet</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="ajaxBetForm" onsubmit="handleAjaxBetSubmit(event)">
                @csrf
                <input type="hidden" name="game_id" value="{{ $game->id }}">
                <input type="hidden" name="period_number" id="inputPeriodNumber" value="{{ $currentPeriod }}">
                <input type="hidden" name="bet_type" id="inputBetType" value="green">

                <div class="modal-body py-3">
                    <label class="form-label text-secondary small fw-semibold" style="font-size: 0.72rem;">PRESET AMOUNT (₹)</label>
                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm flex-fill rounded-pill py-1" onclick="setBetAmount(10)">₹10</button>
                        <button type="button" class="btn btn-outline-primary btn-sm flex-fill rounded-pill py-1" onclick="setBetAmount(100)">₹100</button>
                        <button type="button" class="btn btn-outline-primary btn-sm flex-fill rounded-pill py-1" onclick="setBetAmount(1000)">₹1000</button>
                        <button type="button" class="btn btn-outline-primary btn-sm flex-fill rounded-pill py-1" onclick="setBetAmount(10000)">₹10000</button>
                    </div>

                    <div class="input-group">
                        <span class="input-group-text bg-light fw-bold">₹</span>
                        <input type="number" name="bet_amount" id="inputBetAmount" class="form-control form-control-lg fw-bold" value="10" min="{{ $game->min_entry_fee }}" max="{{ $game->max_entry_fee }}" required>
                    </div>
                </div>
                <div class="modal-footer border-light py-2">
                    <button type="button" class="btn btn-light rounded-pill px-3 py-1 btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitBetBtn" class="btn gh-btn-success rounded-pill px-4 py-1 btn-sm">CONFIRM BET</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- WINNER RESULT POPUP MODAL (COMPACT RESPONSIVE) -->
<div class="modal fade" id="winResultModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow overflow-hidden">
            <div class="p-3 text-center text-white" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
                <div class="fs-1 mb-1"><i class="bi bi-trophy-fill text-warning"></i></div>
                <h5 class="fw-bold mb-0">CONGRATULATIONS!</h5>
                <small class="opacity-90" style="font-size: 0.72rem;">Period #<span id="winModalPeriod"></span></small>
            </div>
            <div class="modal-body text-center p-3">
                <div class="mb-2">
                    <small class="text-secondary d-block mb-1" style="font-size: 0.68rem;">WINNING NUMBER</small>
                    <span id="winModalNumber" class="fs-2 fw-bold text-dark font-monospace">0</span>
                    <div id="winModalColors" class="mt-1"></div>
                </div>
                <div class="p-2 bg-light rounded-3 border mb-3">
                    <small class="text-secondary d-block" style="font-size: 0.68rem;">YOUR WINNING AMOUNT</small>
                    <h3 id="winModalAmount" class="fw-extrabold text-success mb-0 font-monospace">+₹0.00</h3>
                </div>
                <button type="button" class="btn gh-btn-success w-100 py-2 fs-6 rounded-pill" data-bs-dismiss="modal">CONTINUE PLAYING</button>
            </div>
        </div>
    </div>
</div>

<!-- LOSER RESULT POPUP MODAL (COMPACT RESPONSIVE) -->
<div class="modal fade" id="lossResultModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow overflow-hidden">
            <div class="p-3 text-center text-white" style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);">
                <div class="fs-2 mb-1"><i class="bi bi-emoji-frown-fill"></i></div>
                <h5 class="fw-bold mb-0">BETTER LUCK NEXT TIME!</h5>
                <small class="opacity-90" style="font-size: 0.72rem;">Period #<span id="lossModalPeriod"></span> Result</small>
            </div>
            <div class="modal-body text-center p-3">
                <div class="mb-2">
                    <small class="text-secondary d-block mb-1" style="font-size: 0.68rem;">WINNING NUMBER</small>
                    <span id="lossModalNumber" class="fs-3 fw-bold text-dark font-monospace">0</span>
                    <div id="lossModalColors" class="mt-1"></div>
                </div>
                <div class="p-2 bg-light rounded-3 border mb-3" style="font-size: 0.8rem;">
                    <div class="d-flex justify-content-between text-secondary mb-1">
                        <span>YOUR BET</span>
                        <span id="lossModalBetType" class="fw-bold text-dark">GREEN</span>
                    </div>
                    <div class="d-flex justify-content-between text-secondary">
                        <span>AMOUNT</span>
                        <span id="lossModalBetAmount" class="fw-bold text-danger">₹0.00</span>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-danger w-100 py-2 fs-6 rounded-pill" data-bs-dismiss="modal">TRY AGAIN</button>
            </div>
        </div>
    </div>
</div>

<!-- History & My Bets Tabs -->
<div class="gh-card p-3">
    <ul class="nav nav-pills nav-fill mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-semibold py-1 rounded-pill" data-bs-toggle="pill" data-bs-target="#tabPeriodHistory">Period History</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold py-1 rounded-pill" data-bs-toggle="pill" data-bs-target="#tabMyOrders">My Orders</button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Period History Table -->
        <div class="tab-pane fade show active" id="tabPeriodHistory">
            <div class="table-responsive">
                <table class="table table-borderless table-sm align-middle text-center mb-0" style="font-size: 0.82rem;">
                    <thead class="text-secondary border-bottom">
                        <tr>
                            <th>Period</th>
                            <th>Number</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody id="periodHistoryTbody">
                        @foreach($history as $item)
                            @php
                                $data = $item->result_data ?? [];
                                $num = $data['number'] ?? 0;
                                $colors = $data['colors'] ?? ['green'];
                            @endphp
                            <tr class="border-bottom border-light">
                                <td class="font-monospace text-secondary">{{ $item->period_number }}</td>
                                <td class="fw-bold fs-6">{{ $num }}</td>
                                <td>
                                    @foreach($colors as $c)
                                        <span class="badge rounded-pill me-1 text-uppercase {{ $c === 'green' ? 'bg-success' : ($c === 'red' ? 'bg-danger' : 'bg-purple') }}" style="{{ $c === 'violet' ? 'background:#8B5CF6;' : '' }}">{{ $c }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- My Orders Table -->
        <div class="tab-pane fade" id="tabMyOrders">
            <div class="table-responsive">
                <table class="table table-borderless table-sm align-middle text-center mb-0" style="font-size: 0.82rem;">
                    <thead class="text-secondary border-bottom">
                        <tr>
                            <th>Period</th>
                            <th>Select</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="myBetsTbody">
                        @forelse($myBets as $b)
                            <tr class="border-bottom border-light">
                                <td class="font-monospace text-secondary">{{ $b->period_number }}</td>
                                <td><span class="badge bg-light text-dark border text-uppercase">{{ $b->bet_type }}</span></td>
                                <td class="fw-bold">₹{{ number_format($b->bet_amount, 2) }}</td>
                                <td>
                                    @if($b->status === 'won')
                                        <span class="badge bg-success">+₹{{ number_format($b->win_amount, 2) }}</span>
                                    @elseif($b->status === 'lost')
                                        <span class="badge bg-danger">LOST</span>
                                    @else
                                        <span class="badge bg-warning text-dark">PENDING</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-secondary py-3">No bets placed yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const betModal = new bootstrap.Modal(document.getElementById('betModal'));
    const winResultModal = new bootstrap.Modal(document.getElementById('winResultModal'));
    const lossResultModal = new bootstrap.Modal(document.getElementById('lossResultModal'));

    let activeInterval = 30; // Default 30 seconds mode
    let secondsLeft = 30;
    let currentPeriodNumber = "{{ $currentPeriod }}";

    function switchGameMode(modeInterval) {
        activeInterval = modeInterval;
        secondsLeft = activeInterval;

        const btn30 = document.getElementById('btnMode30');
        const btn60 = document.getElementById('btnMode60');
        const titleEl = document.getElementById('gameModeTitle');

        if (modeInterval === 30) {
            btn30.className = "btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm";
            btn60.className = "btn btn-outline-primary rounded-pill px-4 py-2 fw-bold shadow-sm";
            titleEl.innerText = "Fast Parity (30s)";
        } else {
            btn60.className = "btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm";
            btn30.className = "btn btn-outline-primary rounded-pill px-4 py-2 fw-bold shadow-sm";
            titleEl.innerText = "Parity (1m)";
        }

        updateTimerDisplay();
        fetchGameState();
    }

    function selectBet(type) {
        if (secondsLeft <= 5) {
            alert('⚠️ Betting is closed for the last 5 seconds of this period. Please wait for the next period!');
            return;
        }
        document.getElementById('inputBetType').value = type;
        document.getElementById('inputPeriodNumber').value = currentPeriodNumber;
        document.getElementById('modalBetTypeTitle').innerText = 'Place Bet on ' + type.toUpperCase();
        betModal.show();
    }

    function setBetAmount(val) {
        document.getElementById('inputBetAmount').value = val;
    }

    // AJAX Bet Submission
    async function handleAjaxBetSubmit(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('submitBetBtn');
        submitBtn.disabled = true;
        submitBtn.innerText = 'PLACING...';

        const formData = new FormData(document.getElementById('ajaxBetForm'));

        try {
            const response = await fetch("{{ route('games.bet') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                betModal.hide();
                
                // Update top wallet balance live
                updateTopWalletBalance(data.new_balance);

                // Instantly poll state to update My Orders table
                fetchGameState();
            } else {
                alert(data.message || 'Error placing bet');
            }
        } catch (err) {
            console.error('Bet submit error:', err);
            alert('Server error while placing bet');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'CONFIRM BET';
        }
    }

    function updateTopWalletBalance(balStr) {
        const topBalanceEls = document.querySelectorAll('.font-monospace');
        topBalanceEls.forEach(el => {
            if (el.innerText.includes('₹')) {
                el.innerText = '₹' + balStr;
            }
        });
    }

    // Record the EXACT time this page was loaded.
    // Result popups will ONLY fire for bets settled AFTER this timestamp.
    // This prevents stale results from previous sessions showing on page open.
    const SESSION_START_TIME = Math.floor(Date.now() / 1000); // Unix seconds

    // Live Synchronized Game State Polling
    async function fetchGameState() {
        try {
            const url = "{{ route('games.state', 'fast_parity') }}?interval=" + activeInterval;
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            if (!data.success) return;

            // Sync period number
            currentPeriodNumber = data.current_period;
            document.getElementById('periodNumber').innerText = currentPeriodNumber;
            document.getElementById('inputPeriodNumber').value = currentPeriodNumber;

            // Sync remaining seconds if drift > 3s or initial sync
            if (Math.abs(secondsLeft - data.seconds_remaining) > 3 || secondsLeft <= 0) {
                secondsLeft = data.seconds_remaining;
            }

            // Update user balance live
            if (data.user_balance) {
                updateTopWalletBalance(data.user_balance);
            }

            // Update Last Period Result Banner
            if (data.last_result) {
                document.getElementById('lastPeriodNumText').innerText = data.last_result.period_number;
                document.getElementById('lastResultNumBadge').innerText = data.last_result.number;

                const colorsBadgeDiv = document.getElementById('lastResultColorsBadge');
                colorsBadgeDiv.innerHTML = '';
                if (data.last_result.colors) {
                    data.last_result.colors.forEach(c => {
                        colorsBadgeDiv.innerHTML += `<span class="badge rounded-pill me-1 text-uppercase ${c === 'green' ? 'bg-success' : (c === 'red' ? 'bg-danger' : 'bg-purple')}" style="${c === 'violet' ? 'background:#8B5CF6;' : ''}">${c}</span>`;
                    });
                }
            }

            // ── Result Popup Guard ────────────────────────────────────────────────
            // Only show win/loss modal if:
            //   1. The player actually has a settled bet in this response
            //   2. That bet was settled AFTER this page was loaded (SESSION_START_TIME)
            //   3. We haven't already shown a popup for this specific bet ID this session
            // Using sessionStorage (cleared on tab close) prevents ghost popups on refresh.
            if (data.user_latest_settled_bet) {
                const bet          = data.user_latest_settled_bet;
                const betSettledAt = bet.settled_at_unix || 0;
                const isNewResult  = betSettledAt >= SESSION_START_TIME;

                let shownBetIds = JSON.parse(sessionStorage.getItem('shown_bet_popups') || '[]');

                if (isNewResult && !shownBetIds.includes(bet.id)) {
                    shownBetIds.push(bet.id);
                    sessionStorage.setItem('shown_bet_popups', JSON.stringify(shownBetIds));

                    if (bet.status === 'won') {
                        showWinPopup(bet);
                    } else if (bet.status === 'lost') {
                        showLossPopup(bet);
                    }
                }
            }
            // ─────────────────────────────────────────────────────────────────────

            // Update Period History Table
            renderPeriodHistory(data.history);

            // Update My Orders Table
            renderMyBets(data.my_bets);

        } catch (err) {
            console.error('Game state poll error:', err);
        }
    }

    function showWinPopup(bet) {
        document.getElementById('winModalPeriod').innerText = bet.period_number;
        document.getElementById('winModalNumber').innerText = bet.winning_number;
        document.getElementById('winModalAmount').innerText = '+₹' + bet.win_amount;

        const colorsDiv = document.getElementById('winModalColors');
        colorsDiv.innerHTML = '';
        if (bet.winning_colors) {
            bet.winning_colors.forEach(c => {
                colorsDiv.innerHTML += `<span class="badge rounded-pill me-1 text-uppercase ${c === 'green' ? 'bg-success' : (c === 'red' ? 'bg-danger' : 'bg-purple')}" style="${c === 'violet' ? 'background:#8B5CF6;' : ''}">${c}</span>`;
            });
        }

        winResultModal.show();
    }

    function showLossPopup(bet) {
        document.getElementById('lossModalPeriod').innerText = bet.period_number;
        document.getElementById('lossModalNumber').innerText = bet.winning_number;
        document.getElementById('lossModalBetType').innerText = bet.bet_type;
        document.getElementById('lossModalBetAmount').innerText = '₹' + bet.bet_amount;

        const colorsDiv = document.getElementById('lossModalColors');
        colorsDiv.innerHTML = '';
        if (bet.winning_colors) {
            bet.winning_colors.forEach(c => {
                colorsDiv.innerHTML += `<span class="badge rounded-pill me-1 text-uppercase ${c === 'green' ? 'bg-success' : (c === 'red' ? 'bg-danger' : 'bg-purple')}" style="${c === 'violet' ? 'background:#8B5CF6;' : ''}">${c}</span>`;
            });
        }

        lossResultModal.show();
    }

    function renderPeriodHistory(historyList) {
        const tbody = document.getElementById('periodHistoryTbody');
        if (!tbody || !historyList) return;

        let html = '';
        historyList.forEach(item => {
            let colorBadges = '';
            item.colors.forEach(c => {
                colorBadges += `<span class="badge rounded-pill me-1 text-uppercase ${c === 'green' ? 'bg-success' : (c === 'red' ? 'bg-danger' : 'bg-purple')}" style="${c === 'violet' ? 'background:#8B5CF6;' : ''}">${c}</span>`;
            });

            html += `<tr class="border-bottom border-light">
                <td class="font-monospace text-secondary">${item.period_number}</td>
                <td class="fw-bold fs-6">${item.number}</td>
                <td>${colorBadges}</td>
            </tr>`;
        });
        tbody.innerHTML = html;
    }

    function renderMyBets(betsList) {
        const tbody = document.getElementById('myBetsTbody');
        if (!tbody || !betsList) return;

        if (betsList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-secondary py-3">No bets placed yet</td></tr>';
            return;
        }

        let html = '';
        betsList.forEach(b => {
            let statusBadge = '';
            if (b.status === 'won') {
                statusBadge = `<span class="badge bg-success">+₹${b.win_amount}</span>`;
            } else if (b.status === 'lost') {
                statusBadge = `<span class="badge bg-danger">LOST</span>`;
            } else {
                statusBadge = `<span class="badge bg-warning text-dark">PENDING</span>`;
            }

            html += `<tr class="border-bottom border-light">
                <td class="font-monospace text-secondary">${b.period_number}</td>
                <td><span class="badge bg-light text-dark border text-uppercase">${b.bet_type}</span></td>
                <td class="fw-bold">₹${b.bet_amount}</td>
                <td>${statusBadge}</td>
            </tr>`;
        });
        tbody.innerHTML = html;
    }

    function updateTimerDisplay() {
        let mins = Math.floor(secondsLeft / 60);
        let secs = secondsLeft % 60;
        let minStr = mins < 10 ? '0' + mins : mins;
        let secStr = secs < 10 ? '0' + secs : secs;
        
        const timerEl = document.getElementById('countdownTimer');
        const timerBox = document.getElementById('timerBoxContainer');
        const alertBanner = document.getElementById('lastSecondsAlertBanner');
        const betBtns = document.querySelectorAll('button[onclick^="selectBet"]');

        timerEl.className = 'font-monospace d-inline-block transition-all';

        if (secondsLeft === 0) {
            // REACHED 0: CALCULATING RESULT
            timerEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>CALCULATING...';
            timerEl.className = 'font-monospace text-warning fs-6 fw-bold';
            if (timerBox) timerBox.className = 'gh-card p-3 mb-3 border-warning bg-warning bg-opacity-10';
            if (alertBanner) alertBanner.classList.add('d-none');
            return;
        }

        if (secondsLeft <= 5 && secondsLeft > 0) {
            // STAGE 3: LAST 5 SECONDS (CRITICAL RED ALERT & VIBRATE & FAST TICK)
            timerEl.innerText = minStr + ':' + secStr;
            timerEl.classList.add('timer-critical-5s');
            if (timerBox) timerBox.className = 'gh-card p-3 mb-3 gh-red-alert-pulse';
            if (alertBanner) alertBanner.classList.remove('d-none');

            // Disable bet buttons
            betBtns.forEach(btn => {
                btn.disabled = true;
                btn.classList.add('opacity-50');
            });

            // Auto close modal if open
            const modalEl = document.getElementById('betModal');
            if (modalEl && modalEl.classList.contains('show')) {
                betModal.hide();
            }

            if (window.soundManager) window.soundManager.play('fastTick');
            if (navigator.vibrate) navigator.vibrate([80, 40, 80]);

        } else if (secondsLeft <= 10 && secondsLeft > 5) {
            // STAGE 2: LAST 10 SECONDS (RED HEARTBEAT & WARNING TICK)
            timerEl.innerText = minStr + ':' + secStr;
            timerEl.classList.add('timer-warning-10s');
            if (timerBox) timerBox.className = 'gh-card p-3 mb-3 border-danger shadow-sm';
            if (alertBanner) alertBanner.classList.add('d-none');

            betBtns.forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('opacity-50');
            });

            if (window.soundManager) window.soundManager.play('tick');

        } else {
            // STAGE 1: NORMAL TIME (>10s GREEN PULSE)
            timerEl.innerText = minStr + ':' + secStr;
            timerEl.classList.add('timer-normal');
            if (timerBox) timerBox.className = 'gh-card p-3 mb-3';
            if (alertBanner) alertBanner.classList.add('d-none');

            betBtns.forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('opacity-50');
            });
        }
    }

    function showWinPopup(bet) {
        document.getElementById('winModalPeriod').innerText = bet.period_number;
        document.getElementById('winModalNumber').innerText = bet.winning_number;
        document.getElementById('winModalAmount').innerText = '+₹' + bet.win_amount;

        const colorsDiv = document.getElementById('winModalColors');
        colorsDiv.innerHTML = '';
        if (bet.winning_colors) {
            bet.winning_colors.forEach(c => {
                colorsDiv.innerHTML += `<span class="badge rounded-pill me-1 text-uppercase ${c === 'green' ? 'bg-success' : (c === 'red' ? 'bg-danger' : 'bg-purple')}" style="${c === 'violet' ? 'background:#8B5CF6;' : ''}">${c}</span>`;
            });
        }

        winResultModal.show();
        if (window.soundManager) window.soundManager.play('win');
        if (window.animationManager) {
            window.animationManager.triggerConfetti(70);
            window.animationManager.animateCoinsToWallet(document.getElementById('winResultModal'));
        }
    }

    function showLossPopup(bet) {
        document.getElementById('lossModalPeriod').innerText = bet.period_number;
        document.getElementById('lossModalNumber').innerText = bet.winning_number;
        document.getElementById('lossModalBetType').innerText = bet.bet_type;
        document.getElementById('lossModalBetAmount').innerText = '₹' + bet.bet_amount;

        const colorsDiv = document.getElementById('lossModalColors');
        colorsDiv.innerHTML = '';
        if (bet.winning_colors) {
            bet.winning_colors.forEach(c => {
                colorsDiv.innerHTML += `<span class="badge rounded-pill me-1 text-uppercase ${c === 'green' ? 'bg-success' : (c === 'red' ? 'bg-danger' : 'bg-purple')}" style="${c === 'violet' ? 'background:#8B5CF6;' : ''}">${c}</span>`;
            });
        }

        lossResultModal.show();
        if (window.soundManager) window.soundManager.play('lose');
        if (window.animationManager) window.animationManager.shakeScreen();
    }

    // Countdown Timer Loop (1 sec interval)
    setInterval(() => {
        secondsLeft--;
        if (secondsLeft <= 0) {
            secondsLeft = activeInterval;
            fetchGameState(); // Poll immediately at boundary
        }
        updateTimerDisplay();
    }, 1000);

    // Initial fetch on page load
    fetchGameState();
    // Continuous background polling every 2 seconds
    setInterval(fetchGameState, 2000);
</script>
@endpush
@endsection
