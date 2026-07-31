@extends('layouts.app')

@section('content')
<!-- Wallet Summary Card -->
<div class="gh-card p-4 mb-3" style="background: linear-gradient(135deg, #1E88E5 0%, #1565C0 100%); color: #ffffff;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="small opacity-75 d-block">TOTAL WALLET BALANCE</span>
            <h2 class="fw-bold mb-0 font-monospace">₹{{ number_format($wallet->total_balance, 2) }}</h2>
        </div>
        <span class="badge bg-light text-primary fw-bold rounded-pill px-3 py-1"><i class="bi bi-shield-check me-1"></i>SECURE</span>
    </div>

    <div class="row g-2 text-center pt-2 border-top border-white border-opacity-25" style="font-size: 0.8rem;">
        <div class="col-4">
            <span class="opacity-75 d-block" style="font-size: 0.65rem;">MAIN BALANCE</span>
            <span class="fw-bold">₹{{ number_format($wallet->main_balance, 2) }}</span>
        </div>
        <div class="col-4">
            <span class="opacity-75 d-block" style="font-size: 0.65rem;">BONUS WALLET</span>
            <span class="fw-bold">₹{{ number_format($wallet->bonus_balance, 2) }}</span>
        </div>
        <div class="col-4">
            <span class="opacity-75 d-block" style="font-size: 0.65rem;">COMMISSION</span>
            <span class="fw-bold">₹{{ number_format($wallet->commission_balance, 2) }}</span>
        </div>
    </div>
</div>

<!-- Wallet Actions (Deposit, Withdraw, Transfer) -->
<div class="gh-card p-3 mb-3">
    <ul class="nav nav-pills nav-fill mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold py-2 rounded-pill" data-bs-toggle="pill" data-bs-target="#pillDeposit"><i class="bi bi-plus-circle me-1"></i>Deposit</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold py-2 rounded-pill text-dark" data-bs-toggle="pill" data-bs-target="#pillWithdraw"><i class="bi bi-dash-circle me-1"></i>Withdraw</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold py-2 rounded-pill text-dark" data-bs-toggle="pill" data-bs-target="#pillTransfer"><i class="bi bi-arrow-left-right me-1"></i>Transfer</button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Deposit -->
        <div class="tab-pane fade show active" id="pillDeposit">
            <form action="{{ route('wallet.deposit') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold">SELECT PAYMENT METHOD</label>
                    <div class="row g-2">
                        @foreach($paymentMethods as $m)
                            <div class="col-6">
                                <label class="w-100 p-2 rounded-3 border text-center cursor-pointer bg-light">
                                    <input type="radio" name="payment_method" value="{{ $m->code }}" class="me-1" required {{ $loop->first ? 'checked' : '' }}>
                                    <span class="fw-bold text-dark small">{{ $m->name }}</span>
                                    @if($m->bonus_percentage > 0)
                                        <small class="d-block text-success fw-semibold" style="font-size: 0.65rem;">+{{ $m->bonus_percentage }}% Extra</small>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold">DEPOSIT AMOUNT (₹)</label>
                    <div class="d-flex gap-1 gap-sm-2 mb-2">
                        <button type="button" class="btn btn-outline-primary btn-sm flex-fill rounded-pill px-1" style="font-size: 0.78rem;" onclick="setDeposit(200)">₹200</button>
                        <button type="button" class="btn btn-outline-primary btn-sm flex-fill rounded-pill px-1" style="font-size: 0.78rem;" onclick="setDeposit(500)">₹500</button>
                        <button type="button" class="btn btn-outline-primary btn-sm flex-fill rounded-pill px-1" style="font-size: 0.78rem;" onclick="setDeposit(1000)">₹1000</button>
                        <button type="button" class="btn btn-outline-primary btn-sm flex-fill rounded-pill px-1" style="font-size: 0.78rem;" onclick="setDeposit(5000)">₹5000</button>
                    </div>
                    <input type="number" name="amount" id="depositAmountInput" class="form-control form-control-lg fw-bold" value="500" min="100" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold">UTR / REFERENCE TRANSACTION NUMBER</label>
                    <input type="text" name="utr_number" class="form-control" placeholder="e.g. 329871239812">
                </div>

                <button type="submit" class="btn gh-btn-success w-100 py-2 fs-6 rounded-pill">PROCEED TO RECHARGE</button>
            </form>
        </div>

        <!-- Withdraw -->
        <div class="tab-pane fade" id="pillWithdraw">
            <form action="{{ route('wallet.withdraw') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold">WITHDRAWAL AMOUNT (₹)</label>
                    <input type="number" name="amount" class="form-control form-control-lg fw-bold" placeholder="Min ₹100" min="100" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold">SAVED BANK ACCOUNT</label>
                    <select name="bank_account_id" class="form-select">
                        @forelse($bankAccounts as $b)
                            <option value="{{ $b->id }}">{{ $b->bank_name }} - {{ $b->account_number }} ({{ $b->account_holder }})</option>
                        @empty
                            <option value="">No bank account added yet (Add in Profile)</option>
                        @endforelse
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold">OR ENTER UPI ID</label>
                    <input type="text" name="upi_id" class="form-control" placeholder="user@upi">
                </div>

                <button type="submit" class="btn gh-btn-primary w-100 py-2 fs-6 rounded-pill">SUBMIT WITHDRAWAL</button>
            </form>
        </div>

        <!-- Transfer -->
        <div class="tab-pane fade" id="pillTransfer">
            <form action="{{ route('wallet.transfer') }}" method="POST">
                @csrf
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <small class="text-secondary d-block mb-1">AVAILABLE COMMISSION</small>
                    <h4 class="fw-bold text-primary mb-0">₹{{ number_format($wallet->commission_balance, 2) }}</h4>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold">TRANSFER AMOUNT TO MAIN WALLET (₹)</label>
                    <input type="number" name="amount" class="form-control form-control-lg fw-bold" value="{{ $wallet->commission_balance }}" min="10" max="{{ $wallet->commission_balance }}" required>
                </div>
                <button type="submit" class="btn gh-btn-success w-100 py-2 fs-6 rounded-pill">TRANSFER TO MAIN BALANCE</button>
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
