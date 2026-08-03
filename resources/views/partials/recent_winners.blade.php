<!-- Live Winners Card Partial -->
<style>
    /* Live Pulse Badge */
    .live-pulse-dot {
        width: 8px;
        height: 8px;
        background-color: #22c55e;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
        animation: livePulse 1.6s infinite;
    }
    @keyframes livePulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }

    /* Recent Winners Wrapper & Ticker Container */
    .recent-winners-wrapper {
        height: 240px;
        overflow: hidden;
        position: relative;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid #eef2f6;
    }
    .recent-winners-container {
        display: flex;
        flex-direction: column;
        transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1);
    }
    .winner-row {
        background: #ffffff;
        transition: all 0.4s ease;
        min-height: 48px;
    }
    .winner-row:nth-child(even) {
        background: #f8fafc;
    }
    .winner-row.new-entry {
        animation: winnerFlash 1.4s ease-out;
    }
    @keyframes winnerFlash {
        0% { background-color: #d1fae5; transform: scale(1.01); }
        100% { background-color: #ffffff; transform: scale(1); }
    }

    .avatar-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 600;
        flex-shrink: 0;
    }

    /* Game Badges Styling */
    .game-badge {
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.65rem !important;
        letter-spacing: 0.02em;
    }
    .game-badge.fast-parity, .game-badge.parity {
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
        border: 1px solid rgba(37, 99, 235, 0.2);
    }
    .game-badge.mines {
        background: rgba(217, 119, 6, 0.1);
        color: #d97706;
        border: 1px solid rgba(217, 119, 6, 0.2);
    }
    .game-badge.crash {
        background: rgba(220, 38, 38, 0.1);
        color: #dc2626;
        border: 1px solid rgba(220, 38, 38, 0.2);
    }
    .game-badge.jet {
        background: rgba(147, 51, 234, 0.1);
        color: #9333ea;
        border: 1px solid rgba(147, 51, 234, 0.2);
    }
    .game-badge.spin-wheel {
        background: rgba(13, 148, 136, 0.1);
        color: #0d9488;
        border: 1px solid rgba(13, 148, 136, 0.2);
    }
    .game-badge.dice-roll, .game-badge.dice {
        background: rgba(22, 163, 74, 0.1);
        color: #16a34a;
        border: 1px solid rgba(22, 163, 74, 0.2);
    }
    .game-badge.andar-bahar {
        background: rgba(79, 70, 229, 0.1);
        color: #4f46e5;
        border: 1px solid rgba(79, 70, 229, 0.2);
    }
</style>

<div class="gh-card p-3 shadow-sm mb-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center">
            <i class="bi bi-trophy-fill text-warning fs-5 me-2"></i>Recent Winners
        </h6>
        <span class="badge bg-success bg-opacity-10 text-success d-flex align-items-center px-2 py-1" style="font-size: 0.65rem; border: 1px solid rgba(34,197,94,0.2);">
            <span class="live-pulse-dot me-1"></span> LIVE
        </span>
    </div>

    {{-- Continuous Ticker Window --}}
    <div class="recent-winners-wrapper" id="recentWinnersWrapper">
        <div class="recent-winners-container" id="recentWinnersContainer">
            @if(isset($liveWinners) && count($liveWinners) > 0)
                @foreach($liveWinners as $w)
                    @php
                        $gameSlug = strtolower(str_replace(' ', '-', $w['game']));
                        $avatarColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'];
                        $bgColor = $avatarColors[abs(crc32($w['user'])) % count($avatarColors)];
                    @endphp
                    <div class="winner-row d-flex align-items-center justify-content-between py-2 px-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-circle" style="background: {{ $bgColor }}18; color: {{ $bgColor }};">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <span class="fw-semibold text-dark user-name" style="font-size: 0.82rem;">{{ $w['user'] }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge game-badge {{ $gameSlug }}">{{ $w['game'] }}</span>
                            <span class="fw-bold text-success win-amount" style="font-size: 0.85rem;">+₹{{ number_format($w['amount'], 2) }}</span>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('recentWinnersContainer');
    const wrapper = document.getElementById('recentWinnersWrapper');
    if (!container || !wrapper) return;

    const gamesList = [
        { name: 'Fast Parity', class: 'fast-parity' },
        { name: 'Parity', class: 'parity' },
        { name: 'Mines', class: 'mines' },
        { name: 'Crash', class: 'crash' },
        { name: 'Jet', class: 'jet' },
        { name: 'Spin Wheel', class: 'spin-wheel' },
        { name: 'Dice Roll', class: 'dice-roll' },
        { name: 'Andar Bahar', class: 'andar-bahar' }
    ];

    const prefixes = ['User', 'Player', 'Winner', 'Lucky', 'Pro', 'Star', 'Royal', 'Vip', 'King', 'Jack', 'Master', 'Ace', 'Hero', 'Super', 'Champ'];
    const avatarColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'];

    function getRandomItem(arr) {
        return arr[Math.floor(Math.random() * arr.length)];
    }

    function getRandomAmount() {
        const amounts = [180, 450, 850, 1200, 1850, 2400, 3450, 5200, 7800, 9500, 12400, 18000, 27500];
        const base = getRandomItem(amounts);
        const variation = Math.floor(Math.random() * 90);
        return (base + variation).toFixed(2);
    }

    function generateWinner() {
        const prefix = getRandomItem(prefixes);
        const num = Math.floor(10 + Math.random() * 89);
        const game = getRandomItem(gamesList);
        const amount = getRandomAmount();
        return {
            user: `${prefix}***${num}`,
            game: game.name,
            gameClass: game.class,
            amount: amount
        };
    }

    function createWinnerElement(winner) {
        const row = document.createElement('div');
        const gameClass = winner.gameClass || winner.game.toLowerCase().replace(/\s+/g, '-');
        row.className = 'winner-row d-flex align-items-center justify-content-between py-2 px-3 border-bottom new-entry';

        const avatarBg = getRandomItem(avatarColors);

        const formattedAmount = Number(winner.amount).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        row.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <div class="avatar-circle" style="background:${avatarBg}18; color:${avatarBg};">
                    <i class="bi bi-person-fill"></i>
                </div>
                <span class="fw-semibold text-dark user-name" style="font-size: 0.82rem;">${winner.user}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge game-badge ${gameClass}">${winner.game}</span>
                <span class="fw-bold text-success win-amount" style="font-size: 0.85rem;">+₹${formattedAmount}</span>
            </div>
        `;
        return row;
    }

    function pushNextWinner(winnerObj) {
        const newRow = createWinnerElement(winnerObj);
        container.insertBefore(newRow, container.firstChild);

        // Keep maximum 25 items in DOM for optimal performance
        if (container.children.length > 25) {
            container.removeChild(container.lastChild);
        }
    }

    let isPaused = false;
    wrapper.addEventListener('mouseenter', () => isPaused = true);
    wrapper.addEventListener('mouseleave', () => isPaused = false);
    wrapper.addEventListener('touchstart', () => isPaused = true);
    wrapper.addEventListener('touchend', () => isPaused = false);

    // Continuous ticker timer: Prepend fresh winner every 2.0s
    setInterval(() => {
        if (!isPaused) {
            const winner = generateWinner();
            pushNextWinner(winner);
        }
    }, 2000);

    // Fetch real DB wins periodically to inject live player victories
    async function syncRealDbWinners() {
        try {
            const res = await fetch("{{ route('recent-winners.feed') }}");
            if (res.ok) {
                const data = await res.json();
                if (data.status === 'success' && data.winners && data.winners.length > 0) {
                    const topWin = data.winners[0];
                    if (topWin && !topWin.id.startsWith('sim_')) {
                        pushNextWinner({
                            user: topWin.user,
                            game: topWin.game,
                            gameClass: topWin.game.toLowerCase().replace(/\s+/g, '-'),
                            amount: topWin.amount
                        });
                    }
                }
            }
        } catch (e) {
            // Silently fallback to procedural stream
        }
    }

    setInterval(syncRealDbWinners, 10000);
});
</script>
