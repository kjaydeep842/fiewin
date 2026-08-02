<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameHub Control Center - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --admin-primary:   #1E88E5;
            --admin-primary-2: #42A5F5;
            --admin-accent-green:  #22C55E;
            --admin-accent-red:    #EF4444;
            --admin-accent-orange: #F59E0B;
            --admin-bg:        #F4F8FC;
            --admin-card-bg:   #FFFFFF;
            --admin-sidebar-bg: #FFFFFF;
            --admin-border:    #E5E7EB;
            --admin-text:      #111827;
            --admin-muted:     #6B7280;
            --admin-radius:    14px;
            --admin-shadow:    0 4px 20px rgba(0, 0, 0, 0.06);
            --admin-active-bg: rgba(30, 136, 229, 0.10);
            --admin-active-color: #1E88E5;
        }

        body {
            background: var(--admin-bg);
            color: var(--admin-text);
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
        }

        /* ─── Sidebar ────────────────────────────────── */
        .admin-sidebar {
            width: 240px;
            background: var(--admin-sidebar-bg);
            height: 100vh;
            border-right: 1px solid var(--admin-border);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 2px 0 12px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }

        .admin-sidebar-brand {
            padding: 18px 20px;
            border-bottom: 1px solid var(--admin-border);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .admin-sidebar-brand .brand-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--admin-primary), #8B5CF6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.1rem;
        }

        .admin-sidebar-brand .brand-text h6 {
            font-weight: 700;
            margin: 0;
            font-size: 0.95rem;
            color: var(--admin-text);
        }

        .admin-sidebar-brand .brand-text small {
            font-size: 0.68rem;
            color: var(--admin-muted);
        }

        .admin-sidebar-nav {
            padding: 10px 12px;
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .admin-nav-section {
            font-size: 0.65rem;
            font-weight: 700;
            color: var(--admin-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 12px 10px 4px;
        }

        .admin-nav-link {
            color: var(--admin-muted);
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            border-radius: 10px;
            margin-bottom: 2px;
            transition: all 0.18s ease;
        }

        .admin-nav-link i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }

        .admin-nav-link:hover {
            background: rgba(30, 136, 229, 0.07);
            color: var(--admin-primary);
        }

        .admin-nav-link.active {
            background: var(--admin-active-bg);
            color: var(--admin-active-color);
            font-weight: 700;
        }

        .admin-nav-link.active i {
            color: var(--admin-primary);
        }

        .admin-sidebar-footer {
            padding: 12px;
            border-top: 1px solid var(--admin-border);
        }

        /* ─── Top Header ───────────────────────────────── */
        .admin-topbar {
            background: var(--admin-card-bg);
            border-bottom: 1px solid var(--admin-border);
            padding: 12px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 900;
            box-shadow: 0 1px 6px rgba(0,0,0,0.05);
        }

        /* ─── Content Area ─────────────────────────────── */
        .admin-content {
            margin-left: 240px;
            min-height: 100vh;
            background: var(--admin-bg);
        }

        .admin-page-body {
            padding: 28px;
        }

        /* ─── Cards ──────────────────────────────────── */
        .admin-card {
            background: var(--admin-card-bg);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius);
            box-shadow: var(--admin-shadow);
        }

        /* ─── Stat Cards ─────────────────────────────── */
        .admin-stat-card {
            background: var(--admin-card-bg);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius);
            box-shadow: var(--admin-shadow);
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .admin-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.10);
        }

        .admin-stat-card .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 12px;
        }

        /* ─── Tables ─────────────────────────────────── */
        .admin-table {
            font-size: 0.83rem;
        }

        .admin-table thead th {
            color: var(--admin-muted);
            font-weight: 600;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--admin-border);
            padding: 10px 12px;
            background: #FAFBFD;
        }

        .admin-table tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #F3F4F6;
            color: var(--admin-text);
            vertical-align: middle;
        }

        .admin-table tbody tr:hover td {
            background: #F9FAFB;
        }

        .admin-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ─── Badges ────────────────────────────────── */
        .badge-soft-success { background: rgba(34,197,94,0.12); color: #16a34a; font-weight: 600; }
        .badge-soft-danger  { background: rgba(239,68,68,0.12);  color: #dc2626; font-weight: 600; }
        .badge-soft-warning { background: rgba(245,158,11,0.12); color: #b45309; font-weight: 600; }
        .badge-soft-primary { background: rgba(30,136,229,0.12); color: #1E88E5; font-weight: 600; }
        .badge-soft-secondary { background: rgba(107,114,128,0.12); color: #4B5563; font-weight: 600; }

        /* ─── Form inputs in admin ──────────────────── */
        .admin-input {
            border: 1px solid var(--admin-border);
            border-radius: 10px;
            background: #FAFBFD;
            color: var(--admin-text);
            font-size: 0.875rem;
        }

        .admin-input:focus {
            background: #fff;
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 3px rgba(30,136,229,0.12);
        }

        /* ─── Buttons ──────────────────────────────── */
        .btn-admin-primary {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-2));
            color: #fff;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(30,136,229,0.3);
            transition: all 0.18s;
        }

        .btn-admin-primary:hover {
            box-shadow: 0 5px 16px rgba(30,136,229,0.4);
            color: #fff;
        }

        /* ─── Alert Banners ─────────────────────────── */
        .alert-admin {
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>

    <!-- ═══ SIDEBAR ═══ -->
    <div class="admin-sidebar">
        <div class="admin-sidebar-brand">
            <div class="brand-icon"><i class="bi bi-controller"></i></div>
            <div class="brand-text">
                <h6>GameHub</h6>
                <small>Control Center</small>
            </div>
        </div>

        <div class="admin-sidebar-nav">
            <div class="admin-nav-section">Main</div>

            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('admin.users.index') }}" class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> User Management
            </a>
            <a href="{{ route('admin.games.index') }}" class="admin-nav-link {{ request()->routeIs('admin.games.*') ? 'active' : '' }}">
                <i class="bi bi-sliders"></i> Game Engines & RTP
            </a>

            <div class="admin-nav-section mt-2">Game Controls</div>

            <a href="{{ route('admin.fast-parity.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.fast-parity.*') ? 'active' : '' }}">
                <i class="bi bi-lightning-charge-fill text-warning"></i> Fast Parity (30s)
            </a>
            <a href="{{ route('admin.parity.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.parity.*') ? 'active' : '' }}">
                <i class="bi bi-clock-fill text-primary"></i> Parity (3-Min)
            </a>
            <a href="{{ route('admin.mines-admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.mines-admin.*') ? 'active' : '' }}">
                <i class="bi bi-gem text-warning"></i> Mines
            </a>
            <a href="{{ route('admin.andar-bahar.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.andar-bahar.*') ? 'active' : '' }}">
                <i class="bi bi-suit-spade-fill text-primary"></i> Andar Bahar
            </a>
            <a href="{{ route('admin.jet.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.jet.*') ? 'active' : '' }}">
                <i class="bi bi-airplane-engines-fill text-success"></i> Jet Flight
            </a>
            <a href="{{ route('admin.crash-admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.crash-admin.*') ? 'active' : '' }}">
                <i class="bi bi-rocket-takeoff-fill text-danger"></i> Crash Rocket
            </a>
            <a href="{{ route('admin.spin-wheel.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.spin-wheel.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-repeat text-info"></i> Spin Wheel
            </a>
            <a href="{{ route('admin.dice-admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dice-admin.*') ? 'active' : '' }}">
                <i class="bi bi-dice-6-fill text-primary"></i> Dice Roll
            </a>

            <div class="admin-nav-section mt-1">Payments</div>

            <a href="{{ route('admin.merchants.index') }}" class="admin-nav-link {{ request()->routeIs('admin.merchants.*') ? 'active' : '' }}">
                <i class="bi bi-bank text-primary"></i> Merchant Accounts
            </a>
            <a href="{{ route('admin.deposits.index') }}" class="admin-nav-link {{ request()->routeIs('admin.deposits.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-down-left-circle text-success"></i> Deposits Approval
            </a>
            <a href="{{ route('admin.withdrawals.index') }}" class="admin-nav-link {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-up-right-circle text-danger"></i> Withdrawals Approval
            </a>

            <div class="admin-nav-section mt-1">Analytics</div>

            <a href="{{ route('admin.reports.index') }}" class="admin-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Financial Analytics
            </a>
        </div>

        <div class="admin-sidebar-footer">
            <a href="{{ route('home') }}" class="admin-nav-link text-warning mb-1">
                <i class="bi bi-box-arrow-up-right"></i> Return to Frontend
            </a>
            <form method="POST" action="{{ route('admin.logout') }}" class="m-0">
                @csrf
                <button type="submit" class="admin-nav-link border-0 bg-transparent w-100 text-start text-danger">
                    <i class="bi bi-power"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <!-- ═══ CONTENT AREA ═══ -->
    <div class="admin-content">

        <!-- Top Bar -->
        <div class="admin-topbar">
            <div>
                <span class="fw-semibold text-dark">
                    @yield('page-title', 'Admin Panel')
                </span>
                <span class="text-muted ms-2 small">@yield('page-subtitle')</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge badge-soft-success px-3 py-2 rounded-pill">
                    <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i> System Operational
                </span>
                @php
                    $adminUser = auth()->guard('admin')->user() ?? auth()->user();
                @endphp
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                         style="width:34px;height:34px;font-size:0.8rem;">
                        {{ strtoupper(substr($adminUser?->name ?? $adminUser?->username ?? 'A', 0, 1)) }}
                    </div>
                    <div class="d-none d-md-block">
                        <div class="fw-semibold" style="font-size:0.82rem;line-height:1.2;">
                            {{ $adminUser?->name ?? $adminUser?->username ?? 'Administrator' }}
                        </div>
                        <div class="text-muted" style="font-size:0.68rem;">Administrator</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-page-body">
            @if(session('success'))
                <div class="alert alert-admin alert-success bg-success bg-opacity-10 border border-success border-opacity-25 text-success mb-3">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-admin alert-danger bg-danger bg-opacity-10 border border-danger border-opacity-25 text-danger mb-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
