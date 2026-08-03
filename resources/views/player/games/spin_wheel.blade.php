@extends('layouts.app')

@section('content')
<style>
    /* ── Spin Wheel Ultra-Premium Dark Casino Theme ── */
    :root {
        --spin-bg-dark: #0A0F1D;
        --spin-card-bg: rgba(15, 23, 42, 0.85);
        --spin-card-border: rgba(255, 255, 255, 0.12);
        --spin-accent-gold: #F59E0B;
        --spin-accent-cyan: #06B6D4;
        --spin-accent-green: #10B981;
        --spin-accent-purple: #6366F1;
        --spin-radius: 20px;
    }

    .spin-container {
        background: linear-gradient(180deg, #070B16 0%, #0F172A 100%);
        color: #F8FAFC;
        border-radius: var(--spin-radius);
        padding: 16px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
    }

    .spin-glass-card {
        background: var(--spin-card-bg);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid var(--spin-card-border);
        border-radius: 18px;
        padding: 18px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    /* Sector Info Badges */
    .sector-badge {
        font-weight: 800;
        font-size: 0.75rem;
        padding: 6px 12px;
        border-radius: 20px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.3);
        border: 1px solid rgba(255,255,255,0.2);
        transition: transform 0.2s ease;
    }
    .sector-badge:hover {
        transform: translateY(-2px) scale(1.05);
    }

    /* 3D Wheel Stage */
    .wheel-stage-wrapper {
        position: relative;
        display: inline-block;
        margin: 0 auto;
        padding: 15px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(245, 158, 11, 0.15) 0%, rgba(15, 23, 42, 0.8) 70%);
        box-shadow: inset 0 0 30px rgba(0,0,0,0.6), 0 10px 35px rgba(0,0,0,0.5);
    }

    /* Pointer Styling */
    .wheel-pointer-container {
        position: absolute;
        top: 2px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
        filter: drop-shadow(0 4px 10px rgba(0,0,0,0.7));
    }
    .wheel-pointer-arrow {
        width: 0;
        height: 0;
        border-left: 18px solid transparent;
        border-right: 18px solid transparent;
        border-top: 32px solid #F59E0B;
        position: relative;
    }
    .wheel-pointer-arrow::after {
        content: '';
        position: absolute;
        top: -32px;
        left: -12px;
        width: 0;
        height: 0;
        border-left: 12px solid transparent;
        border-right: 12px solid transparent;
        border-top: 22px solid #FDE047;
    }

    /* Canvas styling */
    #wheelCanvas {
        display: block;
        max-width: 100%;
        height: auto;
        border-radius: 50%;
        box-shadow: 0 0 25px rgba(245, 158, 11, 0.35);
        transition: box-shadow 0.3s ease;
    }

    /* Preset Bet Chips & Quick Action Buttons */
    .spin-chip-btn {
        background: rgba(245, 158, 11, 0.12);
        border: 1px solid rgba(245, 158, 11, 0.35);
        color: #FBBF24;
        border-radius: 20px;
        padding: 8px 14px;
        font-weight: 800;
        font-size: 0.82rem;
        transition: all 0.2s ease;
    }
    .spin-chip-btn:hover, .spin-chip-btn:active {
        background: rgba(245, 158, 11, 0.35);
        color: #FFFFFF;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }

    .spin-quick-btn {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #CBD5E1;
        border-radius: 10px;
        padding: 6px 12px;
        font-weight: 700;
        font-size: 0.75rem;
        transition: all 0.2s ease;
    }
    .spin-quick-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #FFFFFF;
    }

    /* CTA Spin Button */
    .btn-spin-cta {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        color: #FFFFFF;
        border: none;
        border-radius: 30px;
        padding: 16px;
        font-weight: 900;
        font-size: 1.2rem;
        letter-spacing: 0.8px;
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.45);
        transition: all 0.25s ease;
        width: 100%;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    .btn-spin-cta:hover:not(:disabled) {
        transform: translateY(-2px) scale(1.01);
        box-shadow: 0 12px 32px rgba(245, 158, 11, 0.65);
        filter: brightness(1.1);
    }
    .btn-spin-cta:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Payout Preview Card */
    .payout-preview-card {
        background: rgba(15, 23, 42, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 14px;
        padding: 12px;
    }

    /* Mobile adjustments */
    @media (max-width: 576px) {
        .spin-container {
            padding: 6px !important;
            border-radius: 0 !important;
        }
        .spin-glass-card {
            padding: 10px !important;
            border-radius: 14px !important;
        }
        .sector-badge {
            font-size: 0.65rem;
            padding: 3px 6px;
        }
        .spin-chip-btn {
            font-size: 0.75rem;
            padding: 6px 8px;
        }
    }

    @media (max-width: 380px) {
        .spin-header-title {
            font-size: 0.78rem !important;
        }
        .sector-badge {
            font-size: 0.62rem !important;
            padding: 3px 5px !important;
        }
        .wheel-stage-wrapper {
            padding: 6px !important;
        }
        #wheelCanvas {
            max-width: 230px !important;
            height: auto !important;
        }
        .spin-chip-btn {
            font-size: 0.7rem;
            padding: 5px 6px;
        }
        .btn-spin-cta {
            font-size: 1.05rem;
            padding: 12px;
        }
    }
</style>

<div class="spin-container mb-4">

    <!-- ── Top Navigation & Header Bar ── -->
    <div class="spin-glass-card mb-3 p-2 px-2 px-sm-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-1 gap-sm-2 min-w-0">
            <a href="{{ route('home') }}" class="btn btn-sm btn-dark rounded-circle border border-secondary text-white flex-shrink-0 d-flex align-items-center justify-content-center" style="width:28px; height:28px; padding:0;" title="Back to Home">
                <i class="bi bi-arrow-left" style="font-size:0.85rem;"></i>
            </a>
            <div class="d-flex align-items-center gap-1 gap-sm-2 min-w-0">
                <span class="p-1 px-2 rounded-3 text-white flex-shrink-0 d-none d-sm-inline-block" style="background: linear-gradient(135deg, #F59E0B, #06B6D4); font-size:0.85rem;">
                    <i class="bi bi-arrow-repeat"></i>
                </span>
                <div class="min-w-0">
                    <div class="fw-bold text-white lh-1 spin-header-title" style="white-space: nowrap;">Spin & Win Arena</div>
                    <small class="text-secondary d-none d-sm-block" style="font-size: 0.7rem;">Spin lucky sectors up to 50x multipliers!</small>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-1 gap-sm-2 flex-shrink-0">
            <!-- Sound Toggle Button -->
            <button type="button" id="spinSoundToggleBtn" class="btn btn-sm btn-outline-secondary rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center" style="width:28px; height:28px; padding:0;" onclick="toggleSpinSound()" title="Toggle Sound">
                <i class="bi bi-volume-up-fill text-warning" id="spinSoundIcon" style="font-size:0.8rem;"></i>
            </button>

            <!-- Rules Modal Trigger -->
            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2 py-1 fw-semibold flex-shrink-0" style="font-size: 0.7rem;" data-bs-toggle="modal" data-bs-target="#spinRulesModal">
                <i class="bi bi-info-circle me-1"></i>Rules
            </button>
        </div>
    </div>

    <!-- ── Multipliers Info Strip ── -->
    <div class="spin-glass-card mb-3 p-2 text-center">
        <small class="text-secondary fw-semibold d-block mb-2" style="font-size: 0.68rem; letter-spacing: 0.5px;">WHEEL SECTOR MULTIPLIERS</small>
        <div class="d-flex justify-content-center gap-1 gap-sm-2 flex-wrap">
            @php
            $sectorsInfo = [
                ['label' => '2X',  'color' => '#6366F1'],
                ['label' => '5X',  'color' => '#10B981'],
                ['label' => '10X', 'color' => '#F59E0B'],
                ['label' => '0X',  'color' => '#EF4444'],
                ['label' => '3X',  'color' => '#D946EF'],
                ['label' => '50X', 'color' => '#06B6D4'],
            ];
            @endphp
            @foreach($sectorsInfo as $s)
            <span class="sector-badge text-white" style="background:{{ $s['color'] }};">{{ $s['label'] }}</span>
            @endforeach
        </div>
    </div>

    <!-- ── 3D Wheel Stage & Pointer ── -->
    <div class="spin-glass-card mb-3 p-3 text-center">
        <div class="wheel-stage-wrapper">
            <!-- Top Golden Pointer -->
            <div class="wheel-pointer-container">
                <div class="wheel-pointer-arrow"></div>
            </div>

            <!-- Canvas Wheel -->
            <canvas id="wheelCanvas" width="300" height="300"></canvas>
        </div>

        <!-- Result Banner -->
        <div id="spinResultBanner" class="mt-3 d-none">
            <span id="spinResultText" class="badge fs-6 px-4 py-2 rounded-pill shadow-lg"></span>
        </div>
    </div>

    <!-- ── Bet Controls Card ── -->
    <div class="spin-glass-card mb-3 p-3">
        <!-- Header & Quick Actions -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <small class="text-secondary fw-bold" style="font-size: 0.75rem;">BET AMOUNT (₹)</small>
            <div class="d-flex gap-1">
                <button type="button" class="spin-quick-btn" onclick="modifySpinBet('half')">½</button>
                <button type="button" class="spin-quick-btn" onclick="modifySpinBet('double')">2×</button>
                <button type="button" class="spin-quick-btn" onclick="modifySpinBet('max')">MAX</button>
                <button type="button" class="spin-quick-btn" onclick="modifySpinBet('clear')">CLEAR</button>
            </div>
        </div>

        <!-- Preset Chips Grid -->
        <div class="d-flex gap-2 justify-content-center flex-wrap mb-3">
            @foreach([10, 50, 100, 500, 1000, 5000] as $chip)
            <button type="button" class="spin-chip-btn flex-fill" onclick="addSpinChip({{ $chip }})">+₹{{ $chip }}</button>
            @endforeach
        </div>

        <!-- Custom Bet Input -->
        <div class="input-group mb-3">
            <span class="input-group-text bg-dark border-secondary text-warning fw-bold fs-5">₹</span>
            <input type="number" id="spinBetAmount"
                   class="form-control bg-dark border-secondary text-white fw-bold fs-4 font-monospace text-center"
                   value="50"
                   min="{{ $game->min_entry_fee }}"
                   max="{{ $game->max_entry_fee }}">
        </div>

        <!-- Payout Calculation Preview -->
        <div class="payout-preview-card mb-3">
            <div class="d-flex justify-content-between align-items-center font-monospace" style="font-size:0.82rem;">
                <span class="text-secondary">Selected Bet:</span>
                <span id="previewBet" class="fw-bold text-white fs-6">₹50.00</span>
            </div>
            <div class="d-flex justify-content-between align-items-center font-monospace mt-1" style="font-size:0.82rem;">
                <span class="text-secondary">Max Win Potential (50x):</span>
                <span id="previewMax" class="fw-bold text-success fs-6">₹2,500.00</span>
            </div>
        </div>

        <!-- SPIN NOW CTA BUTTON -->
        <button id="spinBtn" onclick="spinWheel()" class="btn btn-spin-cta">
            <i class="bi bi-arrow-repeat me-2"></i>SPIN WHEEL NOW
        </button>
    </div>

    <!-- ── Statistics Dashboard ── -->
    <div class="row g-2 mb-3">
        <div class="col-4">
            <div class="spin-glass-card p-2 text-center">
                <small class="text-secondary d-block" style="font-size: 0.62rem;">TOTAL SPINS</small>
                <span id="spinStatTotal" class="fw-bold text-info font-monospace" style="font-size: 0.85rem;">0</span>
            </div>
        </div>
        <div class="col-4">
            <div class="spin-glass-card p-2 text-center">
                <small class="text-secondary d-block" style="font-size: 0.62rem;">WIN RATE</small>
                <span id="spinStatWinRate" class="fw-bold text-success font-monospace" style="font-size: 0.85rem;">0.0%</span>
            </div>
        </div>
        <div class="col-4">
            <div class="spin-glass-card p-2 text-center">
                <small class="text-secondary d-block" style="font-size: 0.62rem;">TOTAL PROFIT</small>
                <span id="spinStatProfit" class="fw-bold text-warning font-monospace" style="font-size: 0.85rem;">₹0.00</span>
            </div>
        </div>
    </div>

    <!-- ── Live Spin History Table ── -->
    <div class="spin-glass-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-white mb-0" style="font-size: 0.88rem;">
                <i class="bi bi-clock-history me-2 text-warning"></i>My Spin History
            </h6>
            <span class="badge bg-secondary bg-opacity-30 text-secondary" style="font-size: 0.65rem;">Auto Sync</span>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle small text-center mb-0" style="background: transparent;">
                <thead class="text-secondary border-secondary">
                    <tr>
                        <th style="font-size: 0.72rem;">Time</th>
                        <th style="font-size: 0.72rem;">Bet (₹)</th>
                        <th style="font-size: 0.72rem;">Multiplier</th>
                        <th style="font-size: 0.72rem;">Result</th>
                    </tr>
                </thead>
                <tbody id="spinHistoryBody">
                    @forelse($myBets as $bet)
                    <tr>
                        <td class="text-secondary font-monospace" style="font-size: 0.72rem;">{{ $bet->created_at->format('H:i:s') }}</td>
                        <td class="fw-bold font-monospace">₹{{ number_format($bet->bet_amount, 2) }}</td>
                        <td>
                            <span class="badge rounded-pill px-2 py-1"
                                  style="background:{{ $bet->multiplier >= 10 ? '#EAB308' : ($bet->multiplier >= 3 ? '#6366F1' : ($bet->multiplier > 0 ? '#10B981' : '#EF4444')) }}">
                                {{ $bet->multiplier > 0 ? $bet->multiplier . 'X' : '0X' }}
                            </span>
                        </td>
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
                    <tr><td colspan="4" class="text-secondary py-3">No spins recorded yet. Spin to win!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── RULES MODAL ── -->
<div class="modal fade" id="spinRulesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 360px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow bg-dark text-white overflow-hidden">
            <div class="p-3 text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                <h6 class="fw-bold mb-0"><i class="bi bi-controller me-2"></i>Spin Wheel Rules</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 small text-secondary">
                <p><strong class="text-white">1. Sectors & Multipliers:</strong> The lucky wheel contains 6 sectors with multipliers ranging from <span class="text-danger">0X</span> up to <span class="text-info">50X</span>.</p>
                <p><strong class="text-white">2. Winning Payouts:</strong> Landing on any sector > 0X immediately multiplies your bet amount. Landing on 0X forfeits the bet amount.</p>
                <p class="mb-0 text-warning"><i class="bi bi-shield-check me-1"></i>All spin outcomes are generated using cryptographically verified server-side random seeds.</p>
            </div>
        </div>
    </div>
</div>

<!-- ── WIN MODAL ── -->
<div class="modal fade" id="spinWinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow bg-dark text-white overflow-hidden text-center">
            <div class="p-3 text-white" style="background: linear-gradient(135deg, #10B981, #059669);">
                <div class="fs-1 mb-1"><i class="bi bi-trophy-fill text-warning"></i></div>
                <h5 class="fw-bold mb-0">CONGRATULATIONS!</h5>
            </div>
            <div class="modal-body p-3">
                <p class="text-secondary small mb-2">
                    You landed on <span id="spinWinMult" class="fw-bold text-warning fs-5">2X</span> multiplier!
                </p>
                <div class="p-2 bg-dark border border-success rounded-3 mb-3">
                    <small class="text-secondary d-block">TOTAL WINNINGS</small>
                    <h3 id="spinWinAmount" class="fw-bold text-success mb-0 font-monospace">+₹0.00</h3>
                </div>
                <button type="button" class="btn gh-btn-success w-100 py-2 rounded-pill fw-bold" data-bs-dismiss="modal">
                    SPIN AGAIN
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── LOSS MODAL ── -->
<div class="modal fade" id="spinLossModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px; margin: auto;">
        <div class="modal-content rounded-4 border-0 shadow bg-dark text-white overflow-hidden text-center">
            <div class="p-3 text-white" style="background: linear-gradient(135deg, #EF4444, #DC2626);">
                <div class="fs-2 mb-1"><i class="bi bi-emoji-frown-fill"></i></div>
                <h5 class="fw-bold mb-0">0X — UNLUCKY!</h5>
            </div>
            <div class="modal-body p-3">
                <p class="text-secondary small mb-3">You hit 0X multiplier. Spin again for big wins!</p>
                <button type="button" class="btn btn-outline-danger w-100 py-2 rounded-pill fw-bold" data-bs-dismiss="modal">
                    TRY AGAIN
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ──────────────────────────────────────────────────────────────────────────
    // Sound Effects Synthesiser Engine
    // ──────────────────────────────────────────────────────────────────────────
    class SpinSoundEngine {
        constructor() {
            this.enabled = (localStorage.getItem('spin_sound_enabled') !== 'false');
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
            localStorage.setItem('spin_sound_enabled', this.enabled ? 'true' : 'false');
            this.updateIcon();
        }

        updateIcon() {
            const icon = document.getElementById('spinSoundIcon');
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
            } catch (e) {}
        }

        play(soundType) {
            if (!this.enabled) return;
            if (soundType === 'click') this.playTone(700, 'sine', 0.08, 0.05);
            else if (soundType === 'tick') this.playTone(480, 'triangle', 0.05, 0.08);
            else if (soundType === 'win') {
                this.playTone(523.25, 'sine', 0.15, 0.15);
                setTimeout(() => this.playTone(659.25, 'sine', 0.15, 0.15), 100);
                setTimeout(() => this.playTone(783.99, 'sine', 0.35, 0.2), 200);
            } else if (soundType === 'lose') {
                this.playTone(240, 'sawtooth', 0.2, 0.15);
                setTimeout(() => this.playTone(180, 'sawtooth', 0.3, 0.15), 150);
            }
        }
    }

    const soundEngine = new SpinSoundEngine();
    function toggleSpinSound() { soundEngine.toggle(); }

    // ──────────────────────────────────────────────────────────────────────────
    // Sector Definitions & Canvas Engine Setup
    // ──────────────────────────────────────────────────────────────────────────
    const spinWinModal  = new bootstrap.Modal(document.getElementById('spinWinModal'));
    const spinLossModal = new bootstrap.Modal(document.getElementById('spinLossModal'));

    const SECTORS = [
        { label: '2X',  color: '#6366F1', mult: 2  },
        { label: '5X',  color: '#10B981', mult: 5  },
        { label: '10X', color: '#F59E0B', mult: 10 },
        { label: '0X',  color: '#EF4444', mult: 0  },
        { label: '3X',  color: '#D946EF', mult: 3  },
        { label: '50X', color: '#06B6D4', mult: 50 },
    ];
    const NUM_SECTORS  = SECTORS.length;
    const ARC          = (2 * Math.PI) / NUM_SECTORS;
    const POINTER_ANGLE = -Math.PI / 2; // top of canvas

    const canvas   = document.getElementById('wheelCanvas');
    const ctx      = canvas.getContext('2d');
    const CX       = canvas.width / 2;
    const CY       = canvas.height / 2;
    const RADIUS   = CX - 12;

    let rotationAngle = 0;
    let activeBetId   = null;
    let isSpinning    = false;

    let stats = {
        totalSpins: 0,
        wins: 0,
        totalProfit: 0
    };

    // ──────────────────────────────────────────────────────────────────────────
    // Metallic 3D Wheel Renderer
    // ──────────────────────────────────────────────────────────────────────────
    function drawWheel(rotation) {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // 1. Draw Metallic Outer Ring Rim
        ctx.save();
        ctx.beginPath();
        ctx.arc(CX, CY, RADIUS + 8, 0, 2 * Math.PI);
        const rimGradient = ctx.createRadialGradient(CX, CY, RADIUS, CX, CY, RADIUS + 10);
        rimGradient.addColorStop(0, '#F59E0B');
        rimGradient.addColorStop(0.5, '#FDE047');
        rimGradient.addColorStop(1, '#B45309');
        ctx.fillStyle = rimGradient;
        ctx.fill();

        // Outer Ring Stud Bulbs (LEDs)
        const numBulbs = 24;
        for (let b = 0; b < numBulbs; b++) {
            const bulbAngle = (b * 2 * Math.PI) / numBulbs + rotation * 0.5;
            const bx = CX + (RADIUS + 4) * Math.cos(bulbAngle);
            const by = CY + (RADIUS + 4) * Math.sin(bulbAngle);
            ctx.beginPath();
            ctx.arc(bx, by, 3, 0, 2 * Math.PI);
            ctx.fillStyle = (b % 2 === 0 || isSpinning) ? '#FFFFFF' : '#FEF08A';
            ctx.shadowColor = '#F59E0B';
            ctx.shadowBlur = 4;
            ctx.fill();
            ctx.shadowBlur = 0;
        }
        ctx.restore();

        // 2. Draw Sectors
        SECTORS.forEach((s, i) => {
            const startAngle = POINTER_ANGLE - ARC / 2 + i * ARC + rotation;
            const endAngle   = startAngle + ARC;

            ctx.save();
            ctx.beginPath();
            ctx.moveTo(CX, CY);
            ctx.arc(CX, CY, RADIUS, startAngle, endAngle);
            ctx.closePath();

            // Gradient Fill for Glossy Sector Effect
            const midAngle = startAngle + ARC / 2;
            const gx = CX + RADIUS * Math.cos(midAngle);
            const gy = CY + RADIUS * Math.sin(midAngle);
            const secGrad = ctx.createLinearGradient(CX, CY, gx, gy);
            secGrad.addColorStop(0, s.color);
            secGrad.addColorStop(1, adjustColorBrightness(s.color, -25));
            ctx.fillStyle = secGrad;
            ctx.fill();

            // White dividing stroke
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.7)';
            ctx.lineWidth   = 2.5;
            ctx.stroke();

            // Sector Label Text
            const textR = RADIUS * 0.65;
            const tx    = CX + textR * Math.cos(midAngle);
            const ty    = CY + textR * Math.sin(midAngle);

            ctx.translate(tx, ty);
            ctx.rotate(midAngle + Math.PI / 2);
            ctx.textAlign    = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle    = '#FFFFFF';
            ctx.font         = '900 17px system-ui, sans-serif';
            ctx.shadowColor  = 'rgba(0,0,0,0.6)';
            ctx.shadowBlur   = 6;
            ctx.fillText(s.label, 0, 0);
            ctx.restore();
        });

        // 3. Draw Metallic Center Cap
        ctx.save();
        ctx.beginPath();
        ctx.arc(CX, CY, 22, 0, 2 * Math.PI);
        const centerGrad = ctx.createRadialGradient(CX - 4, CY - 4, 2, CX, CY, 22);
        centerGrad.addColorStop(0, '#FFFFFF');
        centerGrad.addColorStop(0.4, '#FDE047');
        centerGrad.addColorStop(1, '#B45309');
        ctx.fillStyle = centerGrad;
        ctx.shadowColor = 'rgba(0,0,0,0.5)';
        ctx.shadowBlur = 8;
        ctx.fill();

        ctx.beginPath();
        ctx.arc(CX, CY, 12, 0, 2 * Math.PI);
        ctx.fillStyle = '#0F172A';
        ctx.fill();

        ctx.fillStyle = '#F59E0B';
        ctx.font = '900 8px system-ui, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('SPIN', CX, CY);
        ctx.restore();
    }

    function adjustColorBrightness(hex, percent) {
        let num = parseInt(hex.replace('#',''), 16),
            amt = Math.round(2.55 * percent),
            R = (num >> 16) + amt,
            B = (num >> 8 & 0x00FF) + amt,
            G = (num & 0x0000FF) + amt;
        return '#' + (0x1000000 + (R<255?R<1?0:R:255)*0x10000 + (B<255?B<1?0:B:255)*0x100 + (G<255?G<1?0:G:255)).toString(16).slice(1);
    }

    drawWheel(rotationAngle);

    // ──────────────────────────────────────────────────────────────────────────
    // Spin Physics Calculations
    // ──────────────────────────────────────────────────────────────────────────
    function computeTargetRotation(winnerIdx) {
        const jitter         = (Math.random() - 0.5) * ARC * 0.35;
        const sectorRotation = -winnerIdx * ARC + jitter;
        const fullSpins      = (6 + Math.floor(Math.random() * 4)) * 2 * Math.PI;

        const curNorm   = ((rotationAngle % (2 * Math.PI)) + 2 * Math.PI) % (2 * Math.PI);
        const rawTarget = ((sectorRotation % (2 * Math.PI)) + 2 * Math.PI) % (2 * Math.PI);
        const diff      = (rawTarget - curNorm + 2 * Math.PI) % (2 * Math.PI);

        return rotationAngle + fullSpins + diff;
    }

    function getSectorAtPointer(rotation) {
        const offset = ((ARC / 2 - rotation) % (2 * Math.PI) + 2 * Math.PI) % (2 * Math.PI);
        return Math.floor(offset / ARC) % NUM_SECTORS;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Bet Modifiers & Quick Buttons
    // ──────────────────────────────────────────────────────────────────────────
    function addSpinChip(val) {
        soundEngine.play('click');
        const input = document.getElementById('spinBetAmount');
        let cur = parseFloat(input.value) || 0;
        input.value = Math.min({{ $game->max_entry_fee }}, cur + val);
        updatePreview();
    }

    function modifySpinBet(action) {
        soundEngine.play('click');
        const input = document.getElementById('spinBetAmount');
        let cur = parseFloat(input.value) || 0;
        const minBet = {{ $game->min_entry_fee }};
        const maxBet = {{ $game->max_entry_fee }};

        if (action === 'half') cur = Math.max(minBet, Math.floor(cur / 2));
        else if (action === 'double') cur = Math.min(maxBet, cur * 2);
        else if (action === 'max') cur = maxBet;
        else if (action === 'clear') cur = minBet;

        input.value = cur;
        updatePreview();
    }

    function updatePreview() {
        const bet = parseFloat(document.getElementById('spinBetAmount').value) || 0;
        document.getElementById('previewBet').innerText = '₹' + bet.toFixed(2);
        document.getElementById('previewMax').innerText = '₹' + (bet * 50).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    document.getElementById('spinBetAmount').addEventListener('input', updatePreview);
    updatePreview();

    // ──────────────────────────────────────────────────────────────────────────
    // MAIN SPIN FUNCTION
    // ──────────────────────────────────────────────────────────────────────────
    async function spinWheel() {
        if (isSpinning) return;

        const betAmount = parseFloat(document.getElementById('spinBetAmount').value);
        const minBet    = {{ $game->min_entry_fee }};
        const maxBet    = {{ $game->max_entry_fee }};

        if (isNaN(betAmount) || betAmount < minBet || betAmount > maxBet) {
            alert(`Bet amount must be between ₹${minBet} and ₹${maxBet}`);
            return;
        }

        const btn    = document.getElementById('spinBtn');
        isSpinning   = true;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>SPINNING WHEEL...';

        document.getElementById('spinResultBanner').classList.add('d-none');

        // Step 1: Pick deterministic winner index
        const winnerIdx = Math.floor(Math.random() * NUM_SECTORS);

        // Step 2: Backend Bet Placement Request
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
                alert(data.message || 'Failed to place bet. Check your balance.');
                resetBtn(btn);
                return;
            }

            activeBetId = data.bet.id;
            updateTopWalletBalance(data.new_balance);

            // Step 3: Animate Wheel Rotation (Smooth Cubic Ease-Out)
            const targetRotation = computeTargetRotation(winnerIdx);
            const startRotation  = rotationAngle;
            const startTime      = performance.now();
            const DURATION       = 3600; // ms

            function easeOut(t) { return 1 - Math.pow(1 - t, 3.5); }

            let lastTickAngle = startRotation;

            function animate(now) {
                const elapsed  = now - startTime;
                const progress = Math.min(elapsed / DURATION, 1);
                const eased    = easeOut(progress);
                const angle    = startRotation + (targetRotation - startRotation) * eased;

                drawWheel(angle);

                // Tick Sound Effect on sector boundaries
                if (Math.abs(angle - lastTickAngle) > 0.28) {
                    soundEngine.play('tick');
                    lastTickAngle = angle;
                }

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    rotationAngle = targetRotation;
                    drawWheel(rotationAngle);

                    const actualIdx    = getSectorAtPointer(rotationAngle);
                    const actualSector = SECTORS[actualIdx];

                    // Step 4: Settle with server
                    settleResult(actualSector, btn);
                }
            }

            requestAnimationFrame(animate);

        } catch (err) {
            console.error('Spin error:', err);
            alert('Network connection error. Please try again.');
            resetBtn(btn);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SETTLE RESULT
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

                const banner  = document.getElementById('spinResultBanner');
                const bannerT = document.getElementById('spinResultText');
                const won     = sector.mult > 0;
                const betAmount = parseFloat(document.getElementById('spinBetAmount').value);

                // Update Stats
                stats.totalSpins++;
                if (won) {
                    stats.wins++;
                    stats.totalProfit += (data.win_amount - betAmount);
                } else {
                    stats.totalProfit -= betAmount;
                }
                updateStatsDisplay();

                banner.classList.remove('d-none');
                bannerT.className = 'badge fs-6 px-4 py-2 rounded-pill shadow-lg ' + (won ? 'bg-success' : 'bg-danger');
                bannerT.innerText = won
                    ? `🎉 ${sector.label} MULTIPLIER LANDED — +₹${data.win_amount}`
                    : `💥 ${sector.label} — BETTER LUCK NEXT TIME!`;

                if (won) {
                    soundEngine.play('win');
                    if (window.animationManager) {
                        window.animationManager.triggerConfetti(60);
                        window.animationManager.animateCoinsToWallet(banner);
                    }
                } else {
                    soundEngine.play('lose');
                    if (window.animationManager) window.animationManager.shakeScreen();
                }

                prependHistoryRow(sector, data);

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
            alert('Settlement network error.');
        } finally {
            resetBtn(btn);
        }
    }

    function updateStatsDisplay() {
        document.getElementById('spinStatTotal').innerText = stats.totalSpins;
        const rate = stats.totalSpins > 0 ? ((stats.wins / stats.totalSpins) * 100).toFixed(1) : '0.0';
        document.getElementById('spinStatWinRate').innerText = rate + '%';

        const profitEl = document.getElementById('spinStatProfit');
        profitEl.innerText = (stats.totalProfit >= 0 ? '+₹' : '-₹') + Math.abs(stats.totalProfit).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        profitEl.className = 'fw-bold font-monospace ' + (stats.totalProfit >= 0 ? 'text-success' : 'text-danger');
    }

    function prependHistoryRow(sector, settle) {
        const tbody = document.getElementById('spinHistoryBody');
        const now   = new Date().toLocaleTimeString('en-GB', { hour12: false });
        const bet   = parseFloat(document.getElementById('spinBetAmount').value);
        const won   = sector.mult > 0;

        const bgMap = { 50: '#06B6D4', 10: '#EAB308', 5: '#10B981', 3: '#D946EF', 2: '#6366F1', 0: '#EF4444' };
        const color = bgMap[sector.mult] ?? '#6C757D';

        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="text-secondary font-monospace" style="font-size:0.72rem;">${now}</td>
            <td class="fw-bold font-monospace">₹${bet.toFixed(2)}</td>
            <td><span class="badge rounded-pill px-2 py-1" style="background:${color}">${sector.label}</span></td>
            <td>${won
                ? `<span class="badge bg-success rounded-pill px-2">+₹${settle.win_amount}</span>`
                : `<span class="badge bg-danger rounded-pill px-2">LOST</span>`
            }</td>`;

        const empty = tbody.querySelector('td[colspan]');
        if (empty) empty.closest('tr').remove();
        tbody.insertBefore(row, tbody.firstChild);
    }

    function resetBtn(btn) {
        isSpinning    = false;
        btn.disabled  = false;
        btn.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i>SPIN WHEEL NOW';
    }

    function updateTopWalletBalance(balStr) {
        const topEl = document.getElementById('topWalletBalance');
        if (topEl) topEl.innerText = '₹' + balStr;
    }
</script>
@endpush
@endsection
