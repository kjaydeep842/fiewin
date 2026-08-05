@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <!-- Header Banner -->
    <div class="gh-card p-4 mb-3 text-white" style="background: linear-gradient(135deg, #1E88E5, #8B5CF6);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white bg-opacity-20 p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-1">Privacy Policy</h4>
                <p class="small text-white-50 mb-0">Last Updated: August 2026 | Compliant with Data Protection Regulations</p>
            </div>
        </div>
    </div>

    <!-- Security Highlights Pills -->
    <div class="row g-2 mb-3">
        <div class="col-4">
            <div class="gh-card p-2 text-center">
                <i class="bi bi-file-earmark-lock text-primary fs-5 mb-1 d-block"></i>
                <span class="small fw-semibold d-block text-truncate">256-Bit SSL</span>
            </div>
        </div>
        <div class="col-4">
            <div class="gh-card p-2 text-center">
                <i class="bi bi-person-check text-success fs-5 mb-1 d-block"></i>
                <span class="small fw-semibold d-block text-truncate">Strict Privacy</span>
            </div>
        </div>
        <div class="col-4">
            <div class="gh-card p-2 text-center">
                <i class="bi bi-bank text-warning fs-5 mb-1 d-block"></i>
                <span class="small fw-semibold d-block text-truncate">PCI-DSS Safe</span>
            </div>
        </div>
    </div>

    <!-- Privacy Content -->
    <div class="gh-card p-4">
        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-1-circle-fill text-primary me-2"></i>1. Overview & Commitment</h5>
            <p class="text-secondary small leading-relaxed">
                At Rivexa ("Fiewin"), we are deeply committed to protecting your personal information and respecting your privacy. This Privacy Policy outlines how we collect, store, process, and safeguard your data when you access our real-money gaming platform, website, or mobile services.
            </p>
        </div>

        <hr class="my-3 opacity-25">

        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-2-circle-fill text-primary me-2"></i>2. Information We Collect</h5>
            <p class="text-secondary small leading-relaxed">
                To provide a secure and legally compliant real-money gaming environment, we collect the following categories of information:
            </p>
            <ul class="text-secondary small ps-3 mb-0">
                <li class="mb-2"><strong>Personal Identification:</strong> Full Name, Mobile Number, Email Address, Date of Birth, and Government ID documents for mandatory KYC verification.</li>
                <li class="mb-2"><strong>Financial & Payment Information:</strong> UPI IDs, Bank Account details, deposit/withdrawal transaction history, and wallet balance records.</li>
                <li class="mb-2"><strong>Technical Data:</strong> IP Address, Device Type, Operating System, Browser Information, and Geolocation data to enforce state availability restrictions.</li>
                <li class="mb-0"><strong>Gameplay Activity:</strong> Game history, bet logs, win/loss statistics, bonus claims, and referral activity.</li>
            </ul>
        </div>

        <hr class="my-3 opacity-25">

        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-3-circle-fill text-primary me-2"></i>3. How We Use Your Data</h5>
            <p class="text-secondary small leading-relaxed">
                Your data is processed strictly for legitimate operational, security, and legal purposes:
            </p>
            <div class="row g-2">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <i class="bi bi-wallet2 text-success me-2"></i><strong>Account & Payouts:</strong> Processing deposits, verifying withdrawal requests, and settling game winnings.
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <i class="bi bi-shield-check text-primary me-2"></i><strong>Fraud Prevention:</strong> Preventing multiple accounts, anti-cheating monitoring, and Anti-Money Laundering (AML) compliance.
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <i class="bi bi-patch-check text-warning me-2"></i><strong>Age & Legal Checks:</strong> Restricting access to users aged 18+ and enforcing geographic location boundaries.
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <i class="bi bi-receipt text-info me-2"></i><strong>Taxation (TDS):</strong> Deducting and remitting applicable taxes under Section 194BA of Indian Income Tax laws.
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-3 opacity-25">

        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-4-circle-fill text-primary me-2"></i>4. Data Sharing & Third Parties</h5>
            <p class="text-secondary small leading-relaxed mb-0">
                We <strong>never sell or rent</strong> your personal information to third parties for marketing purposes. Data is shared exclusively with trusted payment gateways, SMS/OTP service providers, identity verification agencies (KYC), and statutory tax or legal authorities when legally mandated.
            </p>
        </div>

        <hr class="my-3 opacity-25">

        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-5-circle-fill text-primary me-2"></i>5. Data Storage & Encryption</h5>
            <p class="text-secondary small leading-relaxed mb-0">
                All sensitive user communications and financial transactions are encrypted using industry-standard <strong>256-bit SSL (Secure Sockets Layer) encryption</strong>. Payment details are processed in accordance with PCI-DSS guidelines, and passwords are hash-stored using bcrypt.
            </p>
        </div>

        <hr class="my-3 opacity-25">

        <div>
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-6-circle-fill text-primary me-2"></i>6. Contact Our Data Protection Officer</h5>
            <p class="text-secondary small leading-relaxed mb-2">
                If you have questions, concerns, or requests regarding your personal data or wish to close your account, please contact our Privacy Team:
            </p>
            <div class="p-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3">
                <span class="d-block text-dark fw-semibold small"><i class="bi bi-envelope-at me-2 text-primary"></i>Email: privacy@fiewin.com</span>
                <span class="d-block text-dark fw-semibold small mt-1"><i class="bi bi-headset me-2 text-primary"></i>Support Hotline: Available 24/7 via <a href="{{ route('contact') }}" class="text-primary text-decoration-none fw-bold">Contact Page</a></span>
            </div>
        </div>
    </div>
</div>
@endsection
