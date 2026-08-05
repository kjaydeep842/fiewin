@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <!-- Header Banner -->
    <div class="gh-card p-4 mb-3 text-white" style="background: linear-gradient(135deg, #10B981, #047857);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white bg-opacity-20 p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                <i class="bi bi-headset"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-1">Contact & Customer Support</h4>
                <p class="small text-white-50 mb-0">24/7 Dedicated Assistance for Payments, Games & Account Support</p>
            </div>
        </div>
    </div>

    <!-- Official Channels Grid -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="gh-card p-3 text-center h-100">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                    <i class="bi bi-envelope-fill fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Email Support</h6>
                <p class="text-secondary small mb-2">Get detailed assistance within 24 hours.</p>
                <a href="mailto:support@fiewin.com" class="fw-bold text-primary text-decoration-none small">support@fiewin.com</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="gh-card p-3 text-center h-100">
                <div class="rounded-circle bg-success bg-opacity-10 text-success mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                    <i class="bi bi-whatsapp fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Instant Helpline</h6>
                <p class="text-secondary small mb-2">Connect with our support executives online.</p>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Available 24/7 Live</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="gh-card p-3 text-center h-100">
                <div class="rounded-circle bg-purple bg-opacity-10 text-purple mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:48px; height:48px; background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Fast Resolution</h6>
                <p class="text-secondary small mb-2">Average ticket resolution time</p>
                <span class="fw-bold text-dark small">< 2 Hours</span>
            </div>
        </div>
    </div>

    <!-- Contact Form & Info Form -->
    <div class="row g-3 mb-3">
        <div class="col-md-7">
            <div class="gh-card p-4">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-chat-left-text-fill text-primary me-2"></i>Send Us a Message</h5>
                
                @if(session('success'))
                    <div class="alert alert-success bg-success bg-opacity-10 border-success text-success p-3 rounded-4 mb-3">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('contact.submit') }}" method="POST">
                    @csrf
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">YOUR FULL NAME</label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="John Doe" value="{{ auth()->check() ? auth()->user()->name : '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">EMAIL ADDRESS</label>
                            <input type="email" name="email" class="form-control form-control-sm" placeholder="user@example.com" value="{{ auth()->check() ? auth()->user()->email : '' }}" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">MOBILE NUMBER</label>
                            <input type="text" name="mobile" class="form-control form-control-sm" placeholder="+91 9876543210" value="{{ auth()->check() ? auth()->user()->mobile : '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">INQUIRY CATEGORY</label>
                            <select name="subject" class="form-select form-select-sm" required>
                                <option value="" disabled selected>Select Category</option>
                                <option value="Deposit / UPI Issue">Deposit / Payment Inquiry</option>
                                <option value="Withdrawal Request Status">Withdrawal Inquiry</option>
                                <option value="KYC & Verification">KYC & Account Verification</option>
                                <option value="Game & Bet Issue">Gameplay / Technical Support</option>
                                <option value="General & Feedback">General Inquiry / Feedback</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-semibold">MESSAGE / ISSUE DETAILS</label>
                        <textarea name="message" rows="4" class="form-control form-control-sm" placeholder="Please describe your query in detail..." required></textarea>
                    </div>
                    <button type="submit" class="btn gh-btn-primary w-100 rounded-pill fw-bold">SUBMIT SUPPORT TICKET</button>
                </form>
            </div>
        </div>

        <!-- Support Information & Corporate Details -->
        <div class="col-md-5">
            <div class="gh-card p-4 h-100">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-building text-primary me-2"></i>Corporate Information</h5>
                <p class="text-secondary small leading-relaxed mb-3">
                    Rivexa operates as a licensed skill gaming platform providing fair, audited, and secure online gaming options.
                </p>

                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex align-items-start gap-2 mb-2">
                        <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                        <div>
                            <span class="d-block fw-bold text-dark small">Registered Entity</span>
                            <span class="text-secondary small">Rivexa Interactive Media Technologies Ltd.<br>Tower B, Cyber City, Phase III, Gurugram, HR - 122002</span>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 border">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                        <span class="fw-bold text-dark small">Encrypted Support Desk</span>
                    </div>
                    <p class="text-secondary small mb-0">All tickets submitted are protected with SSL 256-bit encryption. For immediate financial queries, keep your Transaction Ref ID handy.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Accordion -->
    <div class="gh-card p-4">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-question-circle-fill text-primary me-2"></i>Frequently Asked Questions</h5>
        <div class="accordion accordion-flush" id="faqAccordion">
            <div class="accordion-item border rounded-3 mb-2 overflow-hidden">
                <h2 class="accordion-header" id="faqHead1">
                    <button class="accordion-button collapsed fw-semibold text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1">
                        How fast are deposit amounts credited to my wallet?
                    </button>
                </h2>
                <div id="faqCollapse1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary small">
                        UPI and Bank deposits are credited automatically within 1 to 5 minutes upon successful transaction approval. If your balance hasn't updated after 15 minutes, please submit a ticket with your UTR/UPI Ref No.
                    </div>
                </div>
            </div>

            <div class="accordion-item border rounded-3 mb-2 overflow-hidden">
                <h2 class="accordion-header" id="faqHead2">
                    <button class="accordion-button collapsed fw-semibold text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2">
                        What documents are needed for KYC verification?
                    </button>
                </h2>
                <div id="faqCollapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary small">
                        For withdrawal approval, you must submit a valid Government ID (PAN Card, Aadhaar Card, or Passport) along with your verified Bank Account details matching your registered name.
                    </div>
                </div>
            </div>

            <div class="accordion-item border rounded-3 overflow-hidden">
                <h2 class="accordion-header" id="faqHead3">
                    <button class="accordion-button collapsed fw-semibold text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3">
                        How does TDS tax deduction work on game winnings?
                    </button>
                </h2>
                <div id="faqCollapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary small">
                        In compliance with Section 194BA of the Income Tax Act, 30% TDS is automatically deducted on net winnings at the time of withdrawal. TDS certificates are issued per tax guidelines.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
