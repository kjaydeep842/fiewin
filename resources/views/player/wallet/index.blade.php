@extends('layouts.app')

@section('content')
<style>
    /* ── Wallet Page Styles ── */
    .wallet-summary-card {
        background: linear-gradient(135deg, #1E88E5 0%, #0D47A1 100%);
        color: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(30, 136, 229, 0.25);
    }

    .wallet-stat-box {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 8px 6px;
    }

    /* Segmented Tab Nav */
    .wallet-nav-pills {
        background: rgba(15, 23, 42, 0.05);
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 16px;
        padding: 4px;
        display: flex;
        gap: 4px;
    }

    .wallet-nav-pills .nav-link {
        flex: 1;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 10px 6px;
        color: #64748b;
        transition: all 0.2s ease;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .wallet-nav-pills .nav-link.active {
        background: #1E88E5 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
    }

    /* Payment Method Cards */
    .pm-card-input {
        display: none;
    }

    .pm-card-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 12px 8px;
        border-radius: 14px;
        border: 2px solid #e2e8f0;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s ease;
        height: 100%;
        position: relative;
    }

    .pm-card-input:checked + .pm-card-label {
        border-color: #1E88E5;
        background: rgba(30, 136, 229, 0.06);
        box-shadow: 0 4px 12px rgba(30, 136, 229, 0.15);
    }

    .pm-card-input:checked + .pm-card-label::after {
        content: "\F272";
        font-family: "bootstrap-icons";
        position: absolute;
        top: 6px;
        right: 8px;
        color: #1E88E5;
        font-size: 0.9rem;
    }

    .chip-btn {
        font-size: 0.8rem;
        font-weight: 700;
        border-radius: 20px;
        padding: 6px 12px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #334155;
        transition: all 0.15s ease;
    }

    .chip-btn:hover, .chip-btn:active {
        background: #1E88E5;
        color: #ffffff;
        border-color: #1E88E5;
    }
</style>

<!-- Wallet Summary Card -->
<div class="gh-card wallet-summary-card p-3 p-sm-4 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="d-block opacity-75 fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.05em;">TOTAL WALLET BALANCE</span>
            <h2 class="fw-bold mb-0 font-monospace" style="font-size: clamp(1.4rem, 5.5vw, 2.2rem); letter-spacing: -0.02em;">
                ₹{{ number_format($wallet->total_balance, 2) }}
            </h2>
        </div>
        <span class="badge bg-white text-primary fw-bold rounded-pill px-3 py-2 shadow-sm d-flex align-items-center gap-1" style="font-size: 0.72rem;">
            <i class="bi bi-shield-lock-fill text-success fs-6"></i>SECURE
        </span>
    </div>

    <!-- Sub-balances 3 Column Grid -->
    <div class="row g-2 text-center pt-2">
        <div class="col-4">
            <div class="wallet-stat-box">
                <span class="d-block opacity-75 text-truncate" style="font-size: 0.62rem; font-weight: 600;">MAIN BALANCE</span>
                <span class="fw-bold font-monospace text-truncate d-block" style="font-size: 0.78rem;">₹{{ number_format($wallet->main_balance, 2) }}</span>
            </div>
        </div>
        <div class="col-4">
            <div class="wallet-stat-box">
                <span class="d-block opacity-75 text-truncate" style="font-size: 0.62rem; font-weight: 600;">BONUS WALLET</span>
                <span class="fw-bold font-monospace text-truncate d-block" style="font-size: 0.78rem;">₹{{ number_format($wallet->bonus_balance, 2) }}</span>
            </div>
        </div>
        <div class="col-4">
            <div class="wallet-stat-box">
                <span class="d-block opacity-75 text-truncate" style="font-size: 0.62rem; font-weight: 600;">COMMISSION</span>
                <span class="fw-bold font-monospace text-truncate d-block" style="font-size: 0.78rem;">₹{{ number_format($wallet->commission_balance, 2) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Wallet Action Card (Deposit, Withdraw, Transfer) -->
<div class="gh-card p-3 mb-3">
    <!-- Segmented Navigation Pills -->
    <ul class="nav wallet-nav-pills mb-3" role="tablist">
        <li class="nav-item flex-fill">
            <button class="nav-link active w-100" data-bs-toggle="pill" data-bs-target="#pillDeposit">
                <i class="bi bi-plus-circle-fill"></i>Deposit
            </button>
        </li>
        <li class="nav-item flex-fill">
            <button class="nav-link w-100" data-bs-toggle="pill" data-bs-target="#pillWithdraw">
                <i class="bi bi-dash-circle-fill"></i>Withdraw
            </button>
        </li>
        <li class="nav-item flex-fill">
            <button class="nav-link w-100" data-bs-toggle="pill" data-bs-target="#pillTransfer">
                <i class="bi bi-arrow-left-right"></i>Transfer
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Deposit Tab -->
        <div class="tab-pane fade show active" id="pillDeposit">
            <form action="{{ route('wallet.deposit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold mb-2">SELECT PAYMENT METHOD</label>
                    <div class="row g-2">
                        @foreach($paymentMethods as $m)
                            <div class="col-6">
                                <input type="radio" name="payment_method" id="pm_{{ $m->code }}" value="{{ $m->code }}" class="pm-card-input" required {{ $loop->first ? 'checked' : '' }}>
                                <label for="pm_{{ $m->code }}" class="pm-card-label py-3 px-2">
                                    @if(str_contains($m->code, 'upi'))
                                        <i class="bi bi-qr-code-scan text-primary fs-4 mb-1"></i>
                                    @else
                                        <i class="bi bi-bank text-primary fs-4 mb-1"></i>
                                    @endif
                                    <span class="fw-bold text-dark" style="font-size: 0.82rem; line-height: 1.2;">{{ $m->name }}</span>
                                    @if($m->bonus_percentage > 0)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 mt-1" style="font-size: 0.65rem;">
                                            +{{ number_format($m->bonus_percentage, 2) }}% Extra
                                        </span>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold mb-2">DEPOSIT AMOUNT (₹)</label>
                    <div class="d-flex gap-2 mb-2 flex-wrap">
                        <button type="button" class="btn chip-btn flex-fill" onclick="setDeposit(200)">₹200</button>
                        <button type="button" class="btn chip-btn flex-fill" onclick="setDeposit(500)">₹500</button>
                        <button type="button" class="btn chip-btn flex-fill" onclick="setDeposit(1000)">₹1000</button>
                        <button type="button" class="btn chip-btn flex-fill" onclick="setDeposit(5000)">₹5000</button>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light fw-bold text-secondary">₹</span>
                        <input type="number" name="amount" id="depositAmountInput" class="form-control form-control-lg fw-bold text-dark" value="500" min="10" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold mb-1">UTR / TRANSACTION REFERENCE NO. (OPTIONAL)</label>
                    <input type="text" name="utr_number" class="form-control form-control-lg" placeholder="e.g. 329871239812">
                    <small class="text-muted" style="font-size: 0.72rem;">You can also enter UTR & upload payment proof on the next checkout screen.</small>
                </div>

                <button type="submit" class="btn gh-btn-success w-100 py-3 fs-6 fw-bold rounded-3 shadow">
                    <i class="bi bi-wallet2 me-2"></i>PROCEED TO RECHARGE
                </button>
            </form>
        </div>

        <!-- Withdraw Tab -->
        <div class="tab-pane fade" id="pillWithdraw">
            @if($bankAccounts->isEmpty())
                <div class="alert alert-warning border border-warning border-opacity-25 rounded-3 p-3 mb-3">
                    <div class="d-flex align-items-center gap-2 mb-1 text-warning-emphasis fw-bold" style="font-size: 0.88rem;">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i> Bank Account Approval Required
                    </div>
                    <p class="mb-2 text-dark small" style="font-size: 0.8rem;">
                        @if(($allBankAccounts->count() ?? 0) > 0)
                            Your bank account is currently <strong>PENDING ADMIN VERIFICATION</strong>. You will be able to submit withdrawals as soon as your bank card is approved by Admin.
                        @else
                            You have no <strong>APPROVED</strong> bank card linked. Please add your bank details in Profile for admin verification before requesting a withdrawal.
                        @endif
                    </p>
                    <a href="{{ route('profile.index') }}" class="btn btn-sm btn-warning rounded-pill fw-bold text-dark px-3" style="font-size: 0.75rem;">
                        <i class="bi bi-person-gear me-1"></i> Add / Check Bank Status in Profile
                    </a>
                </div>
            @endif

            <form action="{{ route('wallet.withdraw') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold mb-1">WITHDRAWAL AMOUNT (₹)</label>
                    <div class="input-group mb-1">
                        <span class="input-group-text bg-light fw-bold text-secondary">₹</span>
                        <input type="number" name="amount" class="form-control form-control-lg fw-bold text-dark" placeholder="Min ₹100" min="100" required {{ $bankAccounts->isEmpty() ? 'disabled' : '' }}>
                    </div>
                    <small class="text-muted" style="font-size: 0.72rem;">Available Balance: ₹{{ number_format($wallet->main_balance, 2) }}</small>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold mb-1">APPROVED BANK ACCOUNT</label>
                    <select name="bank_account_id" class="form-select" {{ $bankAccounts->isEmpty() ? 'disabled' : '' }}>
                        @forelse($bankAccounts as $b)
                            <option value="{{ $b->id }}">{{ $b->bank_name }} - {{ $b->account_number }} ({{ $b->account_holder }})</option>
                        @empty
                            <option value="">No approved bank card available</option>
                        @endforelse
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold mb-1">OR ENTER UPI ID</label>
                    <input type="text" name="upi_id" class="form-control" placeholder="username@upi" {{ $bankAccounts->isEmpty() ? 'disabled' : '' }}>
                </div>

                <button type="submit" class="btn gh-btn-primary w-100 py-3 fs-6 fw-bold rounded-3 shadow" {{ $bankAccounts->isEmpty() ? 'disabled' : '' }}>
                    <i class="bi bi-arrow-up-circle-fill me-2"></i>SUBMIT WITHDRAWAL
                </button>

                <a href="{{ route('wallet.history') }}" class="btn btn-outline-secondary btn-sm w-100 rounded-pill mt-3 py-2 fw-semibold">
                    <i class="bi bi-clock-history me-1"></i> Track Live Withdrawal Progress & History
                </a>
            </form>
        </div>

        <!-- Transfer Tab -->
        <div class="tab-pane fade" id="pillTransfer">
            <form action="{{ route('wallet.transfer') }}" method="POST">
                @csrf
                <div class="p-3 bg-light rounded-3 border mb-3 text-center">
                    <small class="text-secondary d-block fw-semibold mb-1">AVAILABLE COMMISSION BALANCE</small>
                    <h4 class="fw-bold text-primary mb-0 font-monospace">₹{{ number_format($wallet->commission_balance, 2) }}</h4>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold mb-1">TRANSFER AMOUNT TO MAIN WALLET (₹)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light fw-bold text-secondary">₹</span>
                        <input type="number" name="amount" class="form-control form-control-lg fw-bold text-dark" value="{{ $wallet->commission_balance }}" min="10" max="{{ $wallet->commission_balance }}" required>
                    </div>
                </div>
                <button type="submit" class="btn gh-btn-success w-100 py-3 fs-6 fw-bold rounded-3 shadow">
                    <i class="bi bi-arrow-left-right me-2"></i>TRANSFER TO MAIN BALANCE
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Transaction History Logs -->
<div class="gh-card p-3">
    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Recent Wallet Transactions</h6>
    <div class="table-responsive">
        <table class="table table-borderless table-sm align-middle mb-0" style="font-size: 0.82rem;">
            <thead class="text-secondary border-bottom">
                <tr>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Ref ID</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                    <tr class="border-bottom border-light">
                        <td>
                            <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.65rem;">{{ str_replace('_', ' ', $t->transaction_type) }}</span>
                        </td>
                        <td class="fw-bold {{ $t->amount >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $t->amount >= 0 ? '+' : '' }}₹{{ number_format($t->amount, 2) }}
                        </td>
                        <td class="font-monospace text-secondary" style="font-size: 0.72rem;">{{ $t->reference_id ?? '-' }}</td>
                        <td class="text-secondary" style="font-size: 0.72rem;">{{ $t->created_at->format('M d, H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-secondary text-center py-3">No transactions found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    function setDeposit(val) {
        document.getElementById('depositAmountInput').value = val;
        if (window.soundManager) window.soundManager.play('click');
    }

    @if(session('success'))
    document.addEventListener('DOMContentLoaded', () => {
        if (window.soundManager) window.soundManager.play('deposit');
        if (window.animationManager) {
            window.animationManager.triggerConfetti(50);
            window.animationManager.animateCoinsToWallet();
        }
    });
    @endif
</script>
@endpush
@endsection
