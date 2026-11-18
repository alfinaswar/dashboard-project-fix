<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Masuk | {{ setting('company_name', config('app.name', 'Tracker Keuangan')) }}</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- SweetAlert2 Theme -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">

    <style>
        :root {
            --primary: #2563EB;
            --primary-hover: #1D4ED8;
            --primary-subtle: rgba(37, 99, 235, 0.08);
            --primary-glow: rgba(37, 99, 235, 0.25);
            --dark-surface: #0B0F19;
            --dark-card: rgba(17, 24, 39, 0.7);
            --text-heading: #0F172A;
            --text-body: #475569;
            --text-muted: #94A3B8;
            --border-color: #E2E8F0;
            --border-focus: #3B82F6;
            --radius-md: 12px;
            --radius-lg: 18px;
            --radius-xl: 24px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        html, body {
            height: 100%;
            background-color: #FFFFFF;
            color: var(--text-heading);
            -webkit-font-smoothing: antialiased;
        }

        .split-layout {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* =========================================
           LEFT COLUMN: Brand & Showcase
           ========================================= */
        .showcase-column {
            flex: 1.1;
            background: linear-gradient(145deg, #090D16 0%, #0F172A 50%, #171E36 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3.5rem 3.5rem;
            color: #FFFFFF;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* Ambient Glowing Orbs */
        .showcase-glow-1 {
            position: absolute;
            top: -120px;
            left: -80px;
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.28) 0%, rgba(37, 99, 235, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
            filter: blur(40px);
        }

        .showcase-glow-2 {
            position: absolute;
            bottom: -100px;
            right: -60px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.22) 0%, rgba(99, 102, 241, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
            filter: blur(50px);
        }

        /* Subtle Background Grid Pattern */
        .showcase-grid-pattern {
            position: absolute;
            inset: 0;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 36px 36px;
            pointer-events: none;
            mask-image: radial-gradient(ellipse at center, rgba(0,0,0,1) 40%, rgba(0,0,0,0) 80%);
            -webkit-mask-image: radial-gradient(ellipse at center, rgba(0,0,0,1) 40%, rgba(0,0,0,0) 80%);
        }

        .showcase-header {
            position: relative;
            z-index: 2;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-icon-box {
            width: 46px;
            height: 46px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, #2563EB 0%, #4F46E5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 1.25rem;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }

        .brand-badge:hover .brand-icon-box {
            transform: scale(1.05) rotate(2deg);
        }

        .brand-text-group {
            display: flex;
            flex-direction: column;
        }

        .brand-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #FFFFFF;
            letter-spacing: -0.3px;
            line-height: 1.2;
        }

        .brand-tag {
            font-size: 0.72rem;
            color: #94A3B8;
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .showcase-body {
            position: relative;
            z-index: 2;
            margin: 2.5rem 0;
        }

        .headline-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 99px;
            background: rgba(37, 99, 235, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #60A5FA;
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .headline-pill .pill-dot {
            width: 7px;
            height: 7px;
            background-color: #38BDF8;
            border-radius: 50%;
            box-shadow: 0 0 10px #38BDF8;
            animation: pulseDot 2s infinite;
        }

        @keyframes pulseDot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.85); }
        }

        .showcase-headline {
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.8px;
            color: #F8FAFC;
            margin-bottom: 1rem;
        }

        .showcase-headline span {
            background: linear-gradient(135deg, #60A5FA 0%, #A78BFA 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .showcase-description {
            font-size: 0.96rem;
            line-height: 1.6;
            color: #94A3B8;
            max-width: 480px;
            margin-bottom: 2.25rem;
        }

        /* Glassmorphism Metric Showcase Card */
        .metric-card {
            background: rgba(17, 24, 39, 0.65);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            padding: 1.5rem 1.75rem;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.6);
            max-width: 470px;
            margin-bottom: 2rem;
            position: relative;
        }

        .metric-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .metric-title {
            font-size: 0.82rem;
            font-weight: 600;
            color: #94A3B8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-badge-live {
            background: rgba(16, 185, 129, 0.15);
            color: #34D399;
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 99px;
            padding: 3px 10px;
            font-size: 0.72rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .metric-badge-live::before {
            content: '';
            width: 6px;
            height: 6px;
            background-color: #34D399;
            border-radius: 50%;
        }

        .metric-body {
            display: flex;
            align-items: baseline;
            gap: 12px;
            margin-bottom: 0.75rem;
        }

        .metric-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: #FFFFFF;
            letter-spacing: -0.5px;
        }

        .metric-trend {
            font-size: 0.82rem;
            font-weight: 600;
            color: #34D399;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .metric-progress-bg {
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 99px;
            overflow: hidden;
        }

        .metric-progress-fill {
            width: 78%;
            height: 100%;
            background: linear-gradient(90deg, #2563EB, #60A5FA, #34D399);
            border-radius: 99px;
        }

        /* Features List */
        .showcase-features {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 470px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.88rem;
            color: #CBD5E1;
        }

        .feature-icon-wrapper {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: rgba(37, 99, 235, 0.2);
            color: #60A5FA;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            flex-shrink: 0;
            border: 1px solid rgba(59, 130, 246, 0.25);
        }

        .showcase-footer {
            position: relative;
            z-index: 2;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #64748B;
        }

        .showcase-footer-security {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #94A3B8;
        }

        .showcase-footer-security i {
            color: #10B981;
        }

        /* =========================================
           RIGHT COLUMN: Login Form
           ========================================= */
        .form-column {
            flex: 0.95;
            background-color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2.5rem;
            position: relative;
            min-height: 100vh;
        }

        .form-content-wrap {
            width: 100%;
            max-width: 440px;
            animation: fadeInSlide 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeInSlide {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile Brand Top (only on small screens) */
        .mobile-brand-top {
            display: none;
            align-items: center;
            gap: 12px;
            margin-bottom: 2rem;
            text-decoration: none;
        }

        .mobile-brand-icon {
            width: 42px;
            height: 42px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, #2563EB 0%, #4F46E5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 1.15rem;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
        }

        .mobile-brand-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-heading);
        }

        .form-header {
            margin-bottom: 2.25rem;
        }

        .welcome-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--primary-subtle);
            color: var(--primary);
            border-radius: 99px;
            padding: 5px 12px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 0.85rem;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .form-title {
            font-size: 1.85rem;
            font-weight: 800;
            color: var(--text-heading);
            letter-spacing: -0.5px;
            line-height: 1.25;
            margin-bottom: 0.5rem;
        }

        .form-subtitle {
            font-size: 0.92rem;
            color: var(--text-body);
            line-height: 1.5;
        }

        /* Inline Alert Messages */
        .auth-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border-radius: var(--radius-md);
            padding: 0.9rem 1rem;
            font-size: 0.86rem;
            line-height: 1.45;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .auth-alert-danger {
            background-color: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        .auth-alert-danger i {
            color: #EF4444;
            font-size: 1rem;
            margin-top: 2px;
        }

        .auth-alert-success {
            background-color: #F0FDF4;
            color: #166534;
            border: 1px solid #BBF7D0;
        }

        .auth-alert-success i {
            color: #22C55E;
            font-size: 1rem;
            margin-top: 2px;
        }

        .auth-alert-close {
            position: absolute;
            top: 10px;
            right: 12px;
            background: none;
            border: none;
            color: inherit;
            font-size: 1.15rem;
            cursor: pointer;
            opacity: 0.6;
            line-height: 1;
            padding: 0;
        }

        .auth-alert-close:hover {
            opacity: 1;
        }

        /* Form Inputs */
        .form-group {
            margin-bottom: 1.35rem;
        }

        .input-label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .input-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .input-label .required-star {
            color: #EF4444;
        }

        .input-box {
            position: relative;
            display: flex;
            align-items: center;
            background: #F8FAFC;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .input-box:focus-within {
            background: #FFFFFF;
            border-color: var(--border-focus);
            box-shadow: 0 0 0 4px var(--primary-subtle);
        }

        .input-box.is-invalid {
            border-color: #EF4444;
            background: #FFFBFB;
        }

        .input-box.is-invalid:focus-within {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
        }

        .input-icon-left {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 100%;
            color: #94A3B8;
            font-size: 0.95rem;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .input-box:focus-within .input-icon-left {
            color: var(--primary);
        }

        .form-input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            padding: 0.88rem 0.5rem 0.88rem 0;
            font-size: 0.92rem;
            color: var(--text-heading);
            font-weight: 500;
        }

        .form-input::placeholder {
            color: #94A3B8;
            font-weight: 400;
        }

        /* Toggle Password Button */
        .btn-toggle-password {
            background: none;
            border: none;
            outline: none;
            color: #94A3B8;
            padding: 0 1rem;
            cursor: pointer;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            transition: color 0.2s ease;
        }

        .btn-toggle-password:hover {
            color: var(--primary);
        }

        .input-feedback-error {
            font-size: 0.78rem;
            color: #DC2626;
            margin-top: 0.4rem;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }

        /* Remember Me Row */
        .form-options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.75rem;
            margin-top: -0.25rem;
        }

        .custom-checkbox-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
            margin-bottom: 0;
        }

        .custom-checkbox-wrap input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border: 1.5px solid #CBD5E1;
            border-radius: 5px;
            outline: none;
            background-color: #FFFFFF;
            cursor: pointer;
            display: grid;
            place-content: center;
            transition: all 0.2s ease;
        }

        .custom-checkbox-wrap input[type="checkbox"]::before {
            content: "\f00c";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            font-size: 10px;
            color: #FFFFFF;
            transform: scale(0);
            transition: transform 0.15s ease-in-out;
        }

        .custom-checkbox-wrap input[type="checkbox"]:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .custom-checkbox-wrap input[type="checkbox"]:checked::before {
            transform: scale(1);
        }

        .custom-checkbox-wrap input[type="checkbox"]:focus {
            box-shadow: 0 0 0 3px var(--primary-subtle);
        }

        .checkbox-label-text {
            font-size: 0.85rem;
            color: var(--text-body);
            font-weight: 500;
        }

        .options-note {
            font-size: 0.78rem;
            color: #94A3B8;
        }

        /* Submit Button */
        .btn-submit-login {
            width: 100%;
            background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
            border: none;
            border-radius: var(--radius-md);
            color: #FFFFFF !important;
            padding: 0.92rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-submit-login:hover {
            background: linear-gradient(135deg, #1D4ED8 0%, #1E40AF 100%);
            transform: translateY(-2px);
            box-shadow: 0 14px 25px -5px rgba(37, 99, 235, 0.5);
            color: #FFFFFF !important;
        }

        .btn-submit-login:active {
            transform: translateY(0);
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }

        .btn-submit-login:disabled {
            opacity: 0.75;
            cursor: not-allowed;
            transform: none !important;
        }

        .btn-submit-login i {
            transition: transform 0.2s ease;
        }

        .btn-submit-login:hover i {
            transform: translateX(3px);
        }

        /* Form Footer / Security Trust */
        .form-bottom-info {
            margin-top: 2.25rem;
            padding-top: 1.5rem;
            border-top: 1px solid #F1F5F9;
            text-align: center;
        }

        .security-stamp {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 99px;
            padding: 5px 14px;
            font-size: 0.74rem;
            color: #64748B;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .security-stamp i {
            color: #10B981;
        }

        .copyright-text {
            font-size: 0.78rem;
            color: #94A3B8;
        }

        /* Loading Spinner Animation */
        .spin-icon {
            display: inline-block;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* =========================================
           RESPONSIVE BEHAVIOR
           ========================================= */
        @media (max-width: 1024px) {
            .showcase-column {
                padding: 2.5rem 2rem;
            }
            .showcase-headline {
                font-size: 1.95rem;
            }
            .form-column {
                padding: 2.5rem 2rem;
            }
        }

        @media (max-width: 991.98px) {
            .split-layout {
                flex-direction: column;
            }
            .showcase-column {
                display: none; /* Hide showcase column on mobile/tablet to prioritize fast login */
            }
            .form-column {
                flex: 1;
                min-height: 100vh;
                padding: 3rem 1.5rem;
                background-color: #F8FAFC;
            }
            .form-content-wrap {
                background: #FFFFFF;
                padding: 2.25rem 1.75rem;
                border-radius: var(--radius-xl);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
                border: 1px solid var(--border-color);
            }
            .mobile-brand-top {
                display: flex;
            }
        }

        @media (max-width: 480px) {
            .form-content-wrap {
                padding: 1.75rem 1.25rem;
                border-radius: var(--radius-lg);
            }
            .form-title {
                font-size: 1.55rem;
            }
            .form-options-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
    </style>
</head>

<body>
<div class="split-layout">

    <!-- ========================================================
         LEFT COLUMN: Premium Branding & Live Features Showcase
         ======================================================== -->
    <div class="showcase-column">
        <!-- Ambient Background Effects -->
        <div class="showcase-glow-1"></div>
        <div class="showcase-glow-2"></div>
        <div class="showcase-grid-pattern"></div>

        <!-- Top Header & Brand -->
        <div class="showcase-header">
            <a href="/" class="brand-badge">
                <div class="brand-icon-box">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="brand-text-group">
                    <span class="brand-title">{{ setting('company_name', 'Tracker Keuangan') }}</span>
                    <span class="brand-tag">Enterprise Management</span>
                </div>
            </a>
        </div>

        <!-- Center Showcase Content -->
        <div class="showcase-body">
            <div class="headline-pill">
                <span class="pill-dot"></span>
                <span>Financial & Operations Suite</span>
            </div>

            <h1 class="showcase-headline">
                Kendali Finansial &amp; Operasional <span>Terintegrasi.</span>
            </h1>

            <p class="showcase-description">
                Pantau pesanan pekerjaan, invoice digital, status pembayaran, serta perjanjian klien secara real-time dalam satu portal terpusat.
            </p>

            <!-- Metric Preview Card (Glassmorphism) -->
            <div class="metric-card">
                <div class="metric-header">
                    <span class="metric-title">Monitoring Arus Kas</span>
                    <span class="metric-badge-live">Realtime Sync</span>
                </div>
                <div class="metric-body">
                    <span class="metric-value">Efisiensi +40%</span>
                    <span class="metric-trend">
                        <i class="fas fa-arrow-up"></i> Terakselerasi
                    </span>
                </div>
                <div class="metric-progress-bg">
                    <div class="metric-progress-fill"></div>
                </div>
            </div>

            <!-- Value Highlights -->
            <div class="showcase-features">
                <div class="feature-item">
                    <div class="feature-icon-wrapper">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <span>Otomasi invoice dengan penomoran unik dan unduh PDF/Print instan</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon-wrapper">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <span>Pelacakan progress pekerjaan, langganan aktif, dan rekonsiliasi fee</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon-wrapper">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <span>Manajemen perjanjian (agreements) dan dokumen klien terstruktur</span>
                </div>
            </div>
        </div>

        <!-- Bottom Security & Trust -->
        <div class="showcase-footer">
            <div class="showcase-footer-security">
                <i class="fas fa-shield-alt"></i>
                <span>Enkripsi Berstandar Industri &bull; Akses Terverifikasi</span>
            </div>
            <span>v2.5</span>
        </div>
    </div>


    <!-- ========================================================
         RIGHT COLUMN: Modern Clean Login Form
         ======================================================== -->
    <div class="form-column">
        <div class="form-content-wrap">

            <!-- Mobile Brand Header (Visible on tablet/mobile) -->
            <a href="/" class="mobile-brand-top">
                <div class="mobile-brand-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <span class="mobile-brand-name">{{ setting('company_name', 'Tracker Keuangan') }}</span>
            </a>

            <!-- Form Title & Greeting -->
            <div class="form-header">
                <div class="welcome-pill">
                    <i class="fas fa-lock"></i>
                    <span>Portal Autentikasi</span>
                </div>
                <h2 class="form-title">Selamat Datang</h2>
                <p class="form-subtitle">
                    Silakan masukkan email dan kata sandi Anda untuk mengakses dashboard pengelolaan.
                </p>
            </div>

            @php
                // Setup SweetAlert2 data
                $sweetAlerts = [];

                if (session('error')) {
                    $sweetAlerts[] = [
                        'type' => 'error',
                        'title' => 'Gagal Masuk',
                        'text' => session('error')
                    ];
                }

                if ($errors->any()) {
                    $sweetAlerts[] = [
                        'type' => 'error',
                        'title' => 'Data Belum Lengkap / Salah',
                        'text' => 'Periksa kembali isian form berikut:',
                        'list' => array_values(array_unique($errors->all())),
                    ];
                }

                if (session('status')) {
                    $sweetAlerts[] = [
                        'type' => 'success',
                        'title' => 'Informasi',
                        'text' => session('status')
                    ];
                }
            @endphp

            @if($sweetAlerts)
                <div id="sweet-alerts" data-alerts="{{ json_encode($sweetAlerts) }}" hidden></div>
            @endif

            <!-- Inline Alert (Instant Fallback & Feedback) -->
            @if(session('error'))
                <div class="auth-alert auth-alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <div style="flex: 1;">{{ session('error') }}</div>
                    <button type="button" class="auth-alert-close" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
                </div>
            @endif

            @if(session('status'))
                <div class="auth-alert auth-alert-success" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <div style="flex: 1;">{{ session('status') }}</div>
                    <button type="button" class="auth-alert-close" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
                </div>
            @endif

            @if($errors->any() && !session('error'))
                <div class="auth-alert auth-alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div style="flex: 1;">
                        <span style="font-weight: 600; display: block; margin-bottom: 2px;">Terjadi Kesalahan:</span>
                        {{ $errors->first() }}
                    </div>
                    <button type="button" class="auth-alert-close" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login.store') }}" method="POST" id="authLoginForm" novalidate autocomplete="on">
                @csrf

                <!-- Email Input -->
                <div class="form-group">
                    <div class="input-label-row">
                        <label for="email" class="input-label">
                            Alamat Email <span class="required-star">*</span>
                        </label>
                    </div>
                    <div class="input-box @error('email') is-invalid @enderror">
                        <span class="input-icon-left">
                            <i class="far fa-envelope"></i>
                        </span>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="form-input" 
                            placeholder="nama@perusahaan.com" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus 
                            autocomplete="username"
                        >
                    </div>
                    @error('email')
                        <div class="input-feedback-error">
                            <i class="fas fa-circle-exclamation"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="form-group">
                    <div class="input-label-row">
                        <label for="password" class="input-label">
                            Kata Sandi <span class="required-star">*</span>
                        </label>
                    </div>
                    <div class="input-box @error('password') is-invalid @enderror">
                        <span class="input-icon-left">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-input" 
                            placeholder="Masukkan kata sandi akun" 
                            required 
                            autocomplete="current-password"
                        >
                        <button 
                            type="button" 
                            class="btn-toggle-password" 
                            id="btnTogglePassword" 
                            aria-label="Tampilkan atau sembunyikan kata sandi" 
                            tabindex="-1"
                        >
                            <i class="far fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="input-feedback-error">
                            <i class="fas fa-circle-exclamation"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <!-- Remember Me & Notes -->
                <div class="form-options-row">
                    <label class="custom-checkbox-wrap" for="remember">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            id="remember" 
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <span class="checkbox-label-text">Ingat sesi saya</span>
                    </label>
                    <span class="options-note">
                        <i class="fas fa-shield-alt mr-1 text-muted"></i> Sesi terenkripsi
                    </span>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit-login" id="btnSubmitLogin">
                    <span id="btnSubmitText">Masuk ke Dashboard</span>
                    <i class="fas fa-arrow-right" id="btnSubmitIcon"></i>
                </button>

                <!-- Footer & Security Verification -->
                <div class="form-bottom-info">
                    <div class="security-stamp">
                        <i class="fas fa-check-shield"></i>
                        <span>Dilindungi Enkripsi 256-bit SSL</span>
                    </div>
                    <p class="copyright-text mb-0">
                        &copy; {{ date('Y') }} {{ setting('company_name', config('app.name', 'Tracker Keuangan')) }}. Seluruh hak cipta dilindungi.
                    </p>
                </div>
            </form>

        </div>
    </div>

</div>

<!-- Scripts -->
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Password Visibility Toggle
        const toggleBtn = document.getElementById('btnTogglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn && passwordInput && toggleIcon) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                
                if (isPassword) {
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                    toggleBtn.setAttribute('title', 'Sembunyikan kata sandi');
                } else {
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                    toggleBtn.setAttribute('title', 'Tampilkan kata sandi');
                }
            });
        }

        // 2. Submit Button Loading Animation
        const loginForm = document.getElementById('authLoginForm');
        const submitBtn = document.getElementById('btnSubmitLogin');
        const submitText = document.getElementById('btnSubmitText');
        const submitIcon = document.getElementById('btnSubmitIcon');

        if (loginForm && submitBtn) {
            loginForm.addEventListener('submit', function (e) {
                const emailInput = document.getElementById('email');
                const passInput = document.getElementById('password');

                // Basic HTML validation check before activating spinner
                if (!emailInput.value.trim() || !passInput.value.trim()) {
                    return; // let native or custom validation highlight
                }

                submitBtn.disabled = true;
                if (submitText) submitText.textContent = 'Memverifikasi...';
                if (submitIcon) {
                    submitIcon.className = 'fas fa-spinner spin-icon';
                }

                // If submission doesn't complete within 10s (e.g. cancelled), re-enable
                setTimeout(function () {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        if (submitText) submitText.textContent = 'Masuk ke Dashboard';
                        if (submitIcon) submitIcon.className = 'fas fa-arrow-right';
                    }
                }, 10000);
            });
        }

        // 3. SweetAlert2 Notifications Trigger
        function showSweetAlerts() {
            const holder = document.getElementById('sweet-alerts');
            if (!holder || typeof Swal === 'undefined') return;

            let alerts = [];
            try { 
                alerts = JSON.parse(holder.dataset.alerts || '[]'); 
            } catch (e) { 
                return; 
            }

            alerts.forEach(function (alert) {
                const isList = Array.isArray(alert.list) && alert.list.length > 0;
                Swal.fire({
                    icon: alert.type || 'info',
                    title: alert.title || 'Pemberitahuan',
                    html: isList
                        ? '<p class="mb-2 text-muted">' + alert.text + '</p><ul class="text-left pl-4 mb-0" style="font-size: 0.9rem;">' +
                          alert.list.map(function (msg) { return '<li>' + msg + '</li>'; }).join('') + '</ul>'
                        : '<span style="font-size: 0.95rem;">' + (alert.text || '') + '</span>',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: alert.type === 'error' ? '#2563EB' : '#10B981',
                    customClass: {
                        popup: 'rounded-lg shadow-lg'
                    }
                });
            });
        }

        showSweetAlerts();
    });
</script>
</body>
</html>