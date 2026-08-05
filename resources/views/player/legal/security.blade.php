@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <!-- Header Banner -->
    <div class="gh-card p-4 mb-3 text-white" style="background: linear-gradient(135deg, #0284C7, #0369A1);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white bg-opacity-20 p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-1">Platform Security & HTTPS</h4>
                <p class="small text-white-50 mb-0">256-Bit SSL Encryption, PCI-DSS Compliance & Provably Fair RNG</p>
            </div>
        </div>
    </div>

    <!-- Active HTTPS Connection Status Card -->
    <div class="gh-card p-4 mb-3 bg-success bg-opacity-10 border border-success border-opacity-25">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-lock-fill text-success fs-1"></i>
                <div>
                    <h6 class="fw-bold text-dark mb-1">Secure HTTPS Connection Active</h6>
                    <p class="text-secondary small mb-0">Your session is protected with TLS 1.3 High-Grade Encryption.</p>
                </div>
            </div>
            <span class="badge bg-success rounded-pill px-3 py-2 fw-semibold">SECURE HTTPS</span>
        </div>
    </div>

    <!-- Security Pillars -->
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="gh-card p-4 h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-file-earmark-lock-fill text-primary fs-4"></i>
                    <h6 class="fw-bold text-dark mb-0">256-Bit SSL Data Encryption</h6>
                </div>
                <p class="text-secondary small leading-relaxed mb-0">
                    All communication between your device and GameHub servers is encrypted using 256-bit Secure Sockets Layer (SSL) technology, ensuring that your personal information, passwords, and transaction details remain private and unreadable to third parties.
                </p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="gh-card p-4 h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-bank2 text-success fs-4"></i>
                    <h6 class="fw-bold text-dark mb-0">PCI-DSS Payment Gateways</h6>
                </div>
                <p class="text-secondary small leading-relaxed mb-0">
                    Our payment processing partners adhere strictly to PCI-DSS (Payment Card Industry Data Security Standard) Level 1 guidelines. Deposits and withdrawals via UPI, Net Banking, and Wallet transfers execute through bank-grade encrypted channels.
                </p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="gh-card p-4 h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-key-fill text-warning fs-4"></i>
                    <h6 class="fw-bold text-dark mb-0">Account & Password Safeguards</h6>
                </div>
                <p class="text-secondary small leading-relaxed mb-0">
                    User passwords are store-hashed using high-entropy bcrypt encryption algorithms. Mobile verification (OTP) and automated session timeouts prevent unauthorized account access.
                </p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="gh-card p-4 h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-cpu-fill text-purple fs-4" style="color: #8B5CF6;"></i>
                    <h6 class="fw-bold text-dark mb-0">Provably Fair & Certified RNG</h6>
                </div>
                <p class="text-secondary small leading-relaxed mb-0">
                    Game results (Mines, Jet, Crash, Spin, Andar Bahar) are governed by audited Random Number Generators (RNG) and Provably Fair cryptographic hash algorithms, guaranteeing unbiased and tamper-proof gameplay.
                </p>
            </div>
        </div>
    </div>

    <!-- Security Tips for Players -->
    <div class="gh-card p-4">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-shield-check text-primary me-2"></i>Security Tips for Players</h5>
        <div class="list-group list-group-flush rounded-3 border">
            <div class="list-group-item p-3 small text-dark d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success"></i>
                Always check for the <strong>padlock icon (https://)</strong> in your browser address bar before entering login credentials.
            </div>
            <div class="list-group-item p-3 small text-dark d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success"></i>
                Never share your GameHub account password, OTP, or PIN with anyone, including support staff.
            </div>
            <div class="list-group-item p-3 small text-dark d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success"></i>
                Log out of your account when using shared or public mobile devices.
            </div>
        </div>
    </div>
</div>
@endsection
