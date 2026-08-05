@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <!-- Header Banner -->
    <div class="gh-card p-4 mb-3 text-white" style="background: linear-gradient(135deg, #059669, #10B981);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white bg-opacity-20 p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                <i class="bi bi-heart-pulse-fill"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-1">Responsible Gaming</h4>
                <p class="small text-white-50 mb-0">Play Safely, Play Smart & Stay in Control</p>
            </div>
        </div>
    </div>

    <!-- Banner Commitment -->
    <div class="gh-card p-4 mb-3 border-start border-4 border-success">
        <h5 class="fw-bold text-dark mb-2"><i class="bi bi-shield-check text-success me-2"></i>Our Commitment to Responsible Gaming</h5>
        <p class="text-secondary small leading-relaxed mb-0">
            At Rivexa ("Fiewin"), we believe real-money gaming should always remain an enjoyable form of entertainment. We are dedicated to maintaining a safe, fair, and transparent environment by encouraging healthy gaming habits, providing self-control tools, and preventing underage gaming.
        </p>
    </div>

    <!-- Golden Rules of Responsible Gaming -->
    <div class="gh-card p-4 mb-3">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i>Golden Rules for Safe Gaming</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 border h-100">
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-cash-stack text-success me-2"></i>Set Budget Limits</div>
                    <div class="text-secondary small">Only play with money you can afford to lose. Never use funds reserved for daily living expenses, rent, or essentials.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 border h-100">
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-clock-history text-primary me-2"></i>Track Your Time</div>
                    <div class="text-secondary small">Balance gaming with other hobbies, work, and family. Take regular breaks and avoid playing when stressed or tired.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 border h-100">
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-arrow-counterclockwise text-danger me-2"></i>Don't Chase Losses</div>
                    <div class="text-secondary small">Accept that losing is part of the game. Trying to quickly recover lost funds often leads to greater financial strain.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 border h-100">
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-controller text-warning me-2"></i>Gaming is Not Income</div>
                    <div class="text-secondary small">View real-money games as fun and leisure, never as a guaranteed income strategy or quick money scheme.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Self Assessment Test -->
    <div class="gh-card p-4 mb-3">
        <h5 class="fw-bold text-dark mb-2"><i class="bi bi-clipboard-check-fill text-primary me-2"></i>Self-Assessment Checklist</h5>
        <p class="text-secondary small mb-3">Ask yourself these questions to evaluate whether your gaming habits remain healthy:</p>

        <div class="list-group list-group-flush rounded-3 border">
            <div class="list-group-item p-3 small text-dark d-flex gap-2 align-items-start">
                <span class="badge bg-primary rounded-circle">1</span>
                Do you spend more money or time gaming than you originally planned?
            </div>
            <div class="list-group-item p-3 small text-dark d-flex gap-2 align-items-start">
                <span class="badge bg-primary rounded-circle">2</span>
                Do you feel anxious, irritable, or agitated when trying to stop or reduce gaming?
            </div>
            <div class="list-group-item p-3 small text-dark d-flex gap-2 align-items-start">
                <span class="badge bg-primary rounded-circle">3</span>
                Have you ever borrowed money or sold personal possessions to fund your gaming?
            </div>
            <div class="list-group-item p-3 small text-dark d-flex gap-2 align-items-start">
                <span class="badge bg-primary rounded-circle">4</span>
                Do you game to escape real-life problems, loneliness, or personal worries?
            </div>
        </div>
        <p class="small text-muted mt-2 mb-0">If you answered "Yes" to two or more questions, we strongly advise using our Self-Exclusion options or seeking guidance from support helplines.</p>
    </div>

    <!-- Self Exclusion & Minor Protection -->
    <div class="gh-card p-4 mb-3">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3 h-100">
                    <h6 class="fw-bold text-danger mb-2"><i class="bi bi-lock-fill me-2"></i>Self-Exclusion Options</h6>
                    <p class="text-secondary small mb-2">If you need a break, you can request a temporary cool-off (7 to 30 days) or permanent self-exclusion of your account.</p>
                    <a href="{{ route('contact') }}" class="btn btn-sm btn-danger rounded-pill fw-bold">Request Account Lock</a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 h-100">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-shield-slash-fill me-2 text-warning"></i>Strict 18+ Minor Protection</h6>
                    <p class="text-secondary small mb-0">Underage gaming (under 18) is strictly prohibited. We mandate identity checks (KYC) and encourage parents to use parental control filtering software (e.g. NetNanny, CyberPatrol) to block minors.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Helpline & Counseling Resources -->
    <div class="gh-card p-4">
        <h5 class="fw-bold text-dark mb-2"><i class="bi bi-headset text-success me-2"></i>Help & Support Resources</h5>
        <p class="text-secondary small mb-3">If you or someone you know is struggling with problem gaming, confidential assistance is available:</p>
        <div class="p-3 bg-light rounded-3 border">
            <span class="d-block text-dark fw-bold small mb-1"><i class="bi bi-telephone-fill text-success me-2"></i>National Gaming Support & Counseling:</span>
            <span class="d-block text-secondary small mb-2">Reach out to trained counselors for free, non-judgmental guidance and support.</span>
            <div class="d-flex flex-wrap gap-2">
                <a href="mailto:support@fiewin.com" class="btn btn-sm btn-outline-success rounded-pill fw-semibold"><i class="bi bi-envelope me-1"></i>support@fiewin.com</a>
                <a href="{{ route('contact') }}" class="btn btn-sm btn-success rounded-pill fw-semibold"><i class="bi bi-chat-dots me-1"></i>24/7 Support Desk</a>
            </div>
        </div>
    </div>
</div>
@endsection
