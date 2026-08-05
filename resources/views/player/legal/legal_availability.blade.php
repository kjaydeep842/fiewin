@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <!-- Header Banner -->
    <div class="gh-card p-4 mb-3 text-white" style="background: linear-gradient(135deg, #D97706, #F59E0B);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white bg-opacity-20 p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-1">Legal Availability & Jurisdictions</h4>
                <p class="small text-white-50 mb-0">State Licensing, Skill Gaming Legality & Regional Restrictions</p>
            </div>
        </div>
    </div>

    <!-- Legal Status Card -->
    <div class="gh-card p-4 mb-3 border-start border-4 border-warning">
        <h5 class="fw-bold text-dark mb-2"><i class="bi bi-journal-check text-warning me-2"></i>Legal Framework: Game of Skill</h5>
        <p class="text-secondary small leading-relaxed mb-0">
            Real-money games hosted on GameHub are classified as <strong>Games of Skill</strong>. Under Indian jurisprudence—supported by landmark judgments of the Supreme Court of India—games where success depends predominantly upon superior knowledge, training, attention, experience, and adroitness of the player are constitutionally protected under Article 19(1)(g) of the Constitution of India.
        </p>
    </div>

    <!-- Allowed vs Restricted States -->
    <div class="row g-3 mb-3">
        <!-- Permitted Jurisdictions -->
        <div class="col-md-6">
            <div class="gh-card p-4 h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold text-success mb-0"><i class="bi bi-check-circle-fill me-2"></i>Permitted States & Territories</h6>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">ALLOWED</span>
                </div>
                <p class="text-secondary small mb-3">Players residing in the following regions are fully eligible to register, deposit, play, and withdraw real money:</p>
                <div class="row g-2 text-dark small font-monospace">
                    <div class="col-6"><i class="bi bi-dot text-success"></i> Maharashtra</div>
                    <div class="col-6"><i class="bi bi-dot text-success"></i> Delhi NCR</div>
                    <div class="col-6"><i class="bi bi-dot text-success"></i> Karnataka</div>
                    <div class="col-6"><i class="bi bi-dot text-success"></i> Uttar Pradesh</div>
                    <div class="col-6"><i class="bi bi-dot text-success"></i> Rajasthan</div>
                    <div class="col-6"><i class="bi bi-dot text-success"></i> West Bengal</div>
                    <div class="col-6"><i class="bi bi-dot text-success"></i> Gujarat</div>
                    <div class="col-6"><i class="bi bi-dot text-success"></i> Haryana</div>
                    <div class="col-6"><i class="bi bi-dot text-success"></i> Punjab</div>
                    <div class="col-6"><i class="bi bi-dot text-success"></i> Madhya Pradesh</div>
                    <div class="col-6"><i class="bi bi-dot text-success"></i> Bihar</div>
                    <div class="col-6"><i class="bi bi-dot text-success"></i> Goa</div>
                </div>
            </div>
        </div>

        <!-- Restricted Jurisdictions -->
        <div class="col-md-6">
            <div class="gh-card p-4 h-100 border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold text-danger mb-0"><i class="bi bi-x-circle-fill me-2"></i>Restricted Jurisdictions</h6>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">PROHIBITED</span>
                </div>
                <p class="text-secondary small mb-3">Due to specific local state enactments restricting online real-money games, users from the following states are <strong>prohibited</strong> from playing real-money contests:</p>
                <div class="row g-2 text-dark small font-monospace">
                    <div class="col-6 text-danger"><i class="bi bi-slash-circle me-1"></i> Andhra Pradesh</div>
                    <div class="col-6 text-danger"><i class="bi bi-slash-circle me-1"></i> Telangana</div>
                    <div class="col-6 text-danger"><i class="bi bi-slash-circle me-1"></i> Assam</div>
                    <div class="col-6 text-danger"><i class="bi bi-slash-circle me-1"></i> Odisha</div>
                    <div class="col-6 text-danger"><i class="bi bi-slash-circle me-1"></i> Nagaland</div>
                    <div class="col-6 text-danger"><i class="bi bi-slash-circle me-1"></i> Sikkim</div>
                    <div class="col-6 text-danger"><i class="bi bi-slash-circle me-1"></i> Tamil Nadu</div>
                </div>
                <div class="p-2 bg-danger bg-opacity-10 rounded-3 text-danger small mt-3">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Geolocation checks are active. Account creation from restricted states is strictly prohibited.
                </div>
            </div>
        </div>
    </div>

    <!-- Age Requirement (18+) & Tax Compliance -->
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="gh-card p-4 h-100">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Age Limit (18+ Only)</h6>
                <p class="text-secondary small leading-relaxed mb-0">
                    Real-money gaming is strictly barred for minors. You must be at least <strong>18 years old</strong> to access GameHub services. Age verification is conducted during identity submission (KYC). Minors attempting to access real-money features will have their accounts immediately blocked and funds refunded according to regulatory policies.
                </p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="gh-card p-4 h-100">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-receipt-cutoff text-primary me-2"></i>TDS Tax Compliance (Section 194BA)</h6>
                <p class="text-secondary small leading-relaxed mb-0">
                    GameHub strictly adheres to the provisions of Section 194BA of the Indian Income Tax Act. A flat <strong>30% TDS tax</strong> is deducted on net winnings at the time of withdrawal. TDS certificates (Form 16A) are issued quarterly to compliant players.
                </p>
            </div>
        </div>
    </div>

    <!-- International Users Notice -->
    <div class="gh-card p-4">
        <h5 class="fw-bold text-dark mb-2"><i class="bi bi-globe text-primary me-2"></i>International Availability & Compliance</h5>
        <p class="text-secondary small leading-relaxed mb-0">
            GameHub operates in full compliance with local regulatory guidelines. International players are responsible for ensuring that participating in online real-money gaming does not violate local laws or regulations within their resident jurisdiction.
        </p>
    </div>
</div>
@endsection
