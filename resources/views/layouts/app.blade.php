<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>GameHub - Next-Gen Gaming Platform</title>
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --gh-primary-blue: #1E88E5;
            --gh-secondary-blue: #42A5F5;
            --gh-accent-green: #22C55E;
            --gh-accent-orange: #F59E0B;
            --gh-accent-red: #EF4444;
            --gh-accent-purple: #8B5CF6;
            --gh-bg: #F4F8FC;
            --gh-card-bg: #FFFFFF;
            --gh-border: #E5E7EB;
            --gh-text-primary: #111827;
            --gh-text-secondary: #6B7280;
            --gh-radius: 16px;
            --gh-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            --gh-shadow-hover: 0 20px 30px -10px rgba(30, 136, 229, 0.15);
        }

        html, body {
            overflow-x: hidden !important;
            width: 100%;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
        }

        body {
            background-color: var(--gh-bg);
            color: var(--gh-text-primary);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            user-select: none;
            -webkit-user-select: none;
            -webkit-tap-highlight-color: transparent;
        }

        .gh-app-viewport {
            max-width: 480px;
            width: 100%;
            margin: 0 auto;
            background: #FAFCFE;
            min-height: 100vh;
            box-shadow: 0 0 35px rgba(0, 0, 0, 0.08);
            position: relative;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Top Header Navigation */
        .gh-top-header {
            background: var(--gh-card-bg);
            border-bottom: 1px solid var(--gh-border);
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1040;
            width: 100%;
        }

        .gh-wallet-pill {
            background: rgba(30, 136, 229, 0.08);
            border: 1px solid rgba(30, 136, 229, 0.2);
            border-radius: 24px;
            padding: 4px 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Buttons */
        .gh-btn-primary {
            background: linear-gradient(135deg, var(--gh-primary-blue), var(--gh-secondary-blue));
            color: #ffffff;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            padding: 10px 18px;
            box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
            transition: all 0.2s ease;
        }

        .gh-btn-primary:hover, .gh-btn-primary:active {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30, 136, 229, 0.4);
            color: #ffffff;
        }

        .gh-btn-success {
            background: linear-gradient(135deg, var(--gh-accent-green), #16a34a);
            color: #ffffff;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
        }

        .gh-btn-orange {
            background: linear-gradient(135deg, var(--gh-accent-orange), #d97706);
            color: #ffffff;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        /* Floating 16px Cards */
        .gh-card {
            background: var(--gh-card-bg);
            border: 1px solid var(--gh-border);
            border-radius: var(--gh-radius);
            box-shadow: var(--gh-shadow);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .gh-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--gh-shadow-hover);
        }

        /* App Body Container */
        .gh-main-content {
            padding: 14px;
            padding-bottom: 90px !important; /* Space for bottom fixed nav */
            flex: 1 0 auto;
            width: 100%;
            overflow-x: hidden;
        }

        /* Bottom Sheet Navigation */
        .gh-bottom-navigation {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 480px;
            height: calc(64px + env(safe-area-inset-bottom, 0px));
            padding-bottom: env(safe-area-inset-bottom, 0px);
            background: var(--gh-card-bg);
            border-top: 1px solid var(--gh-border);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1050;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
        }

        .gh-nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: var(--gh-text-secondary);
            text-decoration: none;
            font-size: 0.72rem;
            font-weight: 500;
            transition: color 0.2s;
            padding: 4px 8px;
        }

        .gh-nav-link i {
            font-size: 1.35rem;
            margin-bottom: 2px;
        }

        .gh-nav-link.active {
            color: var(--gh-primary-blue);
            font-weight: 600;
        }

        .gh-nav-link.active i {
            transform: scale(1.1);
        }

        /* Announcement Marquee */
        .gh-announcement-bar {
            background: rgba(30, 136, 229, 0.06);
            border-bottom: 1px solid rgba(30, 136, 229, 0.12);
            padding: 8px 14px;
            font-size: 0.8rem;
            color: var(--gh-primary-blue);
            width: 100%;
            overflow: hidden;
        }

        /* Button Ripple Effect */
        .gh-ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            animation: rippleAnim 0.6s linear;
            pointer-events: none;
        }

        @keyframes rippleAnim {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* Screen Shake Impact */
        .gh-shake-impact {
            animation: shakeScreenAnim 0.4s cubic-bezier(.36,.07,.19,.97) both;
        }

        @keyframes shakeScreenAnim {
            10%, 90% { transform: translate3d(-2px, 0, 0); }
            20%, 80% { transform: translate3d(3px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-5px, 0, 0); }
            40%, 60% { transform: translate3d(5px, 0, 0); }
        }

        /* Countdown Timer States */
        .timer-normal {
            color: #22C55E !important;
            font-size: 1.6rem !important;
            font-weight: 800 !important;
            text-shadow: 0 0 10px rgba(34, 197, 94, 0.3);
            animation: normalTimerPulse 1s infinite ease-in-out;
        }

        @keyframes normalTimerPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.03); }
        }

        .timer-warning-10s {
            color: #EF4444 !important;
            font-size: 1.85rem !important;
            font-weight: 900 !important;
            text-shadow: 0 0 16px rgba(239, 68, 68, 0.7);
            animation: heartbeat10s 0.7s infinite ease-in-out !important;
        }

        @keyframes heartbeat10s {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.12); text-shadow: 0 0 25px rgba(239, 68, 68, 0.95); }
        }

        .timer-critical-5s {
            color: #DC2626 !important;
            font-size: 2.1rem !important;
            font-weight: 900 !important;
            text-shadow: 0 0 22px rgba(220, 38, 38, 1);
            animation: heartbeat5s 0.4s infinite ease-in-out !important;
        }

        @keyframes heartbeat5s {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* Flying Coin Particle */
        .gh-flying-coin {
            animation: coinSpin 0.7s infinite linear;
        }

        @keyframes coinSpin {
            0% { transform: rotateY(0deg); }
            100% { transform: rotateY(360deg); }
        }

        /* Skeleton Loading Placeholder */
        .gh-skeleton {
            background: linear-gradient(90deg, #E5E7EB 25%, #F3F4F6 50%, #E5E7EB 75%);
            background-size: 200% 100%;
            animation: skeletonLoading 1.5s infinite ease-in-out;
            border-radius: 8px;
        }

        @keyframes skeletonLoading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body>

    <div class="gh-app-viewport">
        <!-- Top App Bar -->
        <header class="gh-top-header">
            <a href="{{ route('home') }}" class="d-flex align-items-center text-decoration-none fw-bold fs-5 text-dark me-2">
                <span class="p-1 px-2 rounded-3 me-2 text-white" style="background: linear-gradient(135deg, var(--gh-primary-blue), var(--gh-accent-purple));"><i class="bi bi-controller"></i></span>
                <span>Game<span style="color: var(--gh-primary-blue);">Hub</span></span>
            </a>

            <div class="d-flex align-items-center gap-1">
                <!-- Sound Toggle Button -->
                <button type="button" id="soundToggleBtn" class="btn btn-sm btn-light border rounded-circle" onclick="togglePlatformSound()" title="Toggle Sound">
                    <i class="bi bi-volume-up-fill text-primary" id="soundToggleIcon"></i>
                </button>

                @auth
                    <a href="{{ route('wallet.index') }}" class="text-decoration-none gh-wallet-pill">
                        <i class="bi bi-wallet2 text-primary fs-6"></i>
                        <span id="topWalletBalance" class="fw-bold text-dark font-monospace" style="font-size: 0.82rem;">₹{{ number_format(auth()->user()->wallet?->main_balance ?? 0, 2) }}</span>
                    </a>
                    <a href="{{ route('wallet.index') }}" class="btn btn-sm gh-btn-success rounded-pill px-2 py-1 text-decoration-none fw-bold" style="font-size: 0.75rem;">DEPOSIT</a>
                @else
                    <div class="d-flex gap-1">
                        <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary rounded-pill px-2 fw-medium" style="font-size: 0.8rem;">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-sm gh-btn-primary rounded-pill px-2" style="font-size: 0.8rem;">Register</a>
                    </div>
                @endauth
            </div>
        </header>

        <!-- Live Announcement Ticker -->
        <div class="gh-announcement-bar d-flex align-items-center">
            <i class="bi bi-megaphone-fill text-warning me-2 fs-6 flex-shrink-0"></i>
            <div class="gh-marquee-wrap">
                <marquee behavior="scroll" direction="left" scrollamount="4">
                    🎉 Welcome to GameHub! Instant 5% bonus on UPI deposits. Player***41 won ₹3,450 on Mines! Rocket hit 52.4x on Crash!
                </marquee>
            </div>
        </div>

        <!-- App Body -->
        <main class="gh-main-content">
            @if(session('success'))
                <div class="alert alert-success bg-success bg-opacity-10 border-success text-success p-3 rounded-4 mb-3 alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger p-3 rounded-4 mb-3 alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Bottom Navigation Bar -->
        <nav class="gh-bottom-navigation">
            <a href="{{ route('home') }}" class="gh-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="bi bi-house-door-fill"></i>
                <span class="d-none d-md-inline">Home</span>
                <span class="d-inline d-md-none">Home</span>
            </a>
            <a href="{{ route('games.index') }}" class="gh-nav-link {{ request()->routeIs('games.*') ? 'active' : '' }}">
                <i class="bi bi-controller"></i>
                <span class="d-none d-md-inline">Games</span>
                <span class="d-inline d-md-none">Games</span>
            </a>
            <a href="{{ route('wallet.index') }}" class="gh-nav-link {{ request()->routeIs('wallet.*') ? 'active' : '' }}">
                <i class="bi bi-wallet-fill"></i>
                <span class="d-none d-md-inline">Wallet</span>
                <span class="d-inline d-md-none">Wallet</span>
            </a>
            <a href="{{ route('promotion.index') }}" class="gh-nav-link {{ request()->routeIs('promotion.*') ? 'active' : '' }}">
                <i class="bi bi-gift-fill"></i>
                <span class="d-none d-md-inline">Rewards</span>
                <span class="d-inline d-md-none">Rewards</span>
            </a>
            <a href="{{ route('profile.index') }}" class="gh-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="bi bi-person-fill"></i>
                <span class="d-none d-md-inline">Profile</span>
                <span class="d-inline d-md-none">Profile</span>
            </a>
        </nav>
    </div>

    <!-- SoundManager & AnimationManager Engine Scripts -->
    <script src="{{ asset('js/sound-manager.js') }}"></script>
    <script src="{{ asset('js/animation-manager.js') }}"></script>
    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePlatformSound() {
            if (window.soundManager) {
                const muted = window.soundManager.toggleMute();
                const icon = document.getElementById('soundToggleIcon');
                if (icon) {
                    icon.className = muted ? 'bi bi-volume-mute-fill text-secondary' : 'bi bi-volume-up-fill text-primary';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (window.soundManager) {
                const icon = document.getElementById('soundToggleIcon');
                if (icon && window.soundManager.isMuted) {
                    icon.className = 'bi bi-volume-mute-fill text-secondary';
                }
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
