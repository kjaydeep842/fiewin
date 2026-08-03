@extends('layouts.app')

@section('content')
<style>
    .ab-container {
        background: linear-gradient(180deg, #0F172A 0%, #1E293B 100%);
        color: #F8FAFC;
        border-radius: 20px;
        padding: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    .ab-header-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 12px 16px;
    }
    .ab-timer-digit {
        font-family: 'Courier New', Courier, monospace;
        font-size: 1.5rem;
        font-weight: 800;
        color: #38BDF8;
    }
    .ab-timer-warning {
        color: #EF4444 !important;
        animation: pulse 1s infinite;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    /* Card Design & Hardware-Accelerated Deal Animation */
    @keyframes cardDealSlideIn {
        0% { opacity: 0; transform: translateY(-25px) scale(0.6) rotate(-8deg); }
        100% { opacity: 1; transform: translateY(0) scale(1) rotate(0deg); }
    }
    .card-slide-deal {
        animation: cardDealSlideIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        will-change: transform, opacity;
    }
    .playing-card {
        width: 64px;
        height: 90px;
        background: #FFFFFF;
        border-radius: 8px;
        border: 2px solid #CBD5E1;
        display: inline-flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 4px 6px;
        font-weight: 800;
        font-size: 1.1rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        transition: all 0.3s ease;
        user-select: none;
    }
    .mini-deal-card {
        width: 40px;
        height: 56px;
        background: #FFFFFF;
        border-radius: 6px;
        border: 1.5px solid #CBD5E1;
        display: inline-flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 2px 4px;
        font-weight: 800;
        font-size: 0.78rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.25);
        user-select: none;
        position: relative;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .mini-deal-card.red, .playing-card.red { color: #DC2626; }
    .mini-deal-card.black, .playing-card.black { color: #0F172A; }
    
    .mini-deal-card.card-matching-win {
        border: 2.5px solid #F59E0B !important;
        background: #FEF3C7 !important;
        box-shadow: 0 0 16px #F59E0B, 0 0 6px #F59E0B !important;
        transform: scale(1.15);
        z-index: 10;
        animation: pulseMatchingCard 0.6s infinite alternate;
    }
    @keyframes pulseMatchingCard {
        0% { transform: scale(1.12); box-shadow: 0 0 10px #F59E0B; }
        100% { transform: scale(1.22); box-shadow: 0 0 22px #F59E0B; }
    }
    
    .card-matched-badge {
        position: absolute;
        top: -8px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #F59E0B, #D97706);
        color: #FFF;
        font-size: 0.55rem;
        font-weight: 900;
        padding: 1px 4px;
        border-radius: 4px;
        white-space: nowrap;
        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    .playing-card-lg {
        width: 80px;
        height: 112px;
        font-size: 1.4rem;
    }
    .card-suit-center {
        font-size: 1.6rem;
        text-align: center;
        margin-top: -6px;
    }

    /* Dealing Table Stage */
    .ab-dealing-stage {
        background: rgba(15, 23, 42, 0.6);
        border: 2px dashed rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        padding: 14px;
        min-height: 140px;
    }
    .side-box {
        border-radius: 12px;
        padding: 10px;
        min-height: 100px;
        transition: all 0.3s ease;
    }
    .side-box-andar {
        background: rgba(30, 136, 229, 0.15);
        border: 1.5px solid #1E88E5;
    }
    .side-box-bahar {
        background: rgba(239, 68, 68, 0.15);
        border: 1.5px solid #EF4444;
    }
    .side-box-tie {
        background: rgba(245, 158, 11, 0.15);
        border: 1.5px solid #F59E0B;
    }
    .side-box.winner-glow {
        box-shadow: 0 0 25px rgba(34, 197, 94, 0.8);
        border-color: #22C55E !important;
        transform: scale(1.03);
    }

    /* Betting Action Buttons */
    .btn-ab-andar {
        background: linear-gradient(135deg, #1E88E5 0%, #1565C0 100%);
        color: #FFFFFF;
        border: none;
        border-radius: 16px;
        padding: 16px;
        font-weight: 700;
        box-shadow: 0 6px 16px rgba(30, 136, 229, 0.4);
        transition: all 0.2s ease;
    }
    .btn-ab-tie {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        color: #FFFFFF;
        border: none;
        border-radius: 16px;
        padding: 16px;
        font-weight: 700;
        box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
        transition: all 0.2s ease;
    }
    .btn-ab-bahar {
        background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
        color: #FFFFFF;
        border: none;
        border-radius: 16px;
        padding: 16px;
        font-weight: 700;
        box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
        transition: all 0.2s ease;
    }
    .btn-ab-andar:hover, .btn-ab-tie:hover, .btn-ab-bahar:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
    }

    /* Record Badges */
    .ab-badge-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.85rem;
        color: #FFF;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
    .badge-andar { background: #1E88E5; }
    .badge-bahar { background: #EF4444; }
    .badge-tie   { background: #F59E0B; }

    /* Bottom Sheet Modal */
    .bottom-sheet-modal .modal-dialog {
        margin: 0;
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%) !important;
        width: 100%;
        max-width: 480px;
    }
    .bottom-sheet-modal .modal-content {
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
        border-top-left-radius: 24px;
        border-top-right-radius: 24px;
        background: #1E293B;
        color: #F8FAFC;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .chip-btn {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.15);
        color: #F8FAFC;
        border-radius: 12px;
        padding: 8px 12px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }
    .chip-btn.active, .chip-btn:hover {
        background: #1E88E5;
        border-color: #1E88E5;
        color: #FFF;
    }
</style>

<div class="ab-container mb-4">
    <!-- Top Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('home') }}" class="btn btn-sm btn-outline-light rounded-circle"><i class="bi bi-arrow-left"></i></a>
            <div>
                <h5 class="fw-bold mb-0 text-white"><i class="bi bi-suit-spade-fill text-primary me-1"></i>Andar Bahar</h5>
                <small class="text-secondary" style="font-size: 0.72rem;">Period #<span id="displayPeriod" class="fw-bold text-info">{{ $currentRound->period_number }}</span></small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-info rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#rulesModal">
                <i class="bi bi-info-circle me-1"></i>Rules
            </button>
        </div>
    </div>

    <!-- Countdown & Open Card Section -->
    <div class="ab-header-card mb-3">
        <div class="row align-items-center">
            <div class="col-7">
                <small class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.5px;">COUNTDOWN TIMER</small>
                <div id="countdownDisplay" class="ab-timer-digit">00:59</div>
                <small id="bettingStatusBadge" class="badge bg-success mt-1">BETTING OPEN</small>
            </div>
            <div class="col-5 text-end">
                <small class="text-secondary d-block text-uppercase fw-semibold mb-1" style="font-size: 0.65rem;">OPEN CARD</small>
                <div id="openCardContainer" class="d-inline-block">
                    @php
                        $isRed = (str_contains($currentRound->open_card, '♥') || str_contains($currentRound->open_card, '♦'));
                        $suit = mb_substr($currentRound->open_card, -1);
                        $rank = mb_substr($currentRound->open_card, 0, -1);
                    @endphp
                    <div class="playing-card playing-card-lg {{ $isRed ? 'red' : 'black' }} mx-auto shadow">
                        <div>{{ $rank }}</div>
                        <div class="card-suit-center">{{ $suit }}</div>
                        <div class="text-end">{{ $rank }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dealing Table Stage -->
    <div class="ab-dealing-stage mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <small class="text-secondary fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">
                <i class="bi bi-play-circle-fill text-warning me-1"></i>LIVE DEALING TABLE
            </small>
            <div id="dealStatusText" class="badge bg-secondary bg-opacity-50 text-secondary border border-secondary" style="font-size: 0.65rem;">
                WAITING FOR BETS
            </div>
        </div>
        <div class="row g-2">
            <!-- Andar Dealing Box -->
            <div class="col-6">
                <div id="sideBoxAndar" class="side-box side-box-andar text-center position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-info" style="font-size: 0.78rem;">ANDAR (LEFT)</span>
                        <span id="countAndarBadge" class="badge bg-info bg-opacity-20 text-info border border-info" style="font-size: 0.62rem;">0 Cards</span>
                    </div>
                    <div id="cardsAndar" class="d-flex flex-wrap justify-content-center gap-1" style="min-height: 60px;">
                        <small class="text-secondary opacity-75 my-3" id="andarPlaceholder">Waiting for deal...</small>
                    </div>
                </div>
            </div>
            <!-- Bahar Dealing Box -->
            <div class="col-6">
                <div id="sideBoxBahar" class="side-box side-box-bahar text-center position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-danger" style="font-size: 0.78rem;">BAHAR (RIGHT)</span>
                        <span id="countBaharBadge" class="badge bg-danger bg-opacity-20 text-danger border border-danger" style="font-size: 0.62rem;">0 Cards</span>
                    </div>
                    <div id="cardsBahar" class="d-flex flex-wrap justify-content-center gap-1" style="min-height: 60px;">
                        <small class="text-secondary opacity-75 my-3" id="baharPlaceholder">Waiting for deal...</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Betting Action Buttons -->
    <div class="row g-2 mb-3">
        <div class="col-4">
            <button id="btnBetAndar" class="btn btn-ab-andar w-100" onclick="openBetModal('andar')">
                <span class="d-block fs-6 fw-bold">ANDAR</span>
                <small class="d-block opacity-90 font-monospace" style="font-size: 0.72rem;">2.0X Payout</small>
            </button>
        </div>
        <div class="col-4">
            <button id="btnBetTie" class="btn btn-ab-tie w-100" onclick="openBetModal('tie')">
                <span class="d-block fs-6 fw-bold">TIE</span>
                <small class="d-block opacity-90 font-monospace" style="font-size: 0.72rem;">9.0X Payout</small>
            </button>
        </div>
        <div class="col-4">
            <button id="btnBetBahar" class="btn btn-ab-bahar w-100" onclick="openBetModal('bahar')">
                <span class="d-block fs-6 fw-bold">BAHAR</span>
                <small class="d-block opacity-90 font-monospace" style="font-size: 0.72rem;">2.0X Payout</small>
            </button>
        </div>
    </div>

    <!-- Record Panel (Recent 30 Rounds) -->
    <div class="ab-header-card mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0 text-white" style="font-size: 0.85rem;"><i class="bi bi-clock-history text-warning me-1"></i>Record History (Recent 30)</h6>
            <button class="btn btn-xs btn-outline-light rounded-pill px-2 py-0" style="font-size: 0.7rem;" onclick="fetchFullHistory()">More ></button>
        </div>
        <div id="recentRecordRow" class="d-flex gap-2 overflow-x-auto pb-1" style="scrollbar-width: thin;">
            @foreach($recentResults as $res)
                @php
                    $badgeClass = match($res->winner) {
                        'andar' => 'badge-andar',
                        'bahar' => 'badge-bahar',
                        'tie' => 'badge-tie',
                        default => 'badge-andar'
                    };
                    $shortLetter = match($res->winner) {
                        'andar' => 'A',
                        'bahar' => 'B',
                        'tie' => 'T',
                        default => 'A'
                    };
                @endphp
                <div class="text-center flex-shrink-0">
                    <span class="ab-badge-circle {{ $badgeClass }}">{{ $shortLetter }}</span>
                    <small class="d-block text-secondary mt-1 font-monospace" style="font-size: 0.62rem;">{{ substr($res->period_number, -4) }}</small>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Order Tabs: My Order & Everyone's Order -->
    <div class="ab-header-card">
        <ul class="nav nav-pills nav-fill mb-3" id="orderTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold py-2" id="my-orders-tab" data-bs-toggle="tab" data-bs-target="#my-orders" type="button" role="tab">
                    <i class="bi bi-person-fill me-1"></i>My Order
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold py-2" id="everyone-orders-tab" data-bs-toggle="tab" data-bs-target="#everyone-orders" type="button" role="tab">
                    <i class="bi bi-people-fill me-1"></i>Everyone's Order
                </button>
            </li>
        </ul>

        <div class="tab-content" id="orderTabContent">
            <!-- My Orders Tab -->
            <div class="tab-pane fade show active" id="my-orders" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-borderless align-middle mb-0" style="font-size: 0.78rem;">
                        <thead>
                            <tr class="text-secondary border-bottom border-secondary">
                                <th>Period</th>
                                <th>Option</th>
                                <th>Amount</th>
                                <th>Result</th>
                                <th>Payout</th>
                            </tr>
                        </thead>
                        <tbody id="myOrdersTableBody">
                            @forelse($myBets as $b)
                                <tr>
                                    <td class="fw-bold font-monospace">{{ $b->period_number }}</td>
                                    <td>
                                        <span class="badge {{ $b->bet_option === 'andar' ? 'bg-primary' : ($b->bet_option === 'bahar' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                            {{ strtoupper($b->bet_option) }}
                                        </span>
                                    </td>
                                    <td class="fw-bold">₹{{ number_format($b->bet_amount, 2) }}</td>
                                    <td>
                                        @if($b->status === 'pending')
                                            <span class="badge bg-secondary">PENDING</span>
                                        @elseif($b->status === 'won')
                                            <span class="badge bg-success">WON</span>
                                        @else
                                            <span class="badge bg-danger">LOST</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold {{ $b->win_amount > 0 ? 'text-success' : 'text-secondary' }}">
                                        ₹{{ number_format($b->win_amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-3">No bet orders placed yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Everyone's Orders Tab -->
            <div class="tab-pane fade" id="everyone-orders" role="tabpanel">
                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-dark table-hover table-borderless align-middle mb-0" style="font-size: 0.78rem;">
                        <thead>
                            <tr class="text-secondary border-bottom border-secondary">
                                <th>User</th>
                                <th>Selection</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="everyoneOrdersTableBody">
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-3">Loading live bets...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Sheet Bet Modal -->
<div class="modal fade bottom-sheet-modal" id="betModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title fw-bold text-white" id="modalBetTitle">Confirm Bet</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="betForm" onsubmit="submitBet(event)">
                    <input type="hidden" id="selectedBetOption" name="bet_option">

                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded bg-black bg-opacity-25">
                        <span class="text-secondary font-monospace">Wallet Balance:</span>
                        <span class="fw-bold text-success fs-6">₹<span id="modalWalletBalance">0.00</span></span>
                    </div>

                    <!-- Quick Chips -->
                    <label class="form-label text-secondary fw-semibold mb-2" style="font-size: 0.75rem;">QUICK CHIPS</label>
                    <div class="d-flex flex-wrap gap-1 mb-3">
                        @foreach([10, 20, 50, 100, 200, 500, 1000, 2000, 5000] as $chip)
                            <button type="button" class="chip-btn flex-fill" onclick="selectChip({{ $chip }})">+₹{{ $chip }}</button>
                        @endforeach
                    </div>

                    <!-- Multiplier Buttons -->
                    <label class="form-label text-secondary fw-semibold mb-2" style="font-size: 0.75rem;">MULTIPLIER</label>
                    <div class="d-flex gap-1 mb-3">
                        @foreach([1, 5, 10, 20, 50] as $mult)
                            <button type="button" class="chip-btn flex-fill" onclick="applyMultiplier({{ $mult }})">X{{ $mult }}</button>
                        @endforeach
                    </div>

                    <!-- Amount Input -->
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-semibold" style="font-size: 0.75rem;">TOTAL BET AMOUNT (₹)</label>
                        <input type="number" id="inputBetAmount" name="amount" class="form-control form-control-lg bg-dark text-white border-secondary fw-bold" value="10" min="1" step="1" oninput="updatePotentialWin()">
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-secondary font-monospace" style="font-size: 0.8rem;">Potential Win:</span>
                        <span class="fw-bold text-warning fs-5">₹<span id="potentialWinAmount">20.00</span></span>
                    </div>

                    <button type="submit" id="btnConfirmBet" class="btn btn-primary w-100 py-3 rounded-3 fw-bold fs-6 shadow">
                        CONFIRM BET
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Rules Modal -->
<div class="modal fade" id="rulesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white rounded-4 border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold"><i class="bi bi-book text-info me-2"></i>Andar Bahar Game Rules</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-secondary" style="font-size: 0.85rem; line-height: 1.6;">
                <p><strong class="text-white">Objective:</strong> An open card is revealed at the start of each round. Predict whether the card with the <span class="text-warning">matching rank</span> will land on <strong>ANDAR</strong> or <strong>BAHAR</strong>.</p>
                <ul>
                    <li><strong class="text-info">ANDAR (2X):</strong> Cards deal alternately starting from Andar. Pays 2.0x payout if the matching rank card lands on Andar.</li>
                    <li><strong class="text-danger">BAHAR (2X):</strong> Pays 2.0x payout if the matching rank card lands on Bahar.</li>
                    <li><strong class="text-warning">TIE (9X):</strong> Pays 9.0x payout if the matching rank card lands on both or tie rule condition is met.</li>
                </ul>
                <p class="mb-0"><strong class="text-white">Timer:</strong> Betting is open for the first 45 seconds of each 60-second round. The final 15 seconds are reserved for card dealing animation and automatic settlement.</p>
            </div>
        </div>
    </div>
</div>

<!-- Full History Modal -->
<div class="modal fade" id="fullHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content bg-dark text-white rounded-4 border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold"><i class="bi bi-clock-history text-warning me-2"></i>Complete Round History</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0" style="font-size: 0.8rem;">
                        <thead>
                            <tr class="text-secondary border-bottom border-secondary">
                                <th>Period</th>
                                <th>Open Card</th>
                                <th>Winner</th>
                                <th>Matching Card</th>
                                <th>Deals</th>
                            </tr>
                        </thead>
                        <tbody id="fullHistoryTableBody">
                            <tr>
                                <td colspan="5" class="text-center py-3 text-secondary">Loading history...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Round Result Winner Modal -->
<div class="modal fade" id="roundResultModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow bg-dark text-white overflow-hidden text-center">
            <div id="resultModalHeader" class="p-3 text-white" style="background: linear-gradient(135deg, #1E88E5, #1565C0);">
                <div id="resultModalIcon" class="fs-1 mb-1"><i class="bi bi-trophy-fill text-warning"></i></div>
                <h5 id="resultModalTitle" class="fw-bold mb-0">ANDAR WON!</h5>
            </div>
            <div class="modal-body p-3">
                <p id="resultModalSubtext" class="text-secondary small mb-2">
                    Matching Card <span id="resultModalWinningCard" class="fw-bold text-warning fs-6">4♣</span> landed on <strong id="resultModalWinnerSide" class="text-info">ANDAR</strong>!
                </p>
                <div id="resultModalAmountBox" class="p-2 bg-dark border border-success rounded-3 mb-3">
                    <small class="text-secondary d-block">TOTAL WINNINGS</small>
                    <h3 id="resultModalAmount" class="fw-bold text-success mb-0 font-monospace">+₹0.00</h3>
                </div>
                <button type="button" class="btn btn-primary w-100 py-2 rounded-pill fw-bold" data-bs-dismiss="modal">
                    CONTINUE
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentOdds = { andar: 2.0, bahar: 2.0, tie: 9.0 };
    let selectedOption = 'andar';
    let isDealingAnimated = false;
    let lastHistoryCache = '';
    let lastMyOrdersCache = '';
    let lastEveryoneOrdersCache = '';
    let lastOpenCardCache = '';

    document.addEventListener('DOMContentLoaded', function() {
        fetchGameState();
        setInterval(fetchGameState, 1000);
    });

    function fetchGameState() {
        fetch("{{ route('games.andar-bahar.state') }}")
            .then(res => res.json())
            .then(data => {
                if (!data.status) return;

                // Update Period & Timer
                document.getElementById('displayPeriod').innerText = data.period;
                let minutes = Math.floor(data.countdown / 60);
                let seconds = data.countdown % 60;
                let formattedTime = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                
                let timerElem = document.getElementById('countdownDisplay');
                timerElem.innerText = formattedTime;

                let statusBadge = document.getElementById('bettingStatusBadge');

                if (data.countdown <= 10 && data.countdown > 0) {
                    timerElem.classList.add('ab-timer-warning');
                    statusBadge.className = 'badge bg-danger mt-1';
                    statusBadge.innerText = 'BETTING CLOSED';
                    toggleBetButtons(false);
                } else if (data.countdown === 0) {
                    timerElem.classList.add('ab-timer-warning');
                    statusBadge.className = 'badge bg-danger mt-1';
                    statusBadge.innerText = 'BETTING CLOSED - DEALING CARDS';
                    toggleBetButtons(false);
                } else {
                    timerElem.classList.remove('ab-timer-warning');
                    statusBadge.className = 'badge bg-success mt-1';
                    statusBadge.innerText = 'BETTING OPEN';
                    toggleBetButtons(true);
                }

                // Update Open Card if changed
                renderOpenCard(data.open_card);

                // Update Wallet
                document.getElementById('modalWalletBalance').innerText = data.wallet;

                // Update Odds
                if (data.settings) {
                    currentOdds.andar = data.settings.andar_odds || 2.0;
                    currentOdds.bahar = data.settings.bahar_odds || 2.0;
                    currentOdds.tie = data.settings.tie_odds || 9.0;
                }

                // Render Recent Record Badges
                renderRecordBadges(data.history);

                // Render Orders
                renderMyOrders(data.my_orders);
                renderEveryoneOrders(data.everyone_orders);

                // Animate Dealing ONLY when countdown reaches 0 (second is 0)!
                if (data.countdown === 0 && data.last_result && !isDealingAnimated) {
                    animateDealingSequence(data.last_result);
                } else if (data.countdown > 0) {
                    isDealingAnimated = false; // Reset for next round
                    if (currentDealInterval) {
                        clearInterval(currentDealInterval);
                        currentDealInterval = null;
                    }
                    resetDealingStage();
                }
            })
            .catch(err => console.error('Error fetching game state:', err));
    }

    function renderOpenCard(cardStr) {
        if (!cardStr || cardStr === lastOpenCardCache) return;
        lastOpenCardCache = cardStr;
        let suit = cardStr.slice(-1);
        let rank = cardStr.slice(0, -1);
        let isRed = (suit === '♥' || suit === '♦');
        
        let container = document.getElementById('openCardContainer');
        container.innerHTML = `
            <div class="playing-card playing-card-lg ${isRed ? 'red' : 'black'} mx-auto shadow">
                <div>${rank}</div>
                <div class="card-suit-center">${suit}</div>
                <div class="text-end">${rank}</div>
            </div>
        `;
    }

    function toggleBetButtons(enabled) {
        ['btnBetAndar', 'btnBetTie', 'btnBetBahar'].forEach(id => {
            let btn = document.getElementById(id);
            if (btn) {
                btn.disabled = !enabled;
                btn.style.opacity = enabled ? '1' : '0.5';
            }
        });
    }

    function openBetModal(option) {
        selectedOption = option;
        document.getElementById('selectedBetOption').value = option;
        let title = 'Bet on ' + option.toUpperCase() + ' (' + currentOdds[option] + 'X)';
        document.getElementById('modalBetTitle').innerText = title;
        updatePotentialWin();
        let modal = new bootstrap.Modal(document.getElementById('betModal'));
        modal.show();
    }

    function selectChip(amount) {
        let input = document.getElementById('inputBetAmount');
        input.value = (parseFloat(input.value || 0) + amount);
        updatePotentialWin();
    }

    function applyMultiplier(mult) {
        let input = document.getElementById('inputBetAmount');
        input.value = (parseFloat(input.value || 10) * mult);
        updatePotentialWin();
    }

    function updatePotentialWin() {
        let amount = parseFloat(document.getElementById('inputBetAmount').value || 0);
        let odds = currentOdds[selectedOption] || 2.0;
        let potential = (amount * odds).toFixed(2);
        document.getElementById('potentialWinAmount').innerText = potential;
    }

    function submitBet(e) {
        e.preventDefault();
        let option = document.getElementById('selectedBetOption').value;
        let amount = document.getElementById('inputBetAmount').value;

        let btn = document.getElementById('btnConfirmBet');
        btn.disabled = true;
        btn.innerText = 'PROCESSING...';

        fetch("{{ route('games.andar-bahar.bet') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ bet_option: option, amount: amount })
        })
        .then(async res => {
            let data = await res.json().catch(() => ({ status: false, message: 'Server returned invalid response.' }));
            return { ok: res.ok, status: res.status, data: data };
        })
        .then(res => {
            btn.disabled = false;
            btn.innerText = 'CONFIRM BET';
            if (res.ok && res.data.status) {
                let modal = bootstrap.Modal.getInstance(document.getElementById('betModal'));
                if (modal) modal.hide();
                fetchGameState();
            } else {
                alert(res.data.message || 'Failed to place bet. Please check your balance.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerText = 'CONFIRM BET';
            alert('Error processing bet. Please try again.');
        });
    }

    class AndarSoundEngine {
        constructor() {
            this.audioCtx = null;
        }
        init() {
            if (!this.audioCtx) {
                const AC = window.AudioContext || window.webkitAudioContext;
                if (AC) this.audioCtx = new AC();
            }
        }
        playTone(freq, type, duration, gainVal = 0.08) {
            try {
                this.init();
                if (!this.audioCtx) return;
                const osc = this.audioCtx.createOscillator();
                const gain = this.audioCtx.createGain();
                osc.type = type;
                osc.frequency.setValueAtTime(freq, this.audioCtx.currentTime);
                gain.gain.setValueAtTime(gainVal, this.audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.0001, this.audioCtx.currentTime + duration);
                osc.connect(gain);
                gain.connect(this.audioCtx.destination);
                osc.start();
                osc.stop(this.audioCtx.currentTime + duration);
            } catch (e) {}
        }
        playCardFlip() {
            this.playTone(650, 'sine', 0.05, 0.08);
            setTimeout(() => this.playTone(850, 'triangle', 0.05, 0.06), 30);
        }
        playWinSound() {
            this.playTone(523.25, 'sine', 0.15, 0.12);
            setTimeout(() => this.playTone(659.25, 'sine', 0.15, 0.12), 120);
            setTimeout(() => this.playTone(783.99, 'sine', 0.35, 0.15), 240);
        }
        playLossSound() {
            this.playTone(240, 'sawtooth', 0.2, 0.12);
            setTimeout(() => this.playTone(180, 'sawtooth', 0.3, 0.12), 150);
        }
    }
    const andarSound = new AndarSoundEngine();

    let currentDealInterval = null;
    let lastShownModalPeriod = null;

    function animateDealingSequence(resultData) {
        if (!resultData || !resultData.deal_sequence) return;
        isDealingAnimated = true;

        if (currentDealInterval) {
            clearInterval(currentDealInterval);
            currentDealInterval = null;
        }

        let andarBox = document.getElementById('cardsAndar');
        let baharBox = document.getElementById('cardsBahar');
        let countAndarBadge = document.getElementById('countAndarBadge');
        let countBaharBadge = document.getElementById('countBaharBadge');
        let dealStatusText = document.getElementById('dealStatusText');

        let sequence = resultData.deal_sequence;
        if (sequence.length === 0) return;

        andarBox.innerHTML = '';
        baharBox.innerHTML = '';
        let andarCount = 0;
        let baharCount = 0;

        if (countAndarBadge) countAndarBadge.innerText = '0 Cards';
        if (countBaharBadge) countBaharBadge.innerText = '0 Cards';

        if (dealStatusText) {
            dealStatusText.className = 'badge bg-warning text-dark';
            dealStatusText.innerText = '⚡ CARDS DEALING...';
        }

        let index = 0;
        let dealIntervalMs = 350; // Throw cards one by one smoothly when countdown reaches 00:00

        currentDealInterval = setInterval(() => {
            if (index >= sequence.length) {
                clearInterval(currentDealInterval);
                currentDealInterval = null;

                let winner = (resultData.winner || '').toLowerCase();
                let winningCard = resultData.winning_card || '';

                if (dealStatusText) {
                    dealStatusText.className = (winner === 'andar') ? 'badge bg-primary' : ((winner === 'bahar') ? 'badge bg-danger' : 'badge bg-warning text-dark');
                    dealStatusText.innerText = '🎉 WINNER: ' + winner.toUpperCase() + ' (' + winningCard + ')';
                }

                // Highlight winning side
                if (winner === 'andar') {
                    document.getElementById('sideBoxAndar').classList.add('winner-glow');
                } else if (winner === 'bahar') {
                    document.getElementById('sideBoxBahar').classList.add('winner-glow');
                }

                andarSound.playWinSound();
                checkAndShowUserResultModal(resultData);
                return;
            }

            let item = sequence[index];
            let cardStr = item.card;
            let suit = cardStr.slice(-1);
            let rank = cardStr.slice(0, -1);
            let isRed = (suit === '♥' || suit === '♦');
            let isMatchingCard = (index === sequence.length - 1);

            // Play card flip sound
            andarSound.playCardFlip();

            let targetContainer = (item.side === 'andar') ? andarBox : baharBox;

            let cardHtml = `
                <div class="mini-deal-card ${isRed ? 'red' : 'black'} ${isMatchingCard ? 'card-matching-win' : 'card-slide-deal'} shadow-sm">
                    ${isMatchingCard ? '<span class="card-matched-badge">MATCH!</span>' : ''}
                    <div>${rank}</div>
                    <div class="text-center">${suit}</div>
                </div>
            `;

            targetContainer.insertAdjacentHTML('beforeend', cardHtml);

            if (item.side === 'andar') {
                andarCount++;
                if (countAndarBadge) countAndarBadge.innerText = andarCount + ' Cards';
            } else {
                baharCount++;
                if (countBaharBadge) countBaharBadge.innerText = baharCount + ' Cards';
            }

            index++;
        }, dealIntervalMs);
    }

    function checkAndShowUserResultModal(resultData) {
        if (!resultData || lastShownModalPeriod === resultData.period_number) return;

        let myOrders = window.lastMyOrdersArray || [];
        let bet = myOrders.find(b => b.period_number === resultData.period_number);
        if (!bet) return; // User didn't bet on this period

        lastShownModalPeriod = resultData.period_number;

        let winner = (resultData.winner || '').toLowerCase();
        let isWon = (bet.status === 'won' || (parseFloat(bet.win_amount || 0) > 0));

        let modalTitle = document.getElementById('resultModalTitle');
        let modalHeader = document.getElementById('resultModalHeader');
        let modalIcon = document.getElementById('resultModalIcon');
        let modalWinningCard = document.getElementById('resultModalWinningCard');
        let modalWinnerSide = document.getElementById('resultModalWinnerSide');
        let modalAmount = document.getElementById('resultModalAmount');

        if (!modalTitle || !modalHeader) return;

        modalWinningCard.innerText = resultData.winning_card || '';
        modalWinnerSide.innerText = winner.toUpperCase();
        modalWinnerSide.className = (winner === 'andar') ? 'text-info' : ((winner === 'bahar') ? 'text-danger' : 'text-warning');

        if (isWon) {
            modalTitle.innerText = 'CONGRATULATIONS!';
            modalHeader.style.background = 'linear-gradient(135deg, #16A34A, #15803D)';
            modalIcon.innerHTML = '<i class="bi bi-trophy-fill text-warning"></i>';
            modalAmount.className = 'fw-bold text-success mb-0 font-monospace';
            modalAmount.innerText = '+₹' + parseFloat(bet.win_amount).toFixed(2);
            andarSound.playWinSound();
        } else {
            modalTitle.innerText = 'BET LOST';
            modalHeader.style.background = 'linear-gradient(135deg, #DC2626, #991B1B)';
            modalIcon.innerHTML = '<i class="bi bi-x-circle-fill text-white"></i>';
            modalAmount.className = 'fw-bold text-danger mb-0 font-monospace';
            modalAmount.innerText = '-₹' + parseFloat(bet.bet_amount).toFixed(2);
            andarSound.playLossSound();
        }

        let modal = new bootstrap.Modal(document.getElementById('roundResultModal'));
        modal.show();
    }

    function resetDealingStage() {
        document.getElementById('cardsAndar').innerHTML = '<small class="text-secondary opacity-75 my-3">Waiting for deal...</small>';
        document.getElementById('cardsBahar').innerHTML = '<small class="text-secondary opacity-75 my-3">Waiting for deal...</small>';
        let countAndarBadge = document.getElementById('countAndarBadge');
        let countBaharBadge = document.getElementById('countBaharBadge');
        let dealStatusText = document.getElementById('dealStatusText');

        if (countAndarBadge) countAndarBadge.innerText = '0 Cards';
        if (countBaharBadge) countBaharBadge.innerText = '0 Cards';
        if (dealStatusText) {
            dealStatusText.className = 'badge bg-secondary bg-opacity-50 text-secondary border border-secondary';
            dealStatusText.innerText = 'WAITING FOR BETS';
        }

        document.getElementById('sideBoxAndar').classList.remove('winner-glow');
        document.getElementById('sideBoxBahar').classList.remove('winner-glow');
    }

    function renderRecordBadges(history) {
        if (!history) return;
        let cacheKey = JSON.stringify(history.map(h => h.period_number + h.winner));
        if (cacheKey === lastHistoryCache) return;
        lastHistoryCache = cacheKey;

        let container = document.getElementById('recentRecordRow');
        let html = '';
        history.forEach(item => {
            let badgeClass = (item.winner === 'andar') ? 'badge-andar' : ((item.winner === 'bahar') ? 'badge-bahar' : 'badge-tie');
            let letter = (item.winner === 'andar') ? 'A' : ((item.winner === 'bahar') ? 'B' : 'T');
            let shortPeriod = item.period_number.slice(-4);
            html += `
                <div class="text-center flex-shrink-0">
                    <span class="ab-badge-circle ${badgeClass}">${letter}</span>
                    <small class="d-block text-secondary mt-1 font-monospace" style="font-size:0.62rem;">${shortPeriod}</small>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    function renderMyOrders(myOrders) {
        if (!myOrders) return;
        window.lastMyOrdersArray = myOrders;
        let cacheKey = JSON.stringify(myOrders.map(o => o.id + o.status + o.win_amount));
        if (cacheKey === lastMyOrdersCache) return;
        lastMyOrdersCache = cacheKey;

        let tbody = document.getElementById('myOrdersTableBody');
        if (myOrders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-secondary py-3">No bet orders placed yet.</td></tr>';
            return;
        }
        let html = '';
        myOrders.forEach(b => {
            let optClass = (b.bet_option === 'andar') ? 'bg-primary' : ((b.bet_option === 'bahar') ? 'bg-danger' : 'bg-warning text-dark');
            let statusBadge = (b.status === 'pending') ? '<span class="badge bg-secondary">PENDING</span>' : ((b.status === 'won') ? '<span class="badge bg-success">WON</span>' : '<span class="badge bg-danger">LOST</span>');
            let winClass = (b.win_amount > 0) ? 'text-success' : 'text-secondary';
            html += `
                <tr>
                    <td class="fw-bold font-monospace">${b.period_number}</td>
                    <td><span class="badge ${optClass}">${b.bet_option.toUpperCase()}</span></td>
                    <td class="fw-bold">₹${parseFloat(b.bet_amount).toFixed(2)}</td>
                    <td>${statusBadge}</td>
                    <td class="fw-bold ${winClass}">₹${parseFloat(b.win_amount).toFixed(2)}</td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function renderEveryoneOrders(orders) {
        if (!orders) return;
        let cacheKey = JSON.stringify(orders.map(o => o.id + o.status));
        if (cacheKey === lastEveryoneOrdersCache) return;
        lastEveryoneOrdersCache = cacheKey;

        let tbody = document.getElementById('everyoneOrdersTableBody');
        if (orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-secondary py-3">No live bets in this period.</td></tr>';
            return;
        }
        let html = '';
        orders.forEach(o => {
            let optClass = (o.selection === 'ANDAR') ? 'bg-primary' : ((o.selection === 'BAHAR') ? 'bg-danger' : 'bg-warning text-dark');
            html += `
                <tr>
                    <td class="fw-bold font-monospace text-info">${o.user}</td>
                    <td><span class="badge ${optClass}">${o.selection}</span></td>
                    <td class="fw-bold text-warning">₹${o.amount}</td>
                    <td><small class="badge bg-success bg-opacity-20 text-success border border-success">${o.status}</small></td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function fetchFullHistory() {
        fetch("{{ route('games.andar-bahar.history') }}")
            .then(res => res.json())
            .then(res => {
                if (!res.status || !res.data) return;
                let tbody = document.getElementById('fullHistoryTableBody');
                let html = '';
                res.data.data.forEach(row => {
                    let winnerBadge = (row.winner === 'andar') ? '<span class="badge bg-primary">ANDAR</span>' : ((row.winner === 'bahar') ? '<span class="badge bg-danger">BAHAR</span>' : '<span class="badge bg-warning text-dark">TIE</span>');
                    html += `
                        <tr>
                            <td class="fw-bold font-monospace">${row.period_number}</td>
                            <td><span class="badge bg-light text-dark fs-6">${row.open_card}</span></td>
                            <td>${winnerBadge}</td>
                            <td><span class="badge bg-secondary">${row.winning_card}</span></td>
                            <td>${row.deal_count} cards</td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
                let modal = new bootstrap.Modal(document.getElementById('fullHistoryModal'));
                modal.show();
            });
    }
</script>
@endsection
