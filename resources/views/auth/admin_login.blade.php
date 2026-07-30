<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GameHub Control Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #F4F8FC 0%, #EEF2FF 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.10);
            width: 100%;
            max-width: 420px;
            padding: 40px 36px;
        }

        .brand-logo {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #1E88E5, #8B5CF6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.5rem;
            margin: 0 auto 16px;
        }

        .form-control {
            border-radius: 10px;
            border: 1.5px solid #E5E7EB;
            padding: 10px 14px;
            font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            border-color: #1E88E5;
            box-shadow: 0 0 0 3px rgba(30,136,229,0.12);
        }

        .btn-login {
            background: linear-gradient(135deg, #1E88E5, #42A5F5);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 11px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 14px rgba(30,136,229,0.35);
            transition: all 0.2s;
        }

        .btn-login:hover {
            box-shadow: 0 6px 20px rgba(30,136,229,0.45);
            color: #fff;
            transform: translateY(-1px);
        }

        .alert-error {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 10px;
            color: #dc2626;
            font-size: 0.855rem;
            padding: 10px 14px;
        }

        .alert-success-box {
            background: rgba(34,197,94,0.08);
            border: 1px solid rgba(34,197,94,0.2);
            border-radius: 10px;
            color: #15803d;
            font-size: 0.855rem;
            padding: 10px 14px;
        }

        .input-group-text {
            border-radius: 10px 0 0 10px;
            border: 1.5px solid #E5E7EB;
            border-right: none;
            background: #F9FAFB;
            color: #9CA3AF;
        }

        .input-group .form-control {
            border-radius: 0 10px 10px 0;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <!-- Brand -->
        <div class="text-center mb-4">
            <div class="brand-logo"><i class="bi bi-controller"></i></div>
            <h5 class="fw-bold mb-0" style="color: #111827;">GameHub Admin</h5>
            <p class="text-muted small mt-1">Control Center — Authorized Access Only</p>
        </div>

        @if(session('error'))
            <div class="alert-error mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>
        @endif

        @if(session('success'))
            <div class="alert-success-box mb-3"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
        @endif

        <form method="POST" action="/admin/login">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size: 0.82rem; color: #374151;">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           placeholder="admin@gamehub.com" value="{{ old('email') }}" required autofocus>
                </div>
                @error('email')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold" style="font-size: 0.82rem; color: #374151;">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                           placeholder="••••••••" required>
                </div>
                @error('password')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small text-muted" for="remember">Remember me</label>
                </div>
            </div>

            <button type="submit" class="btn btn-login w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In to Admin Panel
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('home') }}" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Back to Frontend
            </a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
