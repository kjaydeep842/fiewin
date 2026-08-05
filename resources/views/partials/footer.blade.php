<footer class="gh-footer mt-4 pt-4 pb-4 border-top text-center" style="background: #ffffff; border-color: var(--gh-border) !important;">
    <div class="container px-3">

        <!-- Responsible Gaming Alert Strip -->
        <div class="p-3 bg-light rounded-4 border mb-3 text-start">
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-danger rounded-circle p-2 fs-6 flex-shrink-0 fw-bold" style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;">18+</span>
                <div>
                    <h6 class="fw-bold text-dark mb-1 small"><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>Play Responsibly & Legal Disclaimer</h6>
                    <p class="text-secondary mb-0" style="font-size: 0.73rem; line-height: 1.4;">
                        Real-money gaming involves financial risk and may be addictive. Please play responsibly and within your financial means. Access is strictly prohibited to individuals under 18 years of age and residents of restricted jurisdictions (Andhra Pradesh, Telangana, Assam, Odisha, Nagaland, Sikkim, Tamil Nadu).
                    </p>
                </div>
            </div>
        </div>

        <!-- Trust & Security Badges -->
        <div class="row g-2 mb-3 justify-content-center">
            <div class="col-auto">
                <a href="{{ route('security') }}" class="text-decoration-none">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 p-2 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                        <i class="bi bi-shield-lock-fill"></i> 256-Bit SSL HTTPS
                    </span>
                </a>
            </div>
            <div class="col-auto">
                <a href="{{ route('responsible-gaming') }}" class="text-decoration-none">
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 p-2 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                        <i class="bi bi-person-x-fill"></i> 18+ Age Limit
                    </span>
                </a>
            </div>
            <div class="col-auto">
                <a href="{{ route('legal-availability') }}" class="text-decoration-none">
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 p-2 rounded-pill d-inline-flex align-items-center gap-1 text-dark" style="font-size: 0.72rem;">
                        <i class="bi bi-geo-alt-fill"></i> Skill Gaming Legal
                    </span>
                </a>
            </div>
            <div class="col-auto">
                <a href="{{ route('security') }}" class="text-decoration-none">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 p-2 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                        <i class="bi bi-cpu-fill"></i> Provably Fair RNG
                    </span>
                </a>
            </div>
        </div>

        <!-- Compliance & Legal Navigation Links -->
        <div class="d-flex flex-wrap justify-content-center gap-3 mb-3" style="font-size: 0.78rem;">
            <a href="{{ route('privacy') }}" class="text-secondary text-decoration-none fw-medium hover-primary">Privacy Policy</a>
            <span class="text-muted">•</span>
            <a href="{{ route('terms') }}" class="text-secondary text-decoration-none fw-medium hover-primary">Terms & Conditions</a>
            <span class="text-muted">•</span>
            <a href="{{ route('responsible-gaming') }}" class="text-secondary text-decoration-none fw-medium hover-primary">Responsible Gaming</a>
            <span class="text-muted">•</span>
            <a href="{{ route('legal-availability') }}" class="text-secondary text-decoration-none fw-medium hover-primary">Legal Availability</a>
            <span class="text-muted">•</span>
            <a href="{{ route('security') }}" class="text-secondary text-decoration-none fw-medium hover-primary">HTTPS & Security</a>
            <span class="text-muted">•</span>
            <a href="{{ route('contact') }}" class="text-secondary text-decoration-none fw-medium hover-primary">Contact Us</a>
        </div>

        <!-- Copyright & Platform Info -->
        <div class="text-secondary" style="font-size: 0.72rem;">
            <p class="mb-1">© {{ date('Y') }} <strong>Rivexa (Fiewin)</strong>. All Rights Reserved.</p>
            <p class="mb-0 text-muted">Licensed Skill-Based Gaming Platform. TDS deducted under Sec 194BA.</p>
        </div>
    </div>
</footer>
