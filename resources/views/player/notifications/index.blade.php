@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">

    <!-- Top Title Card -->
    <div class="gh-card p-3 mb-3" style="background: linear-gradient(135deg, #1E88E5 0%, #1565C0 100%); color: #ffffff;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-bell-fill text-warning fs-5"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0 text-white" style="font-size: 1.1rem;">Notifications Center</h5>
                        <span class="small opacity-75" style="font-size: 0.78rem;">Track deposit, withdrawal & promo updates</span>
                    </div>
                </div>
            </div>

            @if($unreadCount > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-primary shadow-sm" style="font-size: 0.78rem;">
                        <i class="bi bi-check2-all me-1"></i> Mark All as Read ({{ $unreadCount }})
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Notification Feed Card -->
    <div class="gh-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.9rem;">
                <i class="bi bi-inbox-fill text-primary me-2"></i>Recent System Alerts
            </h6>
            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                Total: {{ $notifications->total() }}
            </span>
        </div>

        @forelse($notifications as $notif)
            @php
                $isUnread = !$notif->is_read;
                $bgClass = $isUnread ? 'bg-primary bg-opacity-10 border-primary border-opacity-25' : 'bg-white border-light-subtle';
                
                $iconClass = 'bi-bell-fill text-primary';
                $badgeBg = 'bg-primary';
                if (str_contains($notif->type, 'deposit')) {
                    $iconClass = 'bi-arrow-down-circle-fill text-success';
                    $badgeBg = 'bg-success';
                } elseif (str_contains($notif->type, 'withdrawal') && str_contains($notif->type, 'approved')) {
                    $iconClass = 'bi-check-circle-fill text-success';
                    $badgeBg = 'bg-success';
                } elseif (str_contains($notif->type, 'withdrawal') && str_contains($notif->type, 'rejected')) {
                    $iconClass = 'bi-x-circle-fill text-danger';
                    $badgeBg = 'bg-danger';
                } elseif (str_contains($notif->type, 'bank') && str_contains($notif->type, 'approved')) {
                    $iconClass = 'bi-patch-check-fill text-success';
                    $badgeBg = 'bg-success';
                } elseif (str_contains($notif->type, 'bank') && str_contains($notif->type, 'rejected')) {
                    $iconClass = 'bi-exclamation-triangle-fill text-danger';
                    $badgeBg = 'bg-danger';
                } elseif (str_contains($notif->type, 'promo') || str_contains($notif->type, 'offer')) {
                    $iconClass = 'bi-gift-fill text-warning';
                    $badgeBg = 'bg-warning text-dark';
                }
            @endphp

            <div class="card mb-2 rounded-3 border p-3 transition-all {{ $bgClass }}">
                <div class="d-flex align-items-start gap-3">
                    <div class="p-2 rounded-circle bg-white shadow-sm flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi {{ $iconClass }} fs-4"></i>
                    </div>

                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-1 mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.88rem;">{{ $notif->title }}</h6>
                                @if($isUnread)
                                    <span class="badge bg-danger rounded-pill px-2" style="font-size: 0.6rem;">NEW</span>
                                @endif
                            </div>
                            <span class="text-muted small" style="font-size: 0.72rem;">
                                <i class="bi bi-clock me-1"></i>{{ $notif->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <p class="text-secondary mb-2" style="font-size: 0.8rem; line-height: 1.4; word-break: break-word;">
                            {{ $notif->message }}
                        </p>

                        @if($isUnread)
                            <div class="d-flex justify-content-end">
                                <form action="{{ route('notifications.read', $notif->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-0" style="font-size: 0.72rem;">
                                        Mark Read
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-bell-slash fs-1 d-block mb-2 text-secondary opacity-40"></i>
                <h6 class="fw-semibold text-secondary mb-1">No Notifications Found</h6>
                <p class="small text-muted mb-0">You're all caught up! Deposit, withdrawal and promo alerts will appear here.</p>
            </div>
        @endforelse

        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
