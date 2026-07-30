<nav class="gh-bottom-nav">
    <a href="{{ route('home') }}" class="gh-nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
        <i class="bi bi-house-door-fill"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('games.index') }}" class="gh-nav-item {{ request()->routeIs('games.*') ? 'active' : '' }}">
        <i class="bi bi-controller"></i>
        <span>Games</span>
    </a>
    <a href="{{ route('wallet.index') }}" class="gh-nav-item {{ request()->routeIs('wallet.*') ? 'active' : '' }}">
        <i class="bi bi-wallet-fill"></i>
        <span>Wallet</span>
    </a>
    <a href="{{ route('referral.index') }}" class="gh-nav-item {{ request()->routeIs('referral.*') ? 'active' : '' }}">
        <i class="bi bi-people-fill"></i>
        <span>Referral</span>
    </a>
    <a href="{{ route('promotion.index') }}" class="gh-nav-item {{ request()->routeIs('promotion.*') ? 'active' : '' }}">
        <i class="bi bi-gift-fill"></i>
        <span>Reward</span>
    </a>
    <a href="{{ route('profile.index') }}" class="gh-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
        <i class="bi bi-person-fill"></i>
        <span>Profile</span>
    </a>
</nav>
