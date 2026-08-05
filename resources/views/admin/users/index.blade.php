@extends('layouts.admin')

@section('page-title', 'User Management')
@section('page-subtitle', 'Manage registered players, update balances, edit details, and manage status')

@section('content')

<!-- 2. USER MANAGEMENT SEARCH & MAIN TABLE -->
<div class="admin-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill text-primary me-2"></i>Registered Players Directory</h6>
            <span class="text-muted small">Total {{ $users->total() }} users on platform</span>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#sendNotifModal">
                <i class="bi bi-megaphone-fill me-1"></i>Send Special Offer / Alert
            </button>
            <!-- Search Form -->
            <form action="{{ route('admin.users.index') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm rounded-pill px-3" placeholder="Search by name, email, mobile..." value="{{ request('search') }}" style="width: 220px;">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="bi bi-search me-1"></i>Search
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Player Info</th>
                    <th>Main Wallet</th>
                    <th>Commission</th>
                    <th>Bank Cards</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="text-muted font-monospace">#{{ $user->id }}</td>
                    <td>
                        <div class="fw-bold text-dark"><i class="bi bi-person-fill text-primary me-1"></i>{{ $user->name }}</div>
                        <small class="text-muted">{{ $user->email }} | {{ $user->mobile }}</small>
                    </td>
                    <td class="fw-bold text-success font-monospace">₹{{ number_format($user->wallet?->main_balance ?? 0, 2) }}</td>
                    <td class="fw-bold text-warning font-monospace">₹{{ number_format($user->wallet?->commission_balance ?? 0, 2) }}</td>
                    <td>
                        @if($user->bankAccounts && $user->bankAccounts->count() > 0)
                            @foreach($user->bankAccounts as $bk)
                                <div class="small mb-1">
                                    <span class="badge {{ $bk->status === 'approved' ? 'bg-success' : ($bk->status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }} rounded-pill" style="font-size: 0.65rem;">
                                        {{ strtoupper($bk->status) }}
                                    </span>
                                    <span class="fw-semibold text-dark">{{ $bk->bank_name }}</span> (..{{ substr($bk->account_number, -4) }})
                                </div>
                            @endforeach
                        @else
                            <span class="text-muted small opacity-75">No Bank Added</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $user->status === 'active' ? 'badge-soft-success' : 'badge-soft-danger' }} rounded-pill px-3">
                            {{ strtoupper($user->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <!-- Toggle Status Button -->
                            <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $user->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-pill py-1 px-2" style="font-size: 0.75rem;" title="Toggle Status">
                                    {{ $user->status === 'active' ? 'Block' : 'Activate' }}
                                </button>
                            </form>

                            <!-- Edit Modal Trigger -->
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}" style="font-size: 0.75rem;">
                                <i class="bi bi-pencil-square me-1"></i>Edit
                            </button>

                            <!-- Delete User Button -->
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently DELETE user {{ $user->name }}? This action cannot be undone!');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill py-1 px-2" style="font-size: 0.75rem;" title="Delete User">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>

                        <!-- EDIT USER MODAL -->
                        <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Player #{{ $user->id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body text-start">
                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-bold">FULL NAME</label>
                                                <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-bold">EMAIL ADDRESS</label>
                                                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-bold">MOBILE NUMBER</label>
                                                <input type="text" name="mobile" class="form-control" value="{{ $user->mobile }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-bold">ACCOUNT STATUS</label>
                                                <select name="status" class="form-select">
                                                    <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>ACTIVE</option>
                                                    <option value="blocked" {{ $user->status === 'blocked' ? 'selected' : '' }}>BLOCKED</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-bold">KYC STATUS</label>
                                                <select name="kyc_status" class="form-select">
                                                    <option value="unverified" {{ $user->kyc_status === 'unverified' ? 'selected' : '' }}>UNVERIFIED</option>
                                                    <option value="pending" {{ $user->kyc_status === 'pending' ? 'selected' : '' }}>PENDING</option>
                                                    <option value="verified" {{ $user->kyc_status === 'verified' ? 'selected' : '' }}>VERIFIED</option>
                                                    <option value="rejected" {{ $user->kyc_status === 'rejected' ? 'selected' : '' }}>REJECTED</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-secondary small fw-bold">MAIN WALLET BALANCE (₹)</label>
                                                <input type="number" step="0.01" name="main_balance" class="form-control font-monospace fw-bold text-success" value="{{ $user->wallet?->main_balance ?? 0 }}">
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">SAVE CHANGES</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">No users found matching query</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $users->links() }}
    </div>
</div>

<!-- Send Special Offer / Broadcast Notification Modal -->
<div class="modal fade" id="sendNotifModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-megaphone-fill text-warning me-2"></i>Send Special Offer / Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.send-notification') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">TARGET RECIPIENT</label>
                        <select name="user_id" class="form-select">
                            <option value="">-- Broadcast to ALL Active Players --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->mobile }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">NOTIFICATION TYPE</label>
                        <select name="type" class="form-select">
                            <option value="promo">🎁 Special Offer / Promotion</option>
                            <option value="deposit_approved">💰 Deposit Alert</option>
                            <option value="withdrawal_approved">🎉 Withdrawal Alert</option>
                            <option value="general">🔔 General Alert</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">TITLE</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. 🎁 Weekend 50% Bonus Offer!" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">MESSAGE CONTENT</label>
                        <textarea name="message" class="form-control" rows="3" placeholder="e.g. Get a 50% instant bonus on your next deposit above ₹500 today!" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 fw-bold">SEND NOTIFICATION</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
