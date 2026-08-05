<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — GameHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }

        body {
            background: linear-gradient(135deg, #EFF6FF 0%, #F4F8FC 50%, #EEF2FF 100%);
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
            background: linear-gradient(135deg, #1E88E5, #8B5CF6);
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
            padding: 32px 16px;
            position: relative;
            z-index: 1;
        }

        .auth-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.09);
            width: 100%;
            max-width: 440px;
            padding: 40px 36px;
        }

        .auth-icon-wrap {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, #1E88E5, #8B5CF6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.4rem;
            margin: 0 auto 16px;
        }

        /* ── Form Controls ───────────────── */
        .auth-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: #6B7280;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .auth-input {
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.9rem;
            font-family: 'Poppins', sans-serif;
            background: #FAFBFD;
            color: #111827;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            width: 100%;
        }

        .auth-input:focus {
            outline: none;
            border-color: #1E88E5;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.12);
        }

        .auth-input.is-invalid {
            border-color: #EF4444;
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
            font-size: 1rem;
            pointer-events: none;
        }

        .input-icon-wrap .auth-input {
            padding-left: 38px;
        }

        /* ── Buttons ─────────────────────── */
        .btn-auth-primary {
            background: linear-gradient(135deg, #1E88E5, #42A5F5);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-size: 0.9rem;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 16px rgba(30, 136, 229, 0.35);
            transition: all 0.2s ease;
            width: 100%;
            cursor: pointer;
        }

        .btn-auth-primary:hover {
            box-shadow: 0 6px 22px rgba(30, 136, 229, 0.45);
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

        .auth-alert-success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            border-radius: 12px;
            color: #15803d;
            font-size: 0.845rem;
            padding: 14px;
            margin-bottom: 20px;
        }

        .field-error {
            font-size: 0.78rem;
            color: #dc2626;
            margin-top: 4px;
        }

        .bg-dot-pattern {
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, #1E88E515 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
            z-index: 0;
        }
    </style>
</head>
<body>
    <div class="bg-dot-pattern"></div>

    <!-- Top Bar -->
    <div class="auth-topbar">
        <a href="{{ route('home') }}" class="brand-link">
            <div class="brand-icon"><i class="bi bi-controller"></i></div>
            <span>Game<span style="color: #1E88E5;">Hub</span></span>
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold" style="font-size: 0.82rem;">Login</a>
            <a href="{{ route('register') }}" class="btn btn-sm rounded-pill px-3 fw-semibold text-white" style="background: linear-gradient(135deg,#1E88E5,#8B5CF6); font-size: 0.82rem;">Register</a>
        </div>
    </div>

    <!-- Auth Card -->
    <div class="auth-wrap">
        <div class="auth-card">

            <!-- Header -->
            <div class="text-center mb-4">
                <div class="auth-icon-wrap">
                    <i class="bi bi-key-fill"></i>
                </div>
                <h4 class="fw-bold mb-1" style="color: #111827;">Forgot Password?</h4>
                <p class="text-muted small mb-0">Enter your email address to receive password reset instructions</p>
            </div>

            <!-- Error Alert -->
            @if($errors->any())
                <div class="auth-alert-error">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Success Alert -->
            @if(session('status'))
                <div class="auth-alert-success">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-envelope-check-fill text-success fs-5"></i>
                        <strong class="text-dark">Check Your Email Inbox!</strong>
                    </div>
                    <p class="mb-0 text-secondary small">{{ session('status') }}</p>
                </div>
            @endif

            <!-- Forgot Password Form -->
            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <label class="auth-label">Registered Email Address</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" name="email"
                               class="auth-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               placeholder="rivexagames@gmail.com"
                               value="{{ old('email', 'rivexagames@gmail.com') }}"
                               required autofocus>
                    </div>
                    @error('email')
                        <div class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-auth-primary">
                    <i class="bi bi-send-fill me-2"></i>SEND RESET LINK
                </button>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('login') }}" class="small text-decoration-none fw-semibold" style="color: #1E88E5;">
                    <i class="bi bi-arrow-left me-1"></i> Back to Login
                </a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
