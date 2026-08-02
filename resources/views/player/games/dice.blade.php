@extends('layouts.app')

@section('content')
<style>
    /* ── GameHub Premium Casino Theme & Glassmorphism ── */
    :root {
        --dice-bg-dark: #0B132B;
        --dice-card-bg: rgba(15, 23, 42, 0.85);
        --dice-card-border: rgba(255, 255, 255, 0.12);
        --dice-accent-blue: #1E88E5;
        --dice-accent-cyan: #06B6D4;
        --dice-accent-gold: #F59E0B;
        --dice-accent-green: #10B981;
        --dice-accent-red: #EF4444;
        --dice-radius: 20px;
    }

    .dice-container {
        background: linear-gradient(180deg, #0A0F1D 0%, #0F172A 100%);
        color: #F8FAFC;
        border-radius: var(--dice-radius);
        padding: 16px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.4);
    }

    .dice-glass-card {
        background: var(--dice-card-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--dice-card-border);
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
    }

    /* ── 3D & 2D Dice Rendering & Animations ── */
    .dice-stage {
        perspective: 1000px;
        position: relative;
        min-height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle at center, rgba(30, 136, 229, 0.15) 0%, rgba(15, 23, 42, 0.6) 70%);
        border-radius: 16px;
        border: 1px dashed rgba(255, 255, 255, 0.15);
        overflow: hidden;
    }

    .dice-cube-wrapper {
        width: 100px;
        height: 100px;
        position: relative;
        transform-style: preserve-3d;
        transition: transform 0.3s ease;
    }

    .dice-face-3d {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #FFFFFF 0%, #E2E8F0 100%);
        border-radius: 18px;
        border: 3px solid #CBD5E1;
        box-shadow: inset 0 0 10px rgba(0,0,0,0.1), 0 10px 25px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        backface-visibility: hidden;
        user-select: none;
    }

    .dice-glow-win {
        box-shadow: 0 0 35px rgba(16, 185, 129, 0.8), 0 0 15px rgba(16, 185, 129, 0.5) !important;
        border-color: #10B981 !important;
    }

    .dice-glow-loss {
        box-shadow: 0 0 35px rgba(239, 68, 68, 0.8), 0 0 15px rgba(239, 68, 68, 0.5) !important;
        border-color: #EF4444 !important;
    }

    /* 60 FPS GPU-Accelerated Rolling Animations */
    @keyframes dice3DSpinShake {
        0%   { transform: rotateX(0deg) rotateY(0deg) rotateZ(0deg) scale(1); }
        20%  { transform: rotateX(180deg) rotateY(90deg) rotateZ(45deg) scale(1.1); }
        40%  { transform: rotateX(360deg) rotateY(270deg) rotateZ(180deg) scale(1.18); }
        60%  { transform: rotateX(540deg) rotateY(450deg) rotateZ(270deg) scale(1.12); }
        80%  { transform: rotateX(670deg) rotateY(600deg) rotateZ(330deg) scale(1.05); }
        100% { transform: rotateX(720deg) rotateY(720deg) rotateZ(360deg) scale(1); }
    }

    .dice-anim-rolling {
        animation: dice3DSpinShake 1.2s cubic-bezier(0.25, 1, 0.5, 1) forwards;
        will-change: transform;
    }

    @keyframes diceLandingBounce {
        0% { transform: scale(1.2); }
        50% { transform: scale(0.92); }
        75% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .dice-anim-landing {
        animation: diceLandingBounce 0.35s ease-out forwards;
    }

    /* ── Segmented Controls (OVER / UNDER / EXACT) ── */
    .dice-segmented-nav {
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 30px;
        padding: 4px;
        display: flex;
        gap: 4px;
        width: 100%;
        overflow: hidden;
        box-sizing: border-box;
    }

    .dice-seg-btn {
        flex: 1 1 0;
        min-width: 0;
        background: transparent;
        color: #94A3B8;
        border: none;
        border-radius: 24px;
        padding: 10px 6px;
        font-weight: 700;
        font-size: 0.82rem;
        transition: all 0.25s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dice-seg-btn.active-over {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        color: #FFFFFF;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
    }

    .dice-seg-btn.active-under {
        background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
        color: #FFFFFF;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
    }

    .dice-seg-btn.active-exact {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        color: #FFFFFF;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
    }

    /* ── Exact Number Selector Chips ── */
    .exact-chip {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.05);
        border: 1.5px solid rgba(255, 255, 255, 0.15);
        color: #F8FAFC;
        font-weight: 800;
        font-size: 1.2rem;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .exact-chip.selected {
        background: linear-gradient(135deg, #1E88E5 0%, #1565C0 100%);
        border-color: #38BDF8;
        color: #FFFFFF;
        box-shadow: 0 0 15px rgba(30, 136, 229, 0.6);
        transform: scale(1.08);
    }

    /* ── Preset Bet Chips ── */
    .dice-chip-btn {
        background: rgba(30, 136, 229, 0.12);
        border: 1px solid rgba(30, 136, 229, 0.3);
        color: #38BDF8;
        border-radius: 20px;
        padding: 6px 12px;
        font-weight: 700;
        font-size: 0.78rem;
        transition: all 0.2s ease;
    }

    .dice-chip-btn:hover, .dice-chip-btn:active {
        background: rgba(30, 136, 229, 0.3);
        color: #FFFFFF;
        transform: translateY(-1px);
    }

    .dice-quick-btn {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #CBD5E1;
        border-radius: 10px;
        padding: 6px 10px;
        font-weight: 700;
        font-size: 0.75rem;
        transition: all 0.2s ease;
    }

    .dice-quick-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #FFFFFF;
    }

    /* ── Primary Roll Button ── */
    .btn-dice-roll {
        background: linear-gradient(135deg, #1E88E5 0%, #0284C7 100%);
        color: #FFFFFF;
        border: none;
        border-radius: 30px;
        padding: 16px;
        font-weight: 800;
        font-size: 1.15rem;
        letter-spacing: 0.5px;
        box-shadow: 0 8px 25px rgba(30, 136, 229, 0.45);
        transition: all 0.25s ease;
        width: 100%;
    }

    .btn-dice-roll:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(30, 136, 229, 0.6);
        filter: brightness(1.1);
    }

    .btn-dice-roll:active:not(:disabled) {
        transform: translateY(1px);
        box-shadow: 0 4px 15px rgba(30, 136, 229, 0.4);
    }

    /* ── Mobile Responsiveness Enhancements ── */
    .dice-chips-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 6px;
    }

    @media (max-width: 576px) {
        .dice-container {
            padding: 8px !important;
            border-radius: 0 !important;
        }
        .dice-glass-card {
            padding: 10px !important;
            border-radius: 12px !important;
        }
        .dice-seg-btn {
            font-size: 0.74rem !important;
            padding: 8px 3px !important;
            gap: 2px !important;
        }
        .dice-seg-btn i {
            font-size: 0.8rem !important;
        }
        .dice-seg-btn .badge {
            display: none !important; /* Save space on tiny phone screens */
        }
        .dice-stage {
            min-height: 140px !important;
        }
        .dice-cube-wrapper {
            width: 80px !important;
            height: 80px !important;
        }
        .dice-face-3d {
            width: 80px !important;
            height: 80px !important;
        }
        .dice-face-3d svg {
            width: 64px !important;
            height: 64px !important;
        }
    }
</style>

<div class="dice-container">

    <!-- ── Top Header Navigation Bar ── -->
    <div class="dice-glass-card mb-3 p-2 px-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('home') }}" class="btn btn-sm btn-dark rounded-circle border border-secondary text-white" title="Back to Home">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="d-flex align-items-center gap-2">
                <span class="p-1 px-2 rounded-3 text-white" style="background: linear-gradient(135deg, var(--dice-accent-blue), var(--dice-accent-gold));">
                    <i class="bi bi-dice-5-fill fs-5"></i>
                </span>
                <div>
                    <div class="fw-bold text-white fs-6 lh-1">Dice Arena</div>
                    <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 rounded-pill px-2" style="font-size: 0.62rem;">
                        <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i>LIVE 60FPS
                    </span>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Sound Toggle Button -->
            <button type="button" id="diceSoundToggleBtn" class="btn btn-sm btn-outline-secondary rounded-circle" onclick="toggleDiceSound()" title="Toggle Sound">
                <i class="bi bi-volume-up-fill text-warning" id="diceSoundIcon"></i>
            </button>

            <!-- Rules Modal Trigger -->
            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2 fw-semibold" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#diceRulesModal">
                <i class="bi bi-info-circle me-1"></i>Rules
            </button>
        </div>
    </div>

    <!-- ── Live Preview Stats Header (2x2 on Mobile, 1x4 on Desktop) ── -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-sm-3">
            <div class="dice-glass-card p-2 text-center">
                <small class="text-secondary d-block" style="font-size: 0.65rem;">CONDITION</small>
                <span id="previewCondition" class="fw-bold text-info" style="font-size: 0.85rem;">OVER 3</span>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="dice-glass-card p-2 text-center">
                <small class="text-secondary d-block" style="font-size: 0.65rem;">WIN CHANCE</small>
                <span id="previewChance" class="fw-bold text-warning" style="font-size: 0.85rem;">50.00%</span>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="dice-glass-card p-2 text-center">
                <small class="text-secondary d-block" style="font-size: 0.65rem;">MULTIPLIER</small>
                <span id="previewMultiplier" class="fw-bold text-success" style="font-size: 0.85rem;">1.90x</span>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="dice-glass-card p-2 text-center">
                <small class="text-secondary d-block" style="font-size: 0.65rem;">PROFIT (₹)</small>
                <span id="previewProfit" class="fw-bold text-emerald font-monospace" style="font-size: 0.85rem; color: #10B981;">₹45.00</span>
            </div>
        </div>
    </div>

    <!-- ── 3D Dice Stage & Live Result Display ── -->
    <div class="dice-glass-card mb-3 p-3">
        <div class="dice-stage" id="diceStageArea">
            <div class="text-center">
                <!-- 3D Cube Face Wrapper -->
                <div class="dice-cube-wrapper mx-auto mb-2" id="diceCubeWrapper">
                    <div class="dice-face-3d" id="diceFaceDisplay">
                        <svg viewBox="0 0 100 100" width="80" height="80">
                            <g id="diceSvgDots"></g>
                        </svg>
                    </div>
                </div>

                <div id="diceStatusLabel" class="fw-bold text-secondary" style="font-size: 0.78rem;">ROLL TO PLAY</div>
                <div id="diceResultBadge" class="fs-4 fw-black font-monospace text-warning">?</div>
            </div>
        </div>
    </div>

    <!-- ── Bet Type Segmented Selector ── -->
    <div class="dice-glass-card mb-3 p-3">
        <small class="text-secondary fw-semibold d-block mb-2 text-center" style="font-size: 0.72rem;">SELECT BET CONDITION</small>
        
        <div class="dice-segmented-nav mb-3">
            <button type="button" class="dice-seg-btn active-over" id="btnBetOver" onclick="setDiceMode('over')">
                <div class="d-flex flex-column align-items-center justify-content-center lh-sm text-nowrap py-1">
                    <span class="fw-bold"><i class="bi bi-arrow-up-circle-fill me-1"></i>OVER 3</span>
                    <span class="badge bg-dark bg-opacity-40 rounded-pill px-2 mt-1" style="font-size: 0.62rem;">1.90x</span>
                </div>
            </button>
            <button type="button" class="dice-seg-btn" id="btnBetUnder" onclick="setDiceMode('under')">
                <div class="d-flex flex-column align-items-center justify-content-center lh-sm text-nowrap py-1">
                    <span class="fw-bold"><i class="bi bi-arrow-down-circle-fill me-1"></i>UNDER 4</span>
                    <span class="badge bg-dark bg-opacity-40 rounded-pill px-2 mt-1" style="font-size: 0.62rem;">1.90x</span>
                </div>
            </button>
            <button type="button" class="dice-seg-btn" id="btnBetExact" onclick="setDiceMode('exact')">
                <div class="d-flex flex-column align-items-center justify-content-center lh-sm text-nowrap py-1">
                    <span class="fw-bold"><i class="bi bi-crosshair me-1"></i>EXACT</span>
                    <span class="badge bg-dark bg-opacity-40 rounded-pill px-2 mt-1" style="font-size: 0.62rem;">5.50x</span>
                </div>
            </button>
        </div>

        <!-- Exact Number Selector Chips (Visible when Mode === exact) -->
        <div id="exactPickerContainer" class="d-none">
            <small class="text-secondary fw-semibold d-block mb-2 text-center" style="font-size: 0.72rem;">PICK EXACT DIE FACE (1–6)</small>
            <div class="d-flex gap-2 justify-content-center flex-wrap mb-2">
                @for($n = 1; $n <= 6; $n++)
                <button type="button" class="exact-chip {{ $n === 1 ? 'selected' : '' }}" id="chipExact-{{ $n }}" onclick="selectExactFace({{ $n }})">
                    {{ $n }}
                </button>
                @endfor
            </div>
        </div>

        <!-- Bet Amount & Chips Selection -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <small class="text-secondary fw-semibold" style="font-size: 0.72rem;">BET AMOUNT (₹)</small>
                <div class="d-flex gap-1">
                    <button type="button" class="dice-quick-btn" onclick="modifyBet('half')">½</button>
                    <button type="button" class="dice-quick-btn" onclick="modifyBet('double')">2×</button>
                    <button type="button" class="dice-quick-btn" onclick="modifyBet('max')">MAX</button>
                    <button type="button" class="dice-quick-btn" onclick="modifyBet('clear')">CLEAR</button>
                </div>
            </div>

            <!-- Chips Grid (3x2 Grid) -->
            <div class="dice-chips-grid mb-2">
                @foreach([10, 50, 100, 500, 1000, 5000] as $chip)
                <button type="button" class="dice-chip-btn text-center" onclick="addBetChip({{ $chip }})">+₹{{ $chip }}</button>
                @endforeach
            </div>

            <!-- Custom Input Field -->
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-warning fw-bold">₹</span>
                <input type="number" id="diceBetInput" class="form-control bg-dark border-secondary text-white fw-bold fs-5 font-monospace text-center"
                       value="50" min="{{ $game->min_entry_fee }}" max="{{ $game->max_entry_fee }}" oninput="onBetInputChanged()">
            </div>
        </div>

        <!-- ── ROLL THE DICE BUTTON ── -->
        <button type="button" id="btnRollDice" class="btn btn-dice-roll" onclick="executeDiceRoll()">
            <i class="bi bi-dice-5-fill me-2"></i>ROLL THE DICE
        </button>
    </div>

    <!-- ── Live Statistics Dashboard ── -->
    <div class="row g-2 mb-3">
        <div class="col-4">
            <div class="dice-glass-card p-2 text-center">
                <small class="text-secondary d-block" style="font-size: 0.62rem;">WIN RATE</small>
                <span id="statWinRate" class="fw-bold text-success font-monospace" style="font-size: 0.85rem;">0.0%</span>
            </div>
        </div>
        <div class="col-4">
            <div class="dice-glass-card p-2 text-center">
                <small class="text-secondary d-block" style="font-size: 0.62rem;">TOTAL BETS</small>
                <span id="statTotalBets" class="fw-bold text-info font-monospace" style="font-size: 0.85rem;">0</span>
            </div>
        </div>
        <div class="col-4">
            <div class="dice-glass-card p-2 text-center">
                <small class="text-secondary d-block" style="font-size: 0.62rem;">TOTAL PROFIT</small>
                <span id="statTotalProfit" class="fw-bold text-warning font-monospace" style="font-size: 0.85rem;">₹0.00</span>
            </div>
        </div>
    </div>

    <!-- ── Live Rolling History Table ── -->
    <div class="dice-glass-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-white mb-0" style="font-size: 0.85rem;">
                <i class="bi bi-clock-history me-1 text-warning"></i>Live Roll History
            </h6>
            <span class="badge bg-secondary bg-opacity-30 text-secondary" style="font-size: 0.65rem;">Auto Sync</span>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle small text-center mb-0" style="background: transparent;">
                <thead class="text-secondary border-secondary">
                    <tr>
                        <th style="font-size:0.7rem;">Time</th>
                        <th style="font-size:0.7rem;">Condition</th>
                        <th style="font-size:0.7rem;">Bet (₹)</th>
                        <th style="font-size:0.7rem;">Rolled</th>
                        <th style="font-size:0.7rem;">Result</th>
                    </tr>
                </thead>
                <tbody id="diceHistoryTableBody">
                    @forelse($myBets as $bet)
                    <tr class="history-row-slide">
                        <td class="text-secondary font-monospace" style="font-size:0.72rem;">{{ $bet->created_at->format('H:i:s') }}</td>
                        <td><span class="badge bg-secondary bg-opacity-40 text-uppercase">{{ $bet->bet_type }}</span></td>
                        <td class="fw-bold font-monospace">₹{{ number_format($bet->bet_amount, 2) }}</td>
                        <td class="fw-bold text-warning font-monospace fs-6">{{ $bet->bet_details['rolled'] ?? '?' }}</td>
                        <td>
                            @if($bet->status === 'won')
                                <span class="badge bg-success rounded-pill px-2">+₹{{ number_format($bet->win_amount, 2) }}</span>
                            @elseif($bet->status === 'lost')
                                <span class="badge bg-danger rounded-pill px-2">LOST</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill px-2">PENDING</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyHistoryRow"><td colspan="5" class="text-secondary py-3">No rolls recorded yet. Place your first bet!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── RULES MODAL ── -->
<div class="modal fade" id="diceRulesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 360px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow bg-dark text-white overflow-hidden">
            <div class="p-3 text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #1E88E5, #0284C7);">
                <h6 class="fw-bold mb-0"><i class="bi bi-controller me-2"></i>Dice Game Rules</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 small text-secondary">
                <p><strong class="text-white">1. Over Mode (1.90x):</strong> Wins if the rolled die is 4, 5, or 6.</p>
                <p><strong class="text-white">2. Under Mode (1.90x):</strong> Wins if the rolled die is 1, 2, or 3.</p>
                <p><strong class="text-white">3. Exact Mode (5.50x):</strong> Pick a single face (1 to 6). Wins if the rolled die matches your pick exactly.</p>
                <p class="mb-0 text-info"><i class="bi bi-shield-check me-1"></i>All rolls are generated using server-side cryptographically secure random values.</p>
            </div>
        </div>
    </div>
</div>

<!-- ── WIN MODAL ── -->
<div class="modal fade" id="diceWinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow bg-dark text-white overflow-hidden text-center">
            <div class="p-3 text-white" style="background: linear-gradient(135deg, #10B981, #059669);">
                <div class="fs-1 mb-1"><i class="bi bi-trophy-fill text-warning"></i></div>
                <h5 class="fw-bold mb-0">YOU WON!</h5>
            </div>
            <div class="modal-body p-3">
                <p class="text-secondary small mb-2">
                    Rolled <span id="winModalRolled" class="fw-bold text-warning fs-5">6</span> — Outstanding!
                </p>
                <div class="p-2 bg-dark border border-success rounded-3 mb-3">
                    <small class="text-secondary d-block">TOTAL WINNINGS</small>
                    <h3 id="winModalAmount" class="fw-bold text-success mb-0 font-monospace">+₹0.00</h3>
                </div>
                <button type="button" class="btn gh-btn-success w-100 py-2 rounded-pill fw-bold" data-bs-dismiss="modal">
                    ROLL AGAIN
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── LOSS MODAL ── -->
<div class="modal fade" id="diceLossModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow bg-dark text-white overflow-hidden text-center">
            <div class="p-3 text-white" style="background: linear-gradient(135deg, #EF4444, #DC2626);">
                <div class="fs-2 mb-1"><i class="bi bi-x-octagon-fill"></i></div>
                <h5 class="fw-bold mb-0">BETTER LUCK NEXT TIME</h5>
            </div>
            <div class="modal-body p-3">
                <p class="text-secondary small mb-3">
                    Rolled <span id="lossModalRolled" class="fw-bold text-danger fs-5">1</span> — Did not match condition.
                </p>
                <button type="button" class="btn btn-outline-danger w-100 py-2 rounded-pill fw-bold" data-bs-dismiss="modal">
                    TRY AGAIN
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
class SoundEffectsEngine {
    constructor() {
        this.enabled = (localStorage.getItem('dice_sound_enabled') !== 'false');
        this.audioCtx = null;
        this.updateIcon();
    }

    initCtx() {
        if (!this.audioCtx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) this.audioCtx = new AudioContext();
        }
    }

    toggle() {
        this.enabled = !this.enabled;
        localStorage.setItem('dice_sound_enabled', this.enabled ? 'true' : 'false');
        this.updateIcon();
    }

    updateIcon() {
        const icon = document.getElementById('diceSoundIcon');
        if (icon) {
            icon.className = this.enabled ? 'bi bi-volume-up-fill text-warning' : 'bi bi-volume-mute-fill text-secondary';
        }
    }

    playTone(freq, type, duration, gainVal = 0.1) {
        if (!this.enabled) return;
        this.initCtx();
        if (!this.audioCtx) return;

        try {
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
        } catch (e) { /* Audio context suppressed */ }
    }

    play(soundType) {
        if (!this.enabled) return;
        if (soundType === 'click') this.playTone(600, 'sine', 0.08, 0.05);
        else if (soundType === 'chipSelect') this.playTone(800, 'triangle', 0.1, 0.08);
        else if (soundType === 'placeBet') this.playTone(520, 'sine', 0.15, 0.1);
        else if (soundType === 'diceRoll') {
            this.playTone(300, 'sawtooth', 0.05, 0.04);
            this.playTone(450, 'square', 0.05, 0.04);
        }
        else if (soundType === 'diceLanding') this.playTone(180, 'triangle', 0.2, 0.25);
        else if (soundType === 'win') {
            this.playTone(523.25, 'sine', 0.2, 0.15); // C5
            setTimeout(() => this.playTone(659.25, 'sine', 0.2, 0.15), 100); // E5
            setTimeout(() => this.playTone(783.99, 'sine', 0.4, 0.2), 200); // G5
        }
        else if (soundType === 'lose') {
            this.playTone(220, 'sawtooth', 0.2, 0.15);
            setTimeout(() => this.playTone(160, 'sawtooth', 0.3, 0.15), 150);
        }
    }
}

class DiceGameEngine {
    constructor(config) {
        this.gameId = config.gameId;
        this.csrfToken = config.csrfToken;
        this.routes = config.routes;
        this.minBet = config.minBet;
        this.maxBet = config.maxBet;

        this.soundEngine = new SoundEffectsEngine();
        this.mode = 'over'; // 'over' | 'under' | 'exact'
        this.exactFace = 1;
        this.isRolling = false;

        this.stats = {
            totalBets: 0,
            wins: 0,
            totalProfit: 0.00
        };

        this.DOT_MAP = {
            1: [[50, 50]],
            2: [[25, 25], [75, 75]],
            3: [[25, 25], [50, 50], [75, 75]],
            4: [[25, 25], [75, 25], [25, 75], [75, 75]],
            5: [[25, 25], [75, 25], [50, 50], [25, 75], [75, 75]],
            6: [[25, 22], [75, 22], [25, 50], [75, 50], [25, 78], [75, 78]]
        };

        this.initDOM();
        this.renderFace(1);
        this.updateLivePreview();
    }

    initDOM() {
        this.betInput = document.getElementById('diceBetInput');
        this.rollBtn = document.getElementById('btnRollDice') || document.getElementById('rollDiceBtn');
        this.cubeWrapper = document.getElementById('diceCubeWrapper');
        this.faceDisplay = document.getElementById('diceFaceDisplay');
        this.svgDots = document.getElementById('diceSvgDots');
        this.statusLabel = document.getElementById('diceStatusLabel');
        this.resultBadge = document.getElementById('diceResultBadge');

        this.winModal = new bootstrap.Modal(document.getElementById('diceWinModal'));
        this.lossModal = new bootstrap.Modal(document.getElementById('diceLossModal'));
    }

    renderFace(n) {
        if (!this.svgDots) return;
        this.svgDots.innerHTML = '';
        const dots = this.DOT_MAP[n] || [];
        dots.forEach(([cx, cy]) => {
            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.setAttribute('cx', cx);
            circle.setAttribute('cy', cy);
            circle.setAttribute('r', 7.5);
            circle.setAttribute('fill', '#1E88E5');
            this.svgDots.appendChild(circle);
        });
    }

    setMode(newMode) {
        this.soundEngine.play('click');
        this.mode = newMode;

        ['over', 'under', 'exact'].forEach(m => {
            const btn = document.getElementById('btnBet' + m.charAt(0).toUpperCase() + m.slice(1));
            if (btn) btn.className = 'dice-seg-btn' + (m === newMode ? ' active-' + m : '');
        });

        const exactContainer = document.getElementById('exactPickerContainer');
        if (exactContainer) exactContainer.classList.toggle('d-none', newMode !== 'exact');

        this.updateLivePreview();
    }

    selectExactFace(num) {
        this.soundEngine.play('click');
        this.exactFace = num;
        for (let i = 1; i <= 6; i++) {
            const chip = document.getElementById('chipExact-' + i);
            if (chip) chip.classList.toggle('selected', i === num);
        }
        this.updateLivePreview();
    }

    updateLivePreview() {
        const betAmount = parseFloat(String(this.betInput.value).replace(/,/g, '')) || 0.00;
        let condText = 'OVER 3';
        let chanceText = '50.00%';
        let multText = '1.90x';
        let multVal = 1.90;

        if (this.mode === 'over') {
            condText = 'OVER 3';
            chanceText = '50.00%';
            multText = '1.90x';
            multVal = 1.90;
        } else if (this.mode === 'under') {
            condText = 'UNDER 4';
            chanceText = '50.00%';
            multText = '1.90x';
            multVal = 1.90;
        } else if (this.mode === 'exact') {
            condText = 'EXACT ' + this.exactFace;
            chanceText = '16.67%';
            multText = '5.50x';
            multVal = 5.50;
        }

        const profitVal = betAmount * multVal;

        document.getElementById('previewCondition').innerText = condText;
        document.getElementById('previewChance').innerText = chanceText;
        document.getElementById('previewMultiplier').innerText = multText;
        document.getElementById('previewProfit').innerText = '₹' + profitVal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    addChip(val) {
        this.soundEngine.play('chipSelect');
        let current = parseFloat(String(this.betInput.value).replace(/,/g, '')) || 0.00;
        let newAmount = current + val;
        if (newAmount > this.maxBet) newAmount = this.maxBet;
        this.betInput.value = newAmount;
        this.updateLivePreview();
    }

    modifyBet(action) {
        this.soundEngine.play('click');
        let current = parseFloat(String(this.betInput.value).replace(/,/g, '')) || 0.00;
        if (action === 'half') current = Math.max(this.minBet, Math.floor(current / 2));
        else if (action === 'double') current = Math.min(this.maxBet, current * 2);
        else if (action === 'max') current = this.maxBet;
        else if (action === 'clear') current = this.minBet;
        this.betInput.value = current;
        this.updateLivePreview();
    }

    animateWalletCountUp(targetBalance) {
        const topEl = document.getElementById('topWalletBalance');
        if (!topEl) return;

        let startVal = parseFloat(String(topEl.innerText).replace(/[^\d.]/g, '')) || 0.00;
        let endVal = parseFloat(String(targetBalance).replace(/,/g, '')) || 0.00;

        let duration = 800; // ms
        let startTime = null;

        const step = (timestamp) => {
            if (!startTime) startTime = timestamp;
            let progress = Math.min((timestamp - startTime) / duration, 1.0);
            let currentVal = startVal + (endVal - startVal) * progress;

            topEl.innerText = '₹' + currentVal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            if (progress < 1.0) {
                requestAnimationFrame(step);
            }
        };

        requestAnimationFrame(step);
    }

    async executeRoll() {
        if (this.isRolling) return;

        const betAmount = parseFloat(String(this.betInput.value).replace(/,/g, ''));
        if (isNaN(betAmount) || betAmount < this.minBet || betAmount > this.maxBet) {
            alert(`Bet amount must be between ₹${this.minBet} and ₹${this.maxBet}`);
            return;
        }

        this.isRolling = true;
        this.soundEngine.play('placeBet');
        this.rollBtn.disabled = true;
        this.rollBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>ROLLING...';

        let betTypeStr = (this.mode === 'exact') ? 'exact_' + this.exactFace : this.mode;

        const formData = new FormData();
        formData.append('game_id', this.gameId);
        formData.append('period_number', 'DICE_' + Date.now());
        formData.append('bet_amount', betAmount);
        formData.append('bet_type', betTypeStr);

        try {
            // 1. Send Bet Placement API Request
            const betRes = await fetch(this.routes.bet, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                body: formData
            });

            const betData = await betRes.json();
            if (!betData.success) {
                alert(betData.message || 'Failed to place bet');
                this.resetRollBtn();
                return;
            }

            this.animateWalletCountUp(betData.new_balance);

            // 2. Start 60 FPS GPU-Accelerated 3D Rolling Animation (1.5s sequence)
            this.cubeWrapper.classList.add('dice-anim-rolling');
            this.faceDisplay.classList.remove('dice-glow-win', 'dice-glow-loss');
            this.statusLabel.innerText = 'DICE IS ROLLING...';
            this.statusLabel.className = 'fw-bold text-warning';
            this.resultBadge.innerText = '?';

            // Flash random faces with rolling audio
            let flashCount = 0;
            const flashInterval = setInterval(() => {
                let rnd = Math.ceil(Math.random() * 6);
                this.renderFace(rnd);
                this.soundEngine.play('diceRoll');
                flashCount++;
                if (flashCount >= 18) clearInterval(flashInterval);
            }, 75);

            // 3. Settle Round with Backend Server
            const settleForm = new FormData();
            settleForm.append('bet_id', betData.bet.id);

            const settleRes = await fetch(this.routes.settle, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                body: settleForm
            });
            const settleData = await settleRes.json();

            // Wait until full 1.4s animation sequence finishes
            await new Promise(r => setTimeout(r, 1400));
            clearInterval(flashInterval);
            this.cubeWrapper.classList.remove('dice-anim-rolling');
            this.cubeWrapper.classList.add('dice-anim-landing');

            if (!settleData.success) {
                alert(settleData.message || 'Error settling roll.');
                this.resetRollBtn();
                return;
            }

            // 4. Reveal Exact Server Rolled Face & Landing Impact Sound
            const rolledNum = settleData.rolled;
            this.renderFace(rolledNum);
            this.soundEngine.play('diceLanding');

            const isWon = (settleData.status === 'won');
            this.faceDisplay.classList.add(isWon ? 'dice-glow-win' : 'dice-glow-loss');
            this.statusLabel.innerText = isWon ? '🎉 WINNER!' : '❌ NO MATCH';
            this.statusLabel.className = 'fw-bold ' + (isWon ? 'text-success' : 'text-danger');
            this.resultBadge.innerText = rolledNum;

            this.animateWalletCountUp(settleData.new_balance);
            this.updateStats(isWon, betAmount, parseFloat(String(settleData.win_amount).replace(/,/g, '')));

            // 5. Slide New Row into History Feed
            this.prependHistory(betTypeStr, betAmount, rolledNum, settleData);

            // 6. Show Win / Loss Modal & Sound Effects
            setTimeout(() => {
                this.cubeWrapper.classList.remove('dice-anim-landing');
                if (isWon) {
                    document.getElementById('winModalRolled').innerText = rolledNum;
                    document.getElementById('winModalAmount').innerText = '+₹' + settleData.win_amount;
                    this.winModal.show();
                    this.soundEngine.play('win');
                    if (window.animationManager) {
                        window.animationManager.triggerConfetti(60);
                        window.animationManager.animateCoinsToWallet(this.cubeWrapper);
                    }
                } else {
                    document.getElementById('lossModalRolled').innerText = rolledNum;
                    this.lossModal.show();
                    this.soundEngine.play('lose');
                    if (window.animationManager) window.animationManager.shakeScreen();
                }
            }, 400);

        } catch (err) {
            console.error('Dice Roll Error:', err);
            alert('Network communication error. Please try again.');
        } finally {
            this.resetRollBtn();
        }
    }

    resetRollBtn() {
        this.isRolling = false;
        if (this.rollBtn) {
            this.rollBtn.disabled = false;
            this.rollBtn.innerHTML = '<i class="bi bi-dice-5-fill me-2"></i>ROLL THE DICE';
        }
    }

    updateStats(isWon, betAmount, winAmount) {
        this.stats.totalBets += 1;
        if (isWon) {
            this.stats.wins += 1;
            this.stats.totalProfit += (winAmount - betAmount);
        } else {
            this.stats.totalProfit -= betAmount;
        }

        const winRate = ((this.stats.wins / this.stats.totalBets) * 100).toFixed(1);

        document.getElementById('statWinRate').innerText = winRate + '%';
        document.getElementById('statTotalBets').innerText = this.stats.totalBets;
        
        const profitEl = document.getElementById('statTotalProfit');
        let prefix = this.stats.totalProfit >= 0 ? '+₹' : '-₹';
        profitEl.innerText = prefix + Math.abs(this.stats.totalProfit).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        profitEl.className = 'fw-bold font-monospace style-stat ' + (this.stats.totalProfit >= 0 ? 'text-warning' : 'text-danger');
    }

    prependHistory(betType, amount, rolled, settleData) {
        const tbody = document.getElementById('diceHistoryTableBody');
        const emptyRow = document.getElementById('emptyHistoryRow');
        if (emptyRow) emptyRow.remove();

        const isWon = (settleData.status === 'won');
        const now = new Date().toLocaleTimeString('en-GB', { hour12: false });

        const row = document.createElement('tr');
        row.className = 'history-row-slide';
        row.innerHTML = `
            <td class="text-secondary font-monospace" style="font-size:0.72rem;">${now}</td>
            <td><span class="badge bg-secondary bg-opacity-40 text-uppercase">${betType}</span></td>
            <td class="fw-bold font-monospace">₹${parseFloat(amount).toFixed(2)}</td>
            <td class="fw-bold text-warning font-monospace fs-6">${rolled}</td>
            <td>${isWon
                ? `<span class="badge bg-success rounded-pill px-2">+₹${settleData.win_amount}</span>`
                : `<span class="badge bg-danger rounded-pill px-2">LOST</span>`
            }</td>
        `;

        tbody.insertBefore(row, tbody.firstChild);
    }
}

// ── Global Helper Bindings for Blade Actions ──
let diceEngine = null;

document.addEventListener('DOMContentLoaded', function() {
    diceEngine = new DiceGameEngine({
        gameId: "{{ $game->id }}",
        csrfToken: "{{ csrf_token() }}",
        routes: {
            bet: "{{ route('games.bet') }}",
            settle: "{{ route('games.dice.settle') }}"
        },
        minBet: {{ $game->min_entry_fee }},
        maxBet: {{ $game->max_entry_fee }}
    });
});

function toggleDiceSound() {
    if (diceEngine && diceEngine.soundEngine) diceEngine.soundEngine.toggle();
}

function setDiceMode(mode) {
    if (diceEngine) diceEngine.setMode(mode);
}

function selectExactFace(n) {
    if (diceEngine) diceEngine.selectExactFace(n);
}

function addBetChip(val) {
    if (diceEngine) diceEngine.addChip(val);
}

function modifyBet(action) {
    if (diceEngine) diceEngine.modifyBet(action);
}

function onBetInputChanged() {
    if (diceEngine) diceEngine.updateLivePreview();
}

function executeDiceRoll() {
    if (diceEngine) diceEngine.executeRoll();
}
</script>
@endpush
@endsection
