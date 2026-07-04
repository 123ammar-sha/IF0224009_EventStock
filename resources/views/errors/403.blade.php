<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>403 — Akses Ditolak | EventStock</title>
    <meta name="description" content="Anda tidak memiliki izin untuk mengakses halaman ini." />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --brand-violet: #7c3aed;
            --brand-indigo: #4f46e5;
            --danger-red: #ef4444;
            --danger-orange: #f97316;
            --dark-bg: #090d16;
            --dark-card: #0f172a;
            --dark-border: #1e293b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--dark-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* ===== Background animated glow ===== */
        .bg-glow {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }

        .glow-circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.12;
            animation: floatGlow 8s ease-in-out infinite alternate;
        }

        .glow-1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, #ef4444, #dc2626);
            top: -150px; left: -150px;
            animation-delay: 0s;
        }

        .glow-2 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, #7c3aed, #4f46e5);
            bottom: -100px; right: -100px;
            animation-delay: -3s;
        }

        .glow-3 {
            width: 300px; height: 300px;
            background: radial-gradient(circle, #f97316, #dc2626);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            animation-delay: -6s;
        }

        @keyframes floatGlow {
            0%   { transform: scale(1) translate(0, 0); }
            100% { transform: scale(1.15) translate(20px, -20px); }
        }

        /* ===== Grid dots pattern ===== */
        .grid-pattern {
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.035) 1px, transparent 1px);
            background-size: 32px 32px;
            z-index: 0;
        }

        /* ===== Main card ===== */
        .card-wrapper {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 20px;
            max-width: 560px;
            width: 100%;
        }

        /* Icon container */
        .icon-ring {
            position: relative;
            width: 130px;
            height: 130px;
            margin-bottom: 32px;
        }

        .icon-ring::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 1px solid rgba(239, 68, 68, 0.25);
            animation: ringPulse 2.5s ease-in-out infinite;
        }

        .icon-ring::after {
            content: '';
            position: absolute;
            inset: -18px;
            border-radius: 50%;
            border: 1px solid rgba(239, 68, 68, 0.1);
            animation: ringPulse 2.5s ease-in-out infinite 0.5s;
        }

        @keyframes ringPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(1.06); }
        }

        .icon-inner {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(239,68,68,0.15) 0%, rgba(220,38,38,0.08) 100%);
            border: 1px solid rgba(239, 68, 68, 0.3);
            backdrop-filter: blur(12px);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                0 0 0 1px rgba(239,68,68,0.1),
                0 20px 40px rgba(239,68,68,0.15),
                inset 0 1px 0 rgba(255,255,255,0.05);
        }

        .icon-inner i {
            font-size: 42px;
            color: #ef4444;
            filter: drop-shadow(0 0 16px rgba(239,68,68,0.5));
            animation: iconShake 4s ease-in-out infinite;
        }

        @keyframes iconShake {
            0%, 100% { transform: rotate(0deg); }
            10%       { transform: rotate(-8deg); }
            20%       { transform: rotate(8deg); }
            30%       { transform: rotate(-4deg); }
            40%       { transform: rotate(4deg); }
            50%       { transform: rotate(0deg); }
        }

        /* Error code */
        .error-code {
            font-size: 100px;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #ef4444 0%, #f97316 40%, #dc2626 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -4px;
            margin-bottom: 12px;
            filter: drop-shadow(0 0 30px rgba(239,68,68,0.3));
            animation: codePulse 3s ease-in-out infinite;
        }

        @keyframes codePulse {
            0%, 100% { filter: drop-shadow(0 0 30px rgba(239,68,68,0.3)); }
            50%       { filter: drop-shadow(0 0 50px rgba(239,68,68,0.5)); }
        }

        /* Badge */
        .badge-forbidden {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 9999px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #f87171;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 24px;
        }

        .badge-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #ef4444;
            animation: dotBlink 1.5s ease-in-out infinite;
        }

        @keyframes dotBlink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.2; }
        }

        /* Title */
        .title {
            font-size: 28px;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        /* Description */
        .description {
            font-size: 15px;
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 36px;
            max-width: 420px;
        }

        /* Divider */
        .divider {
            width: 100%;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(239,68,68,0.2), transparent);
            margin-bottom: 36px;
        }

        /* Info box */
        .info-box {
            width: 100%;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(30, 41, 59, 0.8);
            border-radius: 14px;
            padding: 18px 22px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            text-align: left;
            margin-bottom: 32px;
            backdrop-filter: blur(8px);
        }

        .info-box-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .info-box-icon i {
            font-size: 14px;
            color: #f87171;
        }

        .info-box-content p {
            font-size: 13px;
            color: #475569;
            line-height: 1.6;
        }

        .info-box-content p strong {
            color: #94a3b8;
            font-weight: 600;
        }

        /* Buttons */
        .btn-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
            filter: brightness(1.1);
        }

        .btn-secondary {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(51, 65, 85, 0.8);
            color: #94a3b8;
            backdrop-filter: blur(8px);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            border-color: rgba(99, 102, 241, 0.4);
            color: #c4b5fd;
            background: rgba(99, 102, 241, 0.08);
        }

        /* Footer note */
        .footer-note {
            margin-top: 40px;
            font-size: 12px;
            color: #334155;
        }

        .footer-note a {
            color: #6366f1;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-note a:hover {
            text-decoration: underline;
        }

        /* Floating particles */
        .particles {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.4);
            animation: particleFloat linear infinite;
        }

        @keyframes particleFloat {
            0%   { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translateY(-100px) rotate(360deg); opacity: 0; }
        }

        @media (max-width: 480px) {
            .error-code { font-size: 70px; }
            .title { font-size: 22px; }
            .btn-group { flex-direction: column; width: 100%; }
            .btn { justify-content: center; }
        }
    </style>
</head>
<body>

    <!-- Background glows -->
    <div class="bg-glow">
        <div class="glow-circle glow-1"></div>
        <div class="glow-circle glow-2"></div>
        <div class="glow-circle glow-3"></div>
    </div>

    <!-- Grid dots -->
    <div class="grid-pattern"></div>

    <!-- Floating particles -->
    <div class="particles" id="particles"></div>

    <!-- Main content -->
    <div class="card-wrapper">

        <!-- Icon -->
        <div class="icon-ring">
            <div class="icon-inner">
                <i class="fas fa-shield-halved"></i>
            </div>
        </div>

        <!-- Error code -->
        <div class="error-code">403</div>

        <!-- Badge -->
        <div class="badge-forbidden">
            <div class="badge-dot"></div>
            Akses Ditolak
        </div>

        <!-- Title -->
        <h1 class="title">Anda Tidak Memiliki Izin</h1>

        <!-- Description -->
        <p class="description">
            Halaman yang Anda coba akses membutuhkan izin khusus
            yang tidak dimiliki oleh akun Anda saat ini.
        </p>

        <div class="divider"></div>

        <!-- Info box -->
        <div class="info-box">
            <div class="info-box-icon">
                <i class="fas fa-circle-info"></i>
            </div>
            <div class="info-box-content">
                <p>
                    Jika Anda yakin seharusnya bisa mengakses halaman ini,
                    silakan hubungi <strong>administrator sistem</strong> untuk
                    mendapatkan hak akses yang sesuai dengan peran Anda.
                </p>
            </div>
        </div>

        <!-- Buttons -->
        <div class="btn-group">
            <a href="/dashboard" class="btn btn-primary" id="btn-dashboard">
                <i class="fas fa-gauge-high"></i>
                Kembali ke Dashboard
            </a>
            <a href="javascript:history.back()" class="btn btn-secondary" id="btn-back">
                <i class="fas fa-arrow-left"></i>
                Halaman Sebelumnya
            </a>
        </div>

        <!-- Footer note -->
        <p class="footer-note">
            EventStock &copy; {{ date('Y') }} &nbsp;·&nbsp;
            Butuh bantuan? Hubungi
            <a href="mailto:admin@eventstock.test">admin@eventstock.test</a>
        </p>

    </div>

    <script>
        // Generate floating particles
        const container = document.getElementById('particles');
        for (let i = 0; i < 18; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            const size = Math.random() * 3 + 1.5;
            p.style.cssText = `
                left: ${Math.random() * 100}%;
                width: ${size}px;
                height: ${size}px;
                animation-duration: ${Math.random() * 12 + 10}s;
                animation-delay: ${Math.random() * 12}s;
                opacity: ${Math.random() * 0.5 + 0.1};
                background: rgba(${Math.random() > 0.5 ? '239, 68, 68' : '124, 58, 237'}, ${Math.random() * 0.5 + 0.2});
            `;
            container.appendChild(p);
        }
    </script>
</body>
</html>
