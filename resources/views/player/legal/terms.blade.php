@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <!-- Header Banner -->
    <div class="gh-card p-4 mb-3 text-white" style="background: linear-gradient(135deg, #2563EB, #1D4ED8);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white bg-opacity-20 p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                <i class="bi bi-file-text-fill"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-1">Terms & Conditions</h4>
                <p class="small text-white-50 mb-0">Platform User Agreement & Real-Money Gaming Rules</p>
            </div>
        </div>
    </div>

    <!-- Quick Highlights -->
    <div class="alert alert-warning border-warning bg-warning bg-opacity-10 text-dark p-3 rounded-4 mb-3 d-flex align-items-center gap-3">
        <i class="bi bi-exclamation-triangle-fill text-warning fs-3 flex-shrink-0"></i>
        <div class="small">
            <strong>18+ Age Restriction & Real Money Disclaimer:</strong> By registering and playing on GameHub, you confirm that you are at least 18 years old and agree that real-money gaming involves financial risk. Play responsibly.
        </div>
    </div>

    <!-- Terms Sections -->
    <div class="gh-card p-4">
        <!-- 1. Account Eligibility & 18+ Rule -->
        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-person-fill-check text-primary me-2"></i>1. Eligibility & Age Restriction (18+)</h5>
            <p class="text-secondary small leading-relaxed">
                Participation in real-money games on GameHub ("Platform") is strictly restricted to individuals who meet all of the following conditions:
            </p>
            <ul class="text-secondary small ps-3 mb-0">
                <li class="mb-2">Must be <strong>18 years of age or older</strong> at the time of registration.</li>
                <li class="mb-2">Must reside in a state or territory where real-money skill gaming is legally permitted (see <a href="{{ route('legal-availability') }}" class="text-primary fw-bold text-decoration-none">Legal Availability</a>).</li>
                <li class="mb-0">Must create only one account per person, mobile number, IP address, and bank account.</li>
            </ul>
        </div>

        <hr class="my-3 opacity-25">

        <!-- 2. Wallet Deposits & Withdrawals -->
        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-wallet2 text-primary me-2"></i>2. Wallet, Deposits & Withdrawals</h5>
            <p class="text-secondary small leading-relaxed mb-2">
                All financial transactions within the GameHub platform are governed by the following strict rules:
            </p>
            <div class="p-3 bg-light rounded-3 border mb-2">
                <span class="d-block fw-semibold text-dark small"><i class="bi bi-arrow-down-left-circle text-success me-2"></i>Deposits</span>
                <span class="text-secondary small d-block">Deposits made via UPI, Bank Transfer, or authorized channels are credited directly to your Main Balance. Deposits must be used for gameplay and cannot be directly withdrawn without minimum wagering.</span>
            </div>
            <div class="p-3 bg-light rounded-3 border">
                <span class="d-block fw-semibold text-dark small"><i class="bi bi-arrow-up-right-circle text-primary me-2"></i>Withdrawals & KYC</span>
                <span class="text-secondary small d-block">Withdrawals require mandatory KYC approval (PAN Card & Bank details). Withdrawals are processed to verified bank accounts or UPI IDs belonging solely to the account holder. Applicable TDS will be deducted under Section 194BA.</span>
            </div>
        </div>

        <hr class="my-3 opacity-25">

        <!-- 3. Fair Play & Anti-Cheating -->
        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-shield-x text-primary me-2"></i>3. Fair Play Policy & Prohibited Conduct</h5>
            <p class="text-secondary small leading-relaxed mb-2">
                GameHub maintains zero tolerance for unfair play or fraudulent activities. The following activities are strictly prohibited and will result in immediate account ban and forfeiture of funds:
            </p>
            <div class="row g-2">
                <div class="col-md-6">
                    <div class="p-2 border rounded-3 bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger small">
                        <i class="bi bi-x-circle-fill me-1"></i> Automated Bots or Cheat Scripts
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-2 border rounded-3 bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger small">
                        <i class="bi bi-x-circle-fill me-1"></i> Multiple Accounts per User
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-2 border rounded-3 bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger small">
                        <i class="bi bi-x-circle-fill me-1"></i> Collusion or Chips Dump
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-2 border rounded-3 bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger small">
                        <i class="bi bi-x-circle-fill me-1"></i> Exploiting Bugs or Glitches
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-3 opacity-25">

        <!-- 4. Responsible Gaming & Financial Risk -->
        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-heart-pulse-fill text-primary me-2"></i>4. Responsible Gaming & Risk Acknowledgement</h5>
            <p class="text-secondary small leading-relaxed mb-0">
                Real-money gaming should be treated as entertainment, not a source of income. Players are responsible for managing their gaming habits and setting personal budgets. Please refer to our dedicated <a href="{{ route('responsible-gaming') }}" class="text-primary fw-bold text-decoration-none">Responsible Gaming Policy</a> for self-exclusion and support options.
            </p>
        </div>

        <hr class="my-3 opacity-25">

        <!-- 5. Limitation of Liability -->
        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-exclamation-octagon text-primary me-2"></i>5. Limitation of Liability</h5>
            <p class="text-secondary small leading-relaxed mb-0">
                GameHub shall not be liable for losses caused by internet connectivity issues, device malfunctions, third-party payment delays, or user negligence. Platform outcomes generated by Provably Fair RNG engines are final and binding.
            </p>
        </div>

        <hr class="my-3 opacity-25">

        <!-- 6. Amendments & Contact -->
        <div>
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-pencil-square text-primary me-2"></i>6. Policy Amendments</h5>
            <p class="text-secondary small leading-relaxed mb-0">
                GameHub reserves the right to modify these Terms & Conditions at any time. Continued use of the platform constitutes acceptance of updated terms. For legal inquiries, contact <a href="mailto:legal@fiewin.com" class="text-primary text-decoration-none fw-semibold">legal@fiewin.com</a>.
            </p>
        </div>
    </div>
</div>
@endsection
