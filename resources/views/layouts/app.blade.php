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

        body {
            background-color: var(--gh-bg);
            color: var(--gh-text-primary);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            padding-bottom: 75px;
            user-select: none;
            -webkit-user-select: none;
        }

        .gh-app-viewport {
            max-width: 480px;
            margin: 0 auto;
            background: #FAFCFE;
            min-height: 100vh;
            box-shadow: 0 0 35px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        /* Top Header Navigation */
        .gh-top-header {
            background: var(--gh-card-bg);
            border-bottom: 1px solid var(--gh-border);
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1040;
        }

        .gh-wallet-pill {
            background: rgba(30, 136, 229, 0.08);
            border: 1px solid rgba(30, 136, 229, 0.2);
            border-radius: 24px;
            padding: 6px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
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

        /* Bottom Sheet Navigation */
        .gh-bottom-navigation {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 480px;
            height: 64px;
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
            padding: 8px 16px;
            font-size: 0.8rem;
            color: var(--gh-primary-blue);
        }
    </style>
</head>
<body>

    <div class="gh-app-viewport">
        <!-- Top App Bar -->
        <header class="gh-top-header">
            <a href="{{ route('home') }}" class="d-flex align-items-center text-decoration-none fw-bold fs-4 text-dark">
                <span class="p-1 px-2 rounded-3 me-2 text-white" style="background: linear-gradient(135deg, var(--gh-primary-blue), var(--gh-accent-purple));"><i class="bi bi-controller"></i></span>
                <span>Game<span style="color: var(--gh-primary-blue);">Hub</span></span>
            </a>

            @auth
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('wallet.index') }}" class="text-decoration-none gh-wallet-pill">
                        <i class="bi bi-wallet2 text-primary fs-6"></i>
                        <span id="topWalletBalance" class="fw-bold text-dark font-monospace" style="font-size: 0.85rem;">₹{{ number_format(auth()->user()->wallet?->main_balance ?? 0, 2) }}</span>
                    </a>
                    <a href="{{ route('wallet.index') }}" class="btn btn-sm gh-btn-success rounded-pill px-3 py-1 text-decoration-none">DEPOSIT</a>
                </div>
            @else
                <div class="d-flex gap-2">
                    <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-medium">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-sm gh-btn-primary rounded-pill px-3">Register</a>
                </div>
            @endauth
        </header>

        <!-- Live Announcement Ticker -->
        <div class="gh-announcement-bar d-flex align-items-center">
            <i class="bi bi-megaphone-fill text-warning me-2 fs-6"></i>
            <marquee behavior="scroll" direction="left" scrollamount="4">
                🎉 Welcome to GameHub! Instant 5% bonus on UPI deposits. Player***41 won ₹3,450 on Mines! Rocket hit 52.4x on Crash!
            </marquee>
        </div>

        <!-- App Body -->
        <main class="p-3">
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

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
