<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Omkaar Logistics ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f3460;
            --primary-light: #1a4980;
            --accent-red: #e3001b;
            --accent-red-hover: #c40017;
            --accent-green: #10b981;
            --accent-blue: #3b82f6;
            --bg-dark: #070c14;
            --card-bg: rgba(13, 22, 38, 0.82);
            --border-glass: rgba(255, 255, 255, 0.12);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        /* Hide number input spinners/up-down controls across all browsers */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
            appearance: textfield;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #060a12;
            position: relative;
            overflow: hidden;
            color: var(--text-main);
        }

        /* ── Canvas for Animated Logistics Network ── */
        #networkCanvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        /* ── Dynamic Floating Aurora Orbs ── */
        .aurora-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            z-index: 0;
            animation: orbFloat 18s ease-in-out infinite alternate;
            pointer-events: none;
        }

        .orb-1 {
            width: 550px;
            height: 550px;
            background: radial-gradient(circle, #e3001b 0%, rgba(227, 0, 27, 0) 70%);
            top: -150px;
            left: -100px;
            animation-duration: 14s;
        }

        .orb-2 {
            width: 650px;
            height: 650px;
            background: radial-gradient(circle, #0f3460 0%, rgba(15, 52, 96, 0) 70%);
            bottom: -200px;
            right: -150px;
            animation-duration: 20s;
        }

        .orb-3 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #2563eb 0%, rgba(37, 99, 235, 0) 70%);
            top: 40%;
            right: 15%;
            animation-duration: 16s;
        }

        @keyframes orbFloat {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(40px, 60px) scale(1.08); }
            100% { transform: translate(-30px, -40px) scale(0.95); }
        }

        /* ── Main Glassmorphic Login Window ── */
        .login-window {
            position: relative;
            z-index: 10;
            width: 820px;
            max-width: 92%;
            background: var(--card-bg);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border-radius: 18px;
            border: 1px solid var(--border-glass);
            box-shadow:
                0 30px 80px rgba(0, 0, 0, 0.7),
                0 0 0 1px rgba(255, 255, 255, 0.08),
                0 0 40px rgba(227, 0, 27, 0.12);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            animation: windowEnter 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes windowEnter {
            from {
                opacity: 0;
                transform: translateY(28px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ── Top Window Bar (Modern ERP Titlebar) ── */
        .window-header {
            background: linear-gradient(135deg, #e3001b 0%, #b80016 50%, #880010 100%);
            color: #fff;
            padding: 13px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.25);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .header-title-wrap {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .header-icon {
            font-size: 16px;
        }

        .window-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .control-dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            display: inline-block;
            transition: transform 0.2s, opacity 0.2s;
            cursor: pointer;
        }
        .control-dot:hover { transform: scale(1.2); }
        .dot-green { background: #22c55e; box-shadow: 0 0 6px rgba(34, 197, 94, 0.6); }
        .dot-yellow { background: #eab308; box-shadow: 0 0 6px rgba(234, 179, 8, 0.6); }
        .dot-red { background: #ef4444; box-shadow: 0 0 6px rgba(239, 68, 68, 0.6); }

        /* ── Body Container (Two-column layout) ── */
        .window-body {
            display: flex;
            padding: 38px 40px;
            gap: 38px;
            position: relative;
        }

        /* ── Left Column: Brand & Value Props ── */
        .brand-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding-right: 32px;
            text-align: center;
        }

        .logo-card-wrap {
            position: relative;
            margin-bottom: 18px;
        }

        .logo-card {
            background: #ffffff;
            padding: 12px 16px;
            border-radius: 12px;
            box-shadow:
                0 12px 30px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.9);
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            display: block;
        }

        .logo-card:hover {
            transform: translateY(-4px) scale(1.03);
            box-shadow:
                0 18px 40px rgba(227, 0, 27, 0.25),
                0 0 0 2px rgba(227, 0, 27, 0.4);
        }

        .brand-logo-img {
            max-width: 175px;
            height: auto;
            display: block;
        }

        .brand-title {
            font-size: 15px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        .brand-tagline {
            font-size: 11px;
            font-weight: 500;
            color: #94a3b8;
            letter-spacing: 1.5px;
            margin-bottom: 18px;
            text-transform: uppercase;
        }

        .feature-chips {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 100%;
            margin-top: 4px;
        }

        .chip-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 11.5px;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }
        .chip-item:hover {
            background: rgba(255, 255, 255, 0.09);
            color: #fff;
        }
        .chip-icon { font-size: 14px; }

        .system-status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: #64748b;
            margin-top: 18px;
        }
        .status-pulse {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 8px #10b981;
            animation: statusPulse 2s infinite;
        }
        @keyframes statusPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }

        /* ── Right Column: Interactive Login Form ── */
        .form-section {
            flex: 1.35;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header-text {
            margin-bottom: 22px;
        }

        .form-header-text h2 {
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 4px;
        }

        .form-header-text p {
            font-size: 12.5px;
            color: var(--text-muted);
        }

        .input-group-row {
            margin-bottom: 18px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .input-label {
            font-size: 12px;
            font-weight: 600;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #64748b;
            font-size: 15px;
            pointer-events: none;
            transition: color 0.25s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-input {
            width: 100%;
            padding: 11px 14px 11px 42px;
            font-size: 13.5px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 9px;
            color: #ffffff;
            outline: none;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .form-input::placeholder {
            color: #64748b;
            font-size: 13px;
        }

        .form-input:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #e3001b;
            box-shadow: 0 0 0 3px rgba(227, 0, 27, 0.25), 0 4px 15px rgba(227, 0, 27, 0.15);
        }

        .form-input:focus + .input-icon,
        .input-wrapper:focus-within .input-icon {
            color: #e3001b;
        }

        select.form-input {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 6'%3E%3Cpath fill='%2394a3b8' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 9px 6px;
            padding-right: 36px;
        }

        select.form-input option {
            background-color: #0f172a;
            color: #ffffff;
            padding: 10px;
        }

        /* Toggle Password Eye Button */
        .btn-toggle-password {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: color 0.2s, background 0.2s;
        }

        .btn-toggle-password:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
        }

        /* ── Action Buttons ── */
        .form-actions-row {
            margin-top: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-submit-main {
            flex: 1;
            padding: 12px 20px;
            font-size: 13.5px;
            font-weight: 600;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #e3001b 0%, #b80016 100%);
            color: #ffffff;
            border: none;
            border-radius: 9px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 20px rgba(227, 0, 27, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.2);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-submit-main:hover {
            background: linear-gradient(135deg, #ff1a35 0%, #d4001a 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(227, 0, 27, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        .btn-submit-main:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(227, 0, 27, 0.4);
        }

        .btn-reset-ghost {
            padding: 12px 18px;
            font-size: 13.5px;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.06);
            color: #94a3b8;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 9px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-reset-ghost:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.25);
        }

        /* ── Error Banner ── */
        .alert-error-banner {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #fca5a5;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 12.5px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        /* ── Footer / Copyright ── */
        .login-footer-text {
            position: absolute;
            bottom: 18px;
            font-size: 11.5px;
            color: rgba(148, 163, 184, 0.6);
            letter-spacing: 0.5px;
            z-index: 10;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .window-body {
                flex-direction: column;
                padding: 24px;
                gap: 24px;
            }
            .brand-section {
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                padding-right: 0;
                padding-bottom: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Dynamic Animated Interactive Logistics Background Canvas -->
    <canvas id="networkCanvas"></canvas>

    <!-- Floating Background Aurora Orbs -->
    <div class="aurora-orb orb-1"></div>
    <div class="aurora-orb orb-2"></div>
    <div class="aurora-orb orb-3"></div>

    <!-- Main Glassmorphism Login Window -->
    <div class="login-window">
        <!-- Modern Windows/ERP Titlebar -->
        <div class="window-header">
            <div class="header-title-wrap">
                <span class="header-icon">🚛</span>
                <span>OMKAAR LOGISTICS ERP • SYSTEM LOGIN</span>
            </div>
            <div class="window-controls">
                <span class="control-dot dot-green" title="System Online"></span>
                <span class="control-dot dot-yellow" title="Encrypted Connection"></span>
                <span class="control-dot dot-red" title="Secure Session"></span>
            </div>
        </div>

        <div class="window-body">
            <!-- Left Side: Brand & Feature Highlights -->
            <div class="brand-section">
                <div class="logo-card-wrap">
                    <div class="logo-card">
                        <img src="{{ asset('assets/logo.jpg') }}" alt="Omkaar Logistics Logo" class="brand-logo-img">
                    </div>
                </div>

                <div class="brand-title">OMKAAR LOGISTICS</div>
                <div class="brand-tagline">FAST • SAFE • RELIABLE</div>

                <div class="feature-chips">
                    <div class="chip-item">
                        <span class="chip-icon">⚡</span>
                        <span>High-Speed C.N &amp; Billing</span>
                    </div>
                    <div class="chip-item">
                        <span class="chip-icon">🛡️</span>
                        <span>Enterprise Access Control</span>
                    </div>
                    <div class="chip-item">
                        <span class="chip-icon">📊</span>
                        <span>Real-Time Fleet &amp; Ledger</span>
                    </div>
                </div>

                <div class="system-status-indicator">
                    <span class="status-pulse"></span>
                    <span>System v10.10.1005 (Active)</span>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <form action="{{ route('login.post') }}" method="POST" class="form-section" id="loginForm">
                @csrf

                <div class="form-header-text">
                    <h2>Welcome Back</h2>
                    <p>Enter your credentials to access the ERP dashboard</p>
                </div>

                @if ($errors->any())
                    <div class="alert-error-banner">
                        <span>⚠️</span>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- Company Selector -->
                <div class="input-group-row">
                    <label for="company" class="input-label">
                        <span>Company Profile</span>
                    </label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M9 8h1"/><path d="M9 12h1"/><path d="M9 16h1"/><path d="M14 8h1"/><path d="M14 12h1"/><path d="M14 16h1"/><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/></svg>
                        </span>
                        <select name="company" id="company" class="form-input" required>
                            @if(isset($companies) && count($companies) > 0)
                                @foreach ($companies as $comp)
                                    <option value="{{ $comp->id }}" {{ old('company') == $comp->id ? 'selected' : '' }}>
                                        {{ $comp->name }}
                                    </option>
                                @endforeach
                            @else
                                <option value="1">OMKAAR LOGISTICS</option>
                            @endif
                        </select>
                    </div>
                </div>

                <!-- Username Input -->
                <div class="input-group-row">
                    <label for="username" class="input-label">
                        <span>User Name</span>
                    </label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <input type="text" name="username" id="username" class="form-input no-uppercase" data-case="sensitive" placeholder="Enter username" value="{{ old('username') }}" required autofocus autocomplete="username">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="input-group-row">
                    <label for="password" class="input-label">
                        <span>Password</span>
                    </label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input type="password" name="password" id="password" class="form-input no-uppercase" data-case="sensitive" style="padding-right: 42px; text-transform: none !important;" placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="btn-toggle-password" id="togglePasswordBtn" title="Toggle password visibility">
                            <svg id="eyeIconOpen" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eyeIconClosed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Actions -->
                <div class="form-actions-row">
                    <button type="submit" class="btn-submit-main" id="btnLoginSubmit">
                        <span>Sign In</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                    <button type="reset" class="btn-reset-ghost" title="Reset fields">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        <span>Clear</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bottom Copyright -->
    <div class="login-footer-text">
        OMKAAR LOGISTICS ERP System © {{ date('Y') }}. Fast • Safe • Reliable.
    </div>

    <!-- Interactive Logistics Network Canvas Animation Script -->
    <script>
        // Password toggle visibility handler
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const eyeOpen = document.getElementById('eyeIconOpen');
        const eyeClosed = document.getElementById('eyeIconClosed');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeOpen.style.display = 'none';
                    eyeClosed.style.display = 'block';
                } else {
                    passwordInput.type = 'password';
                    eyeOpen.style.display = 'block';
                    eyeClosed.style.display = 'none';
                }
            });
        }

        // Animated Logistics Nodes & Connecting Lines (Canvas)
        const canvas = document.getElementById('networkCanvas');
        const ctx = canvas.getContext('2d');

        let width, height;
        let particles = [];
        const particleCount = 45;
        const maxDistance = 140;

        function resizeCanvas() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        }

        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        class Particle {
            constructor() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.vx = (Math.random() - 0.5) * 0.7;
                this.vy = (Math.random() - 0.5) * 0.7;
                this.radius = Math.random() * 2.2 + 1;
                // Alternate particle color accents
                this.color = Math.random() > 0.4 ? 'rgba(59, 130, 246, ' : 'rgba(227, 0, 27, ';
                this.alpha = Math.random() * 0.5 + 0.3;
            }

            update() {
                this.x += this.vx;
                this.y += this.vy;

                if (this.x < 0 || this.x > width) this.vx *= -1;
                if (this.y < 0 || this.y > height) this.vy *= -1;
            }

            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                ctx.fillStyle = this.color + this.alpha + ')';
                ctx.fill();
            }
        }

        for (let i = 0; i < particleCount; i++) {
            particles.push(new Particle());
        }

        function animate() {
            ctx.clearRect(0, 0, width, height);

            // Draw connecting transit lines
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x;
                    const dy = particles[i].y - particles[j].y;
                    const dist = Math.sqrt(dx * dx + dy * dy);

                    if (dist < maxDistance) {
                        const opacity = (1 - dist / maxDistance) * 0.22;
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.strokeStyle = `rgba(148, 163, 184, ${opacity})`;
                        ctx.lineWidth = 0.8;
                        ctx.stroke();
                    }
                }
            }

            // Draw and update particles
            particles.forEach(p => {
                p.update();
                p.draw();
            });

            requestAnimationFrame(animate);
        }

        animate();

        // Submit Button Loading Feedback
        const loginForm = document.getElementById('loginForm');
        const btnSubmit = document.getElementById('btnLoginSubmit');

        if (loginForm && btnSubmit) {
            loginForm.addEventListener('submit', function() {
                btnSubmit.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 0.8s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    <span>Authenticating...</span>
                `;
                btnSubmit.style.opacity = '0.85';
                btnSubmit.style.pointerEvents = 'none';
            });
        }
    </script>
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</body>
</html>
