<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= esc($site_name ?? 'Admin') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #0f172a;
            overflow: hidden;
        }

        /* ── Left panel ── */
        .auth-panel {
            width: 440px;
            min-height: 100vh;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 2.75rem;
            position: relative;
            z-index: 2;
            box-shadow: 8px 0 40px rgba(0,0,0,.25);
            flex-shrink: 0;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: 2.5rem;
        }
        .auth-brand-icon {
            width: 38px; height: 38px;
            background: #16213e;
            border-radius: .5rem;
            display: flex; align-items: center; justify-content: center;
            color: #63b3ed;
            font-size: 1.1rem;
        }
        .auth-brand-name { font-weight: 700; font-size: 1.1rem; color: #0f172a; letter-spacing: -.02em; }

        h2.auth-title { font-size: 1.55rem; font-weight: 700; color: #0f172a; margin-bottom: .4rem; letter-spacing: -.02em; }
        .auth-subtitle { color: #64748b; font-size: .9rem; margin-bottom: 2rem; }

        .form-label { font-weight: 500; font-size: .85rem; color: #374151; }
        .form-control {
            border-radius: .5rem;
            border: 1.5px solid #e2e8f0;
            font-size: .875rem;
            padding: .6rem .9rem;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus {
            border-color: #63b3ed;
            box-shadow: 0 0 0 3px rgba(99,179,237,.15);
        }

        .btn-auth {
            background: #16213e;
            color: #fff;
            border: none;
            border-radius: .5rem;
            padding: .65rem 1.2rem;
            font-size: .9rem;
            font-weight: 600;
            letter-spacing: .01em;
            width: 100%;
            transition: background .15s, transform .1s;
        }
        .btn-auth:hover { background: #1a2f5a; transform: translateY(-1px); }
        .btn-auth:active { transform: translateY(0); }

        .auth-footer { margin-top: 2rem; text-align: center; font-size: .82rem; color: #94a3b8; }
        .auth-footer a { color: #3b82f6; text-decoration: none; }

        /* ── Right panel ── */
        .auth-visual {
            flex: 1;
            background: linear-gradient(135deg, #16213e 0%, #0d1b2a 50%, #1a1a2e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .auth-visual::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99,179,237,.15) 0%, transparent 70%);
            top: -100px; right: -100px;
        }
        .auth-visual::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(118,75,162,.15) 0%, transparent 70%);
            bottom: -80px; left: -80px;
        }

        .auth-visual-content { position: relative; z-index: 1; text-align: center; padding: 2rem; max-width: 400px; }
        .auth-visual-title { color: rgba(255,255,255,.9); font-size: 2rem; font-weight: 700; letter-spacing: -.03em; line-height: 1.2; margin-bottom: 1rem; }
        .auth-visual-sub { color: rgba(255,255,255,.45); font-size: .9rem; line-height: 1.6; }
        .auth-dots { display: flex; gap: .4rem; justify-content: center; margin-top: 2rem; }
        .auth-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,.2); }
        .auth-dot.active { background: #63b3ed; }

        /* Floating grid decoration */
        .grid-decoration {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(99,179,237,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,179,237,.04) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        @media (max-width: 767px) {
            .auth-panel { width: 100%; box-shadow: none; padding: 2rem 1.5rem; }
            .auth-visual { display: none; }
        }
    </style>
</head>
<body>

<!-- ── Left: Login Form ── -->
<div class="auth-panel">

    <!-- Brand -->
    <div class="auth-brand">
        <div class="auth-brand-icon"><i class="bi bi-grid-3x3-gap-fill"></i></div>
        <span class="auth-brand-name"><?= esc($site_name ?? 'Admin') ?></span>
    </div>

    <h2 class="auth-title">Welcome back</h2>
    <p class="auth-subtitle">Sign in to your account to continue</p>

    <!-- Flash messages -->
    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger d-flex gap-2 align-items-center mb-3 py-2 px-3" style="font-size:.85rem;border-radius:.5rem;border:none">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
            <?= session('error') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->has('success')): ?>
        <div class="alert alert-success d-flex gap-2 align-items-center mb-3 py-2 px-3" style="font-size:.85rem;border-radius:.5rem;border:none">
            <i class="bi bi-check-circle-fill flex-shrink-0"></i>
            <?= session('success') ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('auth/login') ?>">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" id="email" name="email"
                   class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>"
                   value="<?= old('email') ?>"
                   placeholder="you@example.com"
                   autocomplete="username"
                   required autofocus>
            <?php if (session('errors.email')): ?>
                <div class="invalid-feedback"><?= session('errors.email') ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-2">
            <label for="password" class="form-label d-flex justify-content-between">
                <span>Password</span>
                <a href="<?= route_to('auth.forgot') ?>" class="text-muted fw-normal small" style="font-size:.78rem">Forgot password?</a>
            </label>
            <div class="input-group">
                <input type="password" id="password" name="password"
                       class="form-control border-end-0 <?= session('errors.password') ? 'is-invalid' : '' ?>"
                       placeholder="••••••••"
                       autocomplete="current-password"
                       required>
                <button type="button" class="btn btn-outline-secondary border-start-0" id="togglePwd" style="border-radius:0 .5rem .5rem 0">
                    <i class="bi bi-eye" id="togglePwdIcon"></i>
                </button>
            </div>
        </div>

        <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label for="remember" class="form-check-label" style="font-size:.85rem">Remember me for 30 days</label>
        </div>

        <button type="submit" class="btn-auth">
            Sign in
            <i class="bi bi-arrow-right ms-2"></i>
        </button>
    </form>

    <div class="auth-footer">
        &copy; <?= date('Y') ?> <?= esc($site_name ?? 'Admin') ?>.
        All rights reserved.
    </div>
</div>

<!-- ── Right: Visual ── -->
<div class="auth-visual">
    <div class="grid-decoration"></div>
    <div class="auth-visual-content">
        <div class="auth-visual-title">
            One platform.<br>Every tool you need.
        </div>
        <p class="auth-visual-sub">
            Manage users, roles, content, and settings from a single, powerful dashboard.
        </p>
        <div class="auth-dots">
            <div class="auth-dot active"></div>
            <div class="auth-dot"></div>
            <div class="auth-dot"></div>
        </div>
    </div>
</div>

<script>
document.getElementById('togglePwd')?.addEventListener('click', function() {
    const f = document.getElementById('password');
    const i = document.getElementById('togglePwdIcon');
    if (f.type === 'password') { f.type = 'text';     i.className = 'bi bi-eye-slash'; }
    else                       { f.type = 'password'; i.className = 'bi bi-eye';       }
});
</script>
</body>
</html>
