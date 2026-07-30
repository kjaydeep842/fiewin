<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — GameHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }

        body {
            background: linear-gradient(135deg, #F0FDF4 0%, #F4F8FC 50%, #EEF2FF 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Top Bar ─────────────────────── */
        .auth-topbar {
            background: #fff;
            border-bottom: 1px solid #E5E7EB;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .brand-link {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-weight: 800;
            font-size: 1.2rem;
            color: #111827;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #22C55E, #1E88E5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1rem;
        }

        /* ── Auth Card ───────────────────── */
        .auth-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 16px;
            position: relative;
            z-index: 1;
        }

        .auth-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.09);
            width: 100%;
            max-width: 480px;
            padding: 36px 36px;
        }

        .auth-icon-wrap {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, #22C55E, #16a34a);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.4rem;
            margin: 0 auto 14px;
        }

        /* ── Bonus Banner ────────────────── */
        .bonus-banner {
            background: linear-gradient(135deg, rgba(34,197,94,0.10), rgba(30,136,229,0.08));
            border: 1px solid rgba(34,197,94,0.25);
            border-radius: 12px;
            padding: 10px 14px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .bonus-badge {
            background: linear-gradient(135deg, #22C55E, #16a34a);
            color: #fff;
            border-radius: 8px;
            padding: 4px 10px;
            font-size: 0.8rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* ── Form Controls ───────────────── */
        .auth-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: #6B7280;
            text-transform: uppercase;
            margin-bottom: 6px;
            display: block;
        }

        .auth-input {
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.875rem;
            font-family: 'Poppins', sans-serif;
            background: #FAFBFD;
            color: #111827;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            width: 100%;
        }

        .auth-input:focus {
            outline: none;
            border-color: #22C55E;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
        }

        .auth-input.is-invalid {
            border-color: #EF4444;
            background: #FFF5F5;
        }

        .input-icon-wrap {
            position: relative;
        }

        .input-icon-wrap .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 0.95rem;
            pointer-events: none;
        }

        .input-icon-wrap .auth-input {
            padding-left: 38px;
        }

        /* ── Buttons ─────────────────────── */
        .btn-auth-success {
            background: linear-gradient(135deg, #22C55E, #16a34a);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-size: 0.9rem;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 16px rgba(34, 197, 94, 0.35);
            transition: all 0.2s ease;
            width: 100%;
            cursor: pointer;
        }

        .btn-auth-success:hover {
            box-shadow: 0 6px 22px rgba(34, 197, 94, 0.45);
            transform: translateY(-1px);
            color: #fff;
        }

        /* ── Alerts ──────────────────────── */
        .auth-alert-error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-radius: 10px;
            color: #dc2626;
            font-size: 0.845rem;
            padding: 10px 14px;
            margin-bottom: 20px;
        }

        /* ── Error text ──────────────────── */
        .field-error {
            font-size: 0.78rem;
            color: #dc2626;
            margin-top: 4px;
        }

        /* ── Referral highlight ──────────── */
        .referral-input {
            border-color: #F59E0B !important;
        }

        .referral-input:focus {
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15) !important;
            border-color: #F59E0B !important;
        }

        /* ── Decorative dots ─────────────── */
        .bg-dot-pattern {
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, #22C55E14 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
            z-index: 0;
        }

        /* ── Password strength ───────────── */
        .password-strength-bar {
            height: 3px;
            border-radius: 3px;
            background: #E5E7EB;
            margin-top: 6px;
            overflow: hidden;
        }

        .password-strength-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s, background 0.3s;
        }
    </style>
</head>
<body>
    <div class="bg-dot-pattern"></div>

    <!-- Top Bar -->
    <div class="auth-topbar">
        <a href="{{ route('home') }}" class="brand-link">
            <div class="brand-icon"><i class="bi bi-controller"></i></div>
            <span>Game<span style="color: #22C55E;">Hub</span></span>
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold" style="font-size: 0.82rem;">Login</a>
            <a href="{{ route('register') }}" class="btn btn-sm rounded-pill px-3 fw-semibold text-white" style="background: linear-gradient(135deg,#22C55E,#16a34a); font-size: 0.82rem;">Register</a>
        </div>
    </div>

    <!-- Auth Card -->
    <div class="auth-wrap">
        <div class="auth-card">

            <!-- Header -->
            <div class="text-center mb-3">
                <div class="auth-icon-wrap">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <h4 class="fw-bold mb-1" style="color: #111827;">Create Your Account</h4>
                <p class="text-muted small mb-0">Join GameHub and start winning today</p>
            </div>

            <!-- Bonus Banner -->
            <div class="bonus-banner">
                <div class="bonus-badge">🎁 BONUS</div>
                <div>
                    <div class="fw-semibold small" style="color: #15803d;">Sign-up Reward: ₹50 Welcome Bonus</div>
                    <div style="font-size: 0.72rem; color: #6B7280;">Credited instantly after registration</div>
                </div>
            </div>

            <!-- Error Alert -->
            @if($errors->any())
                <div class="auth-alert-error">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Register Form -->
            <form method="POST" action="{{ route('register') }}" id="registerForm">
                @csrf

                <!-- Full Name -->
                <div class="mb-3">
                    <label class="auth-label">Full Name</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" name="name"
                               class="auth-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               placeholder="Rahul Sharma"
                               value="{{ old('name') }}"
                               required autofocus>
                    </div>
                    @error('name')
                        <div class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="auth-label">Email Address</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" name="email"
                               class="auth-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               placeholder="rahul@example.com"
                               value="{{ old('email') }}"
                               required>
                    </div>
                    @error('email')
                        <div class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <!-- Mobile -->
                <div class="mb-3">
                    <label class="auth-label">Mobile Number</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-phone input-icon"></i>
                        <input type="text" name="mobile"
                               class="auth-input {{ $errors->has('mobile') ? 'is-invalid' : '' }}"
                               placeholder="9876543210"
                               value="{{ old('mobile') }}"
                               maxlength="15"
                               required>
                    </div>
                    @error('mobile')
                        <div class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password & Confirm Side by Side -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="auth-label">Password</label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-lock input-icon"></i>
                            <input type="password" name="password"
                                   class="auth-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                   id="passwordField"
                                   placeholder="••••••••"
                                   required>
                        </div>
                        <div class="password-strength-bar mt-1">
                            <div class="password-strength-fill" id="strengthFill" style="width: 0%;"></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="auth-label">Confirm Password</label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-lock-fill input-icon"></i>
                            <input type="password" name="password_confirmation"
                                   class="auth-input"
                                   id="confirmPasswordField"
                                   placeholder="••••••••"
                                   required>
                        </div>
                    </div>
                    @error('password')
                        <div class="col-12">
                            <div class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        </div>
                    @enderror
                </div>

                <!-- Referral Code -->
                <div class="mb-4">
                    <label class="auth-label">
                        <i class="bi bi-ticket-perforated me-1" style="color: #F59E0B;"></i>
                        Referral Code <span class="text-muted fw-normal">(Optional)</span>
                    </label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-gift input-icon" style="color: #F59E0B;"></i>
                        <input type="text" name="referral_code"
                               class="auth-input referral-input"
                               placeholder="ENTER INVITE CODE"
                               value="{{ old('referral_code', $refCode ?? '') }}"
                               style="text-transform: uppercase; letter-spacing: 0.1em;">
                    </div>
                    <div style="font-size: 0.7rem; color: #9CA3AF; margin-top: 4px;">
                        <i class="bi bi-info-circle me-1"></i>Both you and your referrer earn bonus credits!
                    </div>
                </div>

                <button type="submit" class="btn-auth-success">
                    <i class="bi bi-stars me-2"></i>CLAIM BONUS & REGISTER
                </button>
            </form>

            <div class="text-center mt-4 pt-3" style="border-top: 1px solid #F3F4F6;">
                <span class="text-muted small">Already have an account?</span>
                <a href="{{ route('login') }}" class="ms-1 fw-bold small text-decoration-none" style="color: #1E88E5;">
                    Login Here <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <!-- Trust Badges -->
            <div class="row g-2 mt-3">
                <div class="col-4 text-center">
                    <div style="font-size: 1.2rem; color: #22C55E;"><i class="bi bi-shield-check-fill"></i></div>
                    <div style="font-size: 0.65rem; color: #9CA3AF; margin-top: 2px;">100% Secure</div>
                </div>
                <div class="col-4 text-center">
                    <div style="font-size: 1.2rem; color: #1E88E5;"><i class="bi bi-lightning-charge-fill"></i></div>
                    <div style="font-size: 0.65rem; color: #9CA3AF; margin-top: 2px;">Instant Payout</div>
                </div>
                <div class="col-4 text-center">
                    <div style="font-size: 1.2rem; color: #F59E0B;"><i class="bi bi-award-fill"></i></div>
                    <div style="font-size: 0.65rem; color: #9CA3AF; margin-top: 2px;">Daily Rewards</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength indicator
        const passwordField = document.getElementById('passwordField');
        const strengthFill = document.getElementById('strengthFill');

        passwordField.addEventListener('input', function () {
            const val = this.value;
            let strength = 0;
            if (val.length >= 6) strength++;
            if (val.length >= 10) strength++;
            if (/[A-Z]/.test(val)) strength++;
            if (/[0-9]/.test(val)) strength++;
            if (/[^A-Za-z0-9]/.test(val)) strength++;

            const widths  = ['0%', '20%', '40%', '60%', '85%', '100%'];
            const colors  = ['transparent', '#EF4444', '#F59E0B', '#F59E0B', '#22C55E', '#16a34a'];

            strengthFill.style.width = widths[strength];
            strengthFill.style.background = colors[strength];
        });

        // Uppercase referral code
        document.querySelector('[name="referral_code"]')?.addEventListener('input', function () {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>
