@extends('layouts.app')

@section('content')
<style>
    .history-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }

    /* Step Tracker Progress Bar */
    .step-tracker {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin: 20px 0 10px;
    }

    .step-tracker::before {
        content: "";
        position: absolute;
        top: 15px;
        left: 15%;
        right: 15%;
        height: 3px;
        background: #e2e8f0;
        z-index: 1;
    }

    .step-item {
        position: relative;
        z-index: 2;
        text-align: center;
        flex: 1;
    }

    .step-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 6px;
        font-weight: 700;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }

    .step-item.active .step-circle {
        background: #1E88E5;
        color: #ffffff;
        box-shadow: 0 0 0 4px rgba(30, 136, 229, 0.2);
    }

    .step-item.completed .step-circle {
        background: #10B981;
        color: #ffffff;
    }

    .step-item.rejected .step-circle {
        background: #EF4444;
        color: #ffffff;
    }

    .step-title {
        font-size: 0.72rem;
        font-weight: 700;
        color: #64748b;
    }

    .step-item.active .step-title { color: #1E88E5; }
    .step-item.completed .step-title { color: #10B981; }
    .step-item.rejected .step-title { color: #EF4444; }
</style>

<!-- Header Banner -->
<div class="gh-card p-4 mb-3 text-white" style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); border-radius: 20px;">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-clock-history text-primary me-2"></i>Transaction & Withdrawal Records</h4>
            <p class="text-white-50 small mb-0">Track all your deposits, withdrawals, and live settlement stages transparently.</p>
        </div>
        <a href="{{ route('wallet.index') }}" class="btn btn-sm btn-outline-light rounded-pill px-3 fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Back to Wallet
        </a>
    </div>
</div>

<!-- Main History Tabs -->
<div class="gh-card p-3 mb-4">
    <ul class="nav nav-pills nav-justified mb-3 gap-2" id="historyTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold py-2 rounded-3" id="withdrawals-tab" data-bs-toggle="pill" data-bs-target="#tabWithdrawals" style="font-size: 0.85rem;">
                <i class="bi bi-arrow-up-circle-fill me-1"></i> Withdrawals Tracking
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold py-2 rounded-3" id="deposits-tab" data-bs-toggle="pill" data-bs-target="#tabDeposits" style="font-size: 0.85rem;">
                <i class="bi bi-arrow-down-circle-fill me-1"></i> Recharge & Deposits
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold py-2 rounded-3" id="ledger-tab" data-bs-toggle="pill" data-bs-target="#tabLedger" style="font-size: 0.85rem;">
                <i class="bi bi-journal-text me-1"></i> Wallet Ledger
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- ── TAB 1: WITHDRAWALS TRACKING ─────────────────── -->
        <div class="tab-pane fade show active" id="tabWithdrawals">
            @forelse($withdrawals as $w)
                <div class="history-card p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 flex-wrap gap-2">
                        <div>
                            <span class="badge bg-light text-dark border font-monospace me-2" style="font-size: 0.72rem;">{{ $w->transaction_id }}</span>
                            <span class="text-secondary small"><i class="bi bi-calendar-event me-1"></i>{{ $w->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        <div class="text-end">
                            <h5 class="fw-bold text-dark mb-0 font-monospace">₹{{ number_format($w->amount, 2) }}</h5>
                            <small class="text-muted" style="font-size: 0.7rem;">Net Transfer: ₹{{ number_format($w->net_amount, 2) }} (Fee: ₹{{ number_format($w->fee, 2) }})</small>
                        </div>
                    </div>

                    <!-- Step Progress Bar -->
                    @php
                        $isApproved = ($w->status === 'approved');
                        $isRejected = ($w->status === 'rejected');
                        $isPending  = ($w->status === 'pending');
                    @endphp
                    <div class="step-tracker">
                        <div class="step-item completed">
                            <div class="step-circle"><i class="bi bi-check-lg"></i></div>
                            <div class="step-title">1. SUBMITTED</div>
                        </div>

                        <div class="step-item {{ $isPending ? 'active' : ($isApproved || $isRejected ? 'completed' : '') }}">
                            <div class="step-circle">
                                @if($isPending) <i class="bi bi-arrow-repeat spin"></i>
                                @else <i class="bi bi-check-lg"></i>
                                @endif
                            </div>
                            <div class="step-title">2. ADMIN REVIEW</div>
                        </div>

                        <div class="step-item {{ $isApproved ? 'completed' : ($isRejected ? 'rejected' : '') }}">
                            <div class="step-circle">
                                @if($isApproved) <i class="bi bi-check-lg"></i>
                                @elseif($isRejected) <i class="bi bi-x-lg"></i>
                                @else 3
                                @endif
                            </div>
                            <div class="step-title">
                                @if($isApproved) 3. TRANSFERRED
                                @elseif($isRejected) 3. REJECTED
                                @else 3. SETTLEMENT
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Account Info & Status Footer -->
                    <div class="p-2 bg-light rounded-3 d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                        <div class="small text-secondary">
                            <i class="bi bi-bank me-1 text-primary"></i> Target:
                            @if($w->bankAccount)
                                <strong>{{ $w->bankAccount->bank_name }}</strong> (A/C: {{ $w->bankAccount->account_number }})
                            @elseif($w->upi_id)
                                <strong>UPI:</strong> {{ $w->upi_id }}
                            @else
                                Bank Account
                            @endif
                        </div>
                        <div>
                            @if($isApproved)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1 rounded-pill">
                                    <i class="bi bi-patch-check-fill me-1"></i> APPROVED & TRANSFERRED
                                </span>
                            @elseif($isRejected)
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-1 rounded-pill">
                                    <i class="bi bi-x-circle-fill me-1"></i> REJECTED (REFUNDED)
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-1 rounded-pill">
                                    <i class="bi bi-clock-history me-1"></i> PROCESSING WITHDRAWAL
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-secondary">
                    <i class="bi bi-inbox display-4 d-block text-muted opacity-50 mb-2"></i>
                    <p class="mb-0">No withdrawal requests submitted yet.</p>
                </div>
            @endforelse
        </div>

        <!-- ── TAB 2: RECHARGE & DEPOSITS ──────────────────── -->
        <div class="tab-pane fade" id="tabDeposits">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th>Tx ID</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Bonus</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deposits as $d)
                            <tr>
                                <td class="font-monospace fw-bold">{{ $d->transaction_id }}</td>
                                <td class="text-uppercase"><span class="badge bg-light text-dark border">{{ $d->payment_method }}</span></td>
                                <td class="fw-bold text-success">₹{{ number_format($d->amount, 2) }}</td>
                                <td class="text-primary">+₹{{ number_format($d->bonus_amount, 2) }}</td>
                                <td class="text-secondary small">{{ $d->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    @if($d->status === 'approved')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">APPROVED</span>
                                    @elseif($d->status === 'rejected')
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">REJECTED</span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">PENDING</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-secondary">No deposit records found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── TAB 3: WALLET LEDGER ────────────────────────── -->
        <div class="tab-pane fade" id="tabLedger">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0" style="font-size: 0.82rem;">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Ref ID</th>
                            <th>Description</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $t)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary text-uppercase">{{ str_replace('_', ' ', $t->transaction_type) }}</span>
                                </td>
                                <td class="fw-bold {{ $t->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $t->amount >= 0 ? '+' : '' }}₹{{ number_format($t->amount, 2) }}
                                </td>
                                <td class="font-monospace text-muted">{{ $t->reference_id ?? '-' }}</td>
                                <td>{{ $t->description ?? '-' }}</td>
                                <td class="text-secondary">{{ $t->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-secondary">No wallet transactions recorded</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
