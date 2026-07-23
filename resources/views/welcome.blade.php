<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teman Belajar Udinus — Temukan Teman Belajar yang Cocok</title>
    @include('layouts.partials.favicon')
    <meta name="description" content="Platform rekomendasi teman belajar untuk mahasiswa UDINUS berbasis Content-Based Filtering. Temukan partner belajar yang sesuai dengan minat, tujuan, gaya belajar, jadwal, dan mode belajar Anda.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --tb-primary: #0b255b;
            --tb-primary-dark: #071940;
            --tb-primary-light: #e6ebf5;
            --tb-primary-soft: #f4f6fb;
            --tb-accent: #ffa73a;
            --tb-accent-dark: #e88f1e;
            --tb-accent-light: #fff4e3;
            --tb-ink: #1a2b3c;
            --tb-muted: #5a6b7d;
            --tb-nav-h: 64px;
        }

        * { -webkit-font-smoothing: antialiased; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--tb-ink);
            background: #fff;
            margin: 0;
        }
        a { color: var(--tb-primary); }

        /* ===== UTILITIES LOKAL ===== */
        .text-tb-primary { color: var(--tb-primary) !important; }
        .text-tb-accent { color: var(--tb-accent) !important; }
        .text-tb-ink { color: var(--tb-ink) !important; }
        .text-tb-muted { color: var(--tb-muted) !important; }
        .text-tb-primary-dark { color: var(--tb-primary-dark) !important; }
        .bg-tb-primary { background-color: var(--tb-primary) !important; }
        .bg-tb-primary-soft { background-color: var(--tb-primary-soft) !important; }
        .bg-tb-primary-light { background-color: var(--tb-primary-light) !important; }

        /* ===== TOMBOL ===== */
        .btn-nav {
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 600; border-radius: 0.625rem;
            transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
            text-decoration: none; white-space: nowrap;
        }
        .btn-nav svg { width: 1rem; height: 1rem; }
        .btn-nav-ghost { border: 1.5px solid var(--tb-primary); color: var(--tb-primary); padding: 0.45rem 1rem; }
        .btn-nav-ghost:hover { background: var(--tb-primary); color: #fff; }
        .btn-nav-solid { background: var(--tb-primary); color: #fff; padding: 0.45rem 1.1rem; }
        .btn-nav-solid:hover { background: var(--tb-primary-dark); transform: translateY(-1px); }
        .btn-nav-accent { background: var(--tb-accent); color: var(--tb-primary-dark); padding: 0.45rem 1.1rem; font-weight: 700; }
        .btn-nav-accent:hover { background: var(--tb-accent-dark); color: #fff; }

        .btn-hero-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            background: var(--tb-accent); color: var(--tb-primary-dark);
            font-weight: 700; padding: 0.85rem 1.75rem; border-radius: 0.75rem;
            font-size: 1.02rem; box-shadow: 0 12px 28px rgba(255,167,58,0.35);
            transition: all 0.25s cubic-bezier(0.4,0,0.2,1); text-decoration: none;
        }
        .btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 16px 36px rgba(255,167,58,0.45); }
        .btn-hero-primary svg { width: 1.2rem; height: 1.2rem; }
        .btn-hero-outline {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            border: 1.5px solid rgba(255,255,255,0.35); color: #fff;
            font-weight: 600; padding: 0.85rem 1.6rem; border-radius: 0.75rem;
            font-size: 1.02rem; transition: all 0.25s; text-decoration: none;
            background: rgba(255,255,255,0.06); backdrop-filter: blur(4px);
        }
        .btn-hero-outline:hover { background: rgba(255,255,255,0.14); border-color: rgba(255,255,255,0.6); }
        .btn-hero-outline svg { width: 1.2rem; height: 1.2rem; }

        /* ===== NAVBAR (fixed) ===== */
        .tb-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 50;
            height: var(--tb-nav-h);
            display: flex; align-items: center;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(11,37,91,0.08);
            transition: all 0.3s ease;
        }
        .tb-nav.scrolled { box-shadow: 0 6px 30px rgba(11,37,91,0.10); background: rgba(255,255,255,0.97); }
        .tb-nav-inner { display: flex; align-items: center; width: 100%; padding: 0 1.25rem; }
        .tb-nav-brand { display: flex; align-items: center; text-decoration: none; flex-shrink: 0; }
        .tb-brand-text { font-weight: 800; letter-spacing: -0.02em; font-size: 1.3rem; line-height: 1; display: inline-flex; align-items: center; }
        .tb-nav img { vertical-align: middle; }
        .tb-nav-menu { display: flex; align-items: center; gap: 0.25rem; margin: 0 auto; }
        .tb-nav-link {
            font-weight: 500; color: var(--tb-ink); text-decoration: none;
            padding: 0.5rem 1rem; border-radius: 0.5rem; transition: all 0.2s;
            font-size: 0.95rem; cursor: pointer; background: none; border: none; font-family: inherit;
        }
        .tb-nav-link:hover { color: var(--tb-primary); background: var(--tb-primary-soft); }
        .tb-nav-link.active { color: #fff; background: var(--tb-primary); font-weight: 600; }
        @media (max-width: 639px) {
            .tb-nav-link { padding: 0.4rem 0.5rem; font-size: 0.82rem; }
            .tb-nav-menu { gap: 0 !important; }
            .tb-brand-text { font-size: 1.05rem; }
            .btn-nav { padding-top: 0.35rem; padding-bottom: 0.35rem; font-size: 0.8rem !important; }
        }
        .tb-nav-actions { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
        .tb-nav button[aria-label="Toggle navigation"] svg { width: 1.75rem; height: 1.75rem; color: var(--tb-primary); }

        /* ===== SECTION FULL-PAGE ===== */
        .tb-section-full {
            min-height: 100vh;
            padding-top: var(--tb-nav-h);
            display: flex; flex-direction: column; justify-content: center;
            scroll-margin-top: 0;
        }
        .tb-section-inner { width: 100%; }

        /* ===== HERO ===== */
        .tb-hero {
            position: relative; overflow: hidden;
            background: linear-gradient(135deg, var(--tb-primary-dark) 0%, var(--tb-primary) 55%, #14306e 100%);
            color: #fff;
        }
        .tb-hero::before {
            content: ""; position: absolute; top: -30%; right: -10%;
            width: 600px; height: 600px; border-radius: 50%;
            background: radial-gradient(circle, rgba(255,167,58,0.18) 0%, transparent 65%);
            pointer-events: none;
        }
        .tb-hero::after {
            content: ""; position: absolute; bottom: -40%; left: -15%;
            width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 65%);
            pointer-events: none;
        }
        .tb-hero-grid { position: relative; z-index: 2; display: grid; grid-template-columns: 1fr; gap: 2.5rem; align-items: center; }
        @media (min-width: 1024px) { .tb-hero-grid { grid-template-columns: 1.05fr 0.95fr; } }
        .tb-pill {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.18);
            color: #fff; padding: 0.45rem 1rem; border-radius: 999px;
            font-size: 0.82rem; font-weight: 600; backdrop-filter: blur(6px);
        }
        .tb-pill svg { width: 1.05rem; height: 1.05rem; color: var(--tb-accent); }
        .tb-hero-title { font-size: clamp(2rem, 4.5vw, 3.4rem); font-weight: 800; line-height: 1.08; letter-spacing: -0.03em; color: #fff; }
        .tb-hero-title .tb-grad {
            background: linear-gradient(120deg, var(--tb-accent) 0%, #ffd08a 100%);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
        }
        .tb-hero-sub { font-size: 1.08rem; color: rgba(255,255,255,0.78); line-height: 1.65; max-width: 540px; }
        .tb-hero-checks { display: flex; flex-wrap: wrap; gap: 1.2rem; }
        .tb-hero-checks span { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; color: rgba(255,255,255,0.82); }
        .tb-hero-checks svg { width: 1.15rem; height: 1.15rem; color: #4ade80; }

        .tb-mockup {
            background: #fff; border-radius: 1.25rem; overflow: hidden;
            box-shadow: 0 30px 70px rgba(0,0,0,0.35); color: var(--tb-ink);
            transform: perspective(1400px) rotateY(-4deg) rotateX(2deg);
        }
        .tb-mockup-head { display: flex; align-items: center; gap: 0.6rem; padding: 1rem 1.25rem; border-bottom: 1px solid #eef2f7; background: var(--tb-primary-soft); }
        .tb-mockup-head svg { width: 1.2rem; height: 1.2rem; color: var(--tb-accent); }
        .tb-mockup-head strong { font-size: 0.92rem; color: var(--tb-ink); }
        .tb-mockup-body { padding: 0.75rem 1.25rem 1.1rem; }
        .tb-rec-row { display: flex; align-items: center; justify-content: space-between; padding: 0.7rem 0; border-bottom: 1px solid #f1f4f8; }
        .tb-rec-row:last-child { border-bottom: none; }
        .tb-avatar { width: 42px; height: 42px; border-radius: 0.7rem; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.05rem; color: #fff; flex-shrink: 0; }
        .tb-avatar-1 { background: linear-gradient(135deg,#3b5bdb,#5c7cfa); }
        .tb-avatar-2 { background: linear-gradient(135deg,#e8590c,#ffa73a); }
        .tb-avatar-3 { background: linear-gradient(135deg,#0ca678,#20c997); }
        .tb-score-bar { width: 130px; height: 6px; background: #eef2f7; border-radius: 999px; margin-top: 0.3rem; overflow: hidden; }
        .tb-score-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg,var(--tb-accent),#ffd08a); }
        .skor-badge { font-weight: 700; }

        /* ===== STATS BAND ===== */
        .tb-stats { background: var(--tb-primary); padding: 2.5rem 0; position: relative; }
        .tb-stats::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--tb-accent), transparent 70%); }
        .tb-stat-num { font-size: clamp(1.8rem, 4vw, 2.6rem); font-weight: 800; color: var(--tb-accent); line-height: 1; letter-spacing: -0.02em; }
        .tb-stat-label { color: rgba(255,255,255,0.72); font-size: 0.85rem; margin-top: 0.4rem; font-weight: 500; }

        /* ===== SECTION GENERIC ===== */
        .tb-eyebrow {
            display: inline-block; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.08em;
            text-transform: uppercase; color: var(--tb-accent-dark); background: var(--tb-accent-light);
            padding: 0.3rem 0.8rem; border-radius: 999px; margin-bottom: 0.85rem;
        }
        .tb-section-title { font-size: clamp(1.7rem, 3.5vw, 2.4rem); font-weight: 800; letter-spacing: -0.02em; color: var(--tb-ink); line-height: 1.15; }
        .tb-section-sub { color: var(--tb-muted); font-size: 1.05rem; line-height: 1.6; }

        .tb-step { background: #fff; border: 1px solid #eef2f7; border-radius: 1.1rem; padding: 2rem 1.5rem; text-align: center; height: 100%; transition: all 0.3s ease; }
        .tb-step:hover { transform: translateY(-6px); box-shadow: 0 20px 50px rgba(11,37,91,0.12); border-color: rgba(11,37,91,0.15); }
        .tb-step-num { width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, var(--tb-primary), #1a3a7a); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; margin-bottom: 1rem; box-shadow: 0 10px 24px rgba(11,37,91,0.28); }
        .tb-step-icon { color: var(--tb-accent); margin-bottom: 0.6rem; display: block; }
        .tb-step-icon svg { width: 1.8rem; height: 1.8rem; }

        .tb-feature { background: #fff; border: 1px solid #eef2f7; border-radius: 0.9rem; padding: 1.1rem 1.15rem; height: 100%; position: relative; overflow: hidden; transition: all 0.3s ease; }
        .tb-feature::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: var(--feat-color, var(--tb-primary)); transform: scaleX(0); transform-origin: left; transition: transform 0.35s ease; }
        .tb-feature:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(11,37,91,0.10); border-color: var(--feat-color, var(--tb-primary)); }
        .tb-feature:hover::before { transform: scaleX(1); }
        .tb-feature-icon { width: 38px; height: 38px; border-radius: 0.6rem; background: var(--feat-soft, var(--tb-primary-soft)); color: var(--feat-color, var(--tb-primary)); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.6rem; transition: all 0.3s ease; }
        .tb-feature-icon svg { width: 1.2rem; height: 1.2rem; }
        .tb-feature:hover .tb-feature-icon { transform: scale(1.1); }
        .tb-feature h5 { font-weight: 700; font-size: 0.98rem; color: var(--tb-ink); margin-bottom: 0.3rem; line-height: 1.2; }
        .tb-feature-badge { display: inline-block; background: var(--feat-soft, var(--tb-accent-light)); color: var(--feat-color, var(--tb-accent-dark)); font-size: 0.65rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 0.3rem; margin-bottom: 0.45rem; letter-spacing: 0.02em; }
        .tb-feature p { color: var(--tb-muted); font-size: 0.83rem; line-height: 1.5; margin: 0; }

        /* Variasi warna per card */
        .tb-feature.f-navy    { --feat-color: #0b255b; --feat-soft: #e6ebf5; }
        .tb-feature.f-orange  { --feat-color: #e88f1e; --feat-soft: #fff4e3; }
        .tb-feature.f-green   { --feat-color: #0ca678; --feat-soft: #e6fcf5; }
        .tb-feature.f-purple  { --feat-color: #7048e8; --feat-soft: #f3f0ff; }
        .tb-feature.f-blue    { --feat-color: #1c7ed6; --feat-soft: #e7f5ff; }
        .tb-feature.f-red     { --feat-color: #e03131; --feat-soft: #fff5f5; }

        .tb-audience-card { background: #fff; border: 1px solid #eef2f7; border-radius: 1rem; padding: 1.5rem 1rem; text-align: center; transition: all 0.25s ease; }
        .tb-audience-card:hover { border-color: var(--tb-accent); transform: translateY(-3px); box-shadow: 0 12px 30px rgba(255,167,58,0.14); }
        .tb-audience-icon { width: 52px; height: 52px; border-radius: 0.9rem; background: linear-gradient(135deg, var(--tb-primary-soft), var(--tb-primary-light)); color: var(--tb-primary); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.8rem; }
        .tb-audience-icon svg { width: 1.5rem; height: 1.5rem; }
        .tb-audience-card .fak { font-weight: 700; color: var(--tb-ink); font-size: 0.95rem; }
        .tb-audience-card small { color: var(--tb-accent-dark); font-weight: 700; letter-spacing: 0.05em; }

        .tb-cta { background: linear-gradient(135deg, var(--tb-primary-dark), var(--tb-primary) 60%, #14306e); color: #fff; border-radius: 1.75rem; padding: 3.5rem 2rem; text-align: center; position: relative; overflow: hidden; }
        .tb-cta::before { content: ""; position: absolute; top: -40%; right: -5%; width: 320px; height: 320px; border-radius: 50%; background: radial-gradient(circle, rgba(255,167,58,0.22) 0%, transparent 65%); }
        .tb-cta-title { font-size: clamp(1.7rem, 3.5vw, 2.4rem); font-weight: 800; letter-spacing: -0.02em; }

        .tb-footer { background: var(--tb-primary-dark); color: rgba(255,255,255,0.62); padding: 3rem 0 0; position: relative; }
        .tb-footer::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--tb-accent), rgba(255,167,58,0) 60%); }
        .tb-footer-brand { font-weight: 800; font-size: 1.15rem; color: #fff; letter-spacing: -0.02em; }
        .tb-footer-logo { background: #fff; border-radius: 0.5rem; padding: 0.3rem; display: inline-flex; align-items: center; justify-content: center; line-height: 0; }
        .tb-footer h6 { font-size: 0.78rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--tb-accent); margin-bottom: 0.85rem; }
        .tb-footer a { color: rgba(255,255,255,0.62); text-decoration: none; font-size: 0.9rem; transition: color 0.2s; display: inline-flex; align-items: center; gap: 0.45rem; }
        .tb-footer a:hover { color: #fff; }
        .tb-footer a svg { width: 0.95rem; height: 0.95rem; }
        .tb-footer-info { display: flex; align-items: flex-start; gap: 0.6rem; font-size: 0.88rem; color: rgba(255,255,255,0.6); margin-bottom: 0.6rem; }
        .tb-footer-info svg { width: 1rem; height: 1rem; color: var(--tb-accent); flex-shrink: 0; margin-top: 0.15rem; }
        .tb-footer-bottom { border-top: 1px solid rgba(255,255,255,0.1); padding: 1.1rem 0; margin-top: 2rem; font-size: 0.82rem; }

        @keyframes tb-fade-up { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
        .tb-animate { animation: tb-fade-up 0.7s ease-out both; }
        .tb-animate-2 { animation-delay: 0.12s; }
        .tb-animate-3 { animation-delay: 0.24s; }
        .tb-animate-4 { animation-delay: 0.36s; }
        @media (prefers-reduced-motion: reduce) { .tb-animate { animation: none; } }
    </style>
</head>
<body>

    <!-- ===== NAVBAR ===== -->
    <nav class="tb-nav" id="tbNav">
        <div class="tb-nav-inner">
            <!-- logo + TBU mentok kiri -->
            <a class="tb-nav-brand" href="#beranda">
                <img src="{{ asset('img/logo.png') }}" alt="Teman Belajar Udinus" class="me-2" width="36" height="36">
                <span class="tb-brand-text"><span class="text-tb-primary">Teman Belajar</span>&nbsp;<span class="text-tb-accent">Udinus</span></span>
            </a>

            <!-- menu tengah -->
            <div class="tb-nav-menu flex">
                <a class="tb-nav-link" href="#cara-kerja">Cara Kerja</a>
                <a class="tb-nav-link" href="#fitur">Fitur</a>
                <a class="tb-nav-link" href="#untuk-siapa">Untuk Siapa</a>
            </div>

            <!-- tombol kanan mentok -->
            <div class="tb-nav-actions ms-auto sm:ms-0">
                @guest
                    <a href="{{ route('login') }}" class="btn-nav btn-nav-ghost text-sm hidden sm:inline-flex">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-nav btn-nav-solid text-sm">Daftar</a>
                @else
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn-nav btn-nav-accent text-sm">
                            <x-icon name="speedometer2" class="me-1" />
                            <span class="hidden sm:inline">Dashboard Admin</span>
                            <span class="sm:hidden">Admin</span>
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn-nav btn-nav-accent text-sm">
                            <x-icon name="house" class="me-1" />
                            <span class="hidden sm:inline">Dashboard</span>
                            <span class="sm:hidden">Dashboard</span>
                        </a>
                    @endif
                @endguest
            </div>
        </div>
    </nav>

    <!-- ===== HERO (1 halaman penuh) ===== -->
    <section class="tb-section-full tb-hero" id="beranda">
        <div class="mx-auto w-full max-w-7xl px-4 py-10">
            <div class="tb-hero-grid">
                <div class="tb-hero-content">
                    <span class="tb-pill tb-animate">
                        <x-icon name="mortarboard-fill" />
                        Platform Resmi untuk Mahasiswa UDINUS
                    </span>
                    <h1 class="tb-hero-title mt-4 tb-animate tb-animate-2">
                        Temukan <span class="tb-grad">Teman Belajar</span> yang Benar-benar Cocok
                    </h1>
                    <p class="tb-hero-sub mt-4 tb-animate tb-animate-3">
                        Berhenti mencari teman belajar secara acak. Isi profil preferensi Anda sekali, dan biarkan sistem mencocokkan Anda dengan mahasiswa UDINUS lain yang punya minat, tujuan, gaya belajar, jadwal, dan mode belajar selaras.
                    </p>
                    <div class="flex flex-wrap gap-3 mt-6 tb-animate tb-animate-4">
                        @guest
                            <a href="{{ route('register') }}" class="btn-hero-primary">
                                <x-icon name="person-plus" /> Mulai Gratis Sekarang
                            </a>
                            <a href="#cara-kerja" class="btn-hero-outline">
                                <x-icon name="play-circle" /> Lihat Cara Kerja
                            </a>
                        @else
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="btn-hero-primary">
                                    <x-icon name="speedometer2" /> Buka Dashboard Admin
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="btn-hero-primary">
                                    <x-icon name="house" /> Ke Dashboard Saya
                                </a>
                            @endif
                        @endguest
                    </div>
                    <div class="tb-hero-checks mt-6 tb-animate tb-animate-4">
                        <span><x-icon name="check-circle-fill" /> Gratis selamanya</span>
                        <span><x-icon name="check-circle-fill" /> Lintas program studi</span>
                        <span><x-icon name="check-circle-fill" /> Cukup email UDINUS</span>
                    </div>
                </div>

                <div class="tb-animate tb-animate-3">
                    <div class="tb-mockup">
                        <div class="tb-mockup-head">
                            <x-icon name="stars" />
                            <strong>Rekomendasi untuk Anda</strong>
                            <span class="ms-auto inline-flex items-center rounded-full bg-tb-primary-soft text-tb-primary px-2 py-0.5 text-xs font-semibold">Top 10</span>
                        </div>
                        <div class="tb-mockup-body">
                            <div class="tb-rec-row">
                                <div class="flex items-center">
                                    <span class="tb-avatar tb-avatar-1 me-3">A</span>
                                    <div>
                                        <div class="font-semibold" style="font-size:0.95rem;">Andi Pratama</div>
                                        <small class="text-tb-muted" style="font-size:0.78rem;">Teknik Informatika · Hybrid · Senin Pagi</small>
                                        <div class="tb-score-bar"><div class="tb-score-fill" style="width:92%;"></div></div>
                                    </div>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-green-100 text-green-700 px-2.5 py-1 skor-badge" style="font-size:0.82rem;">92%</span>
                            </div>
                            <div class="tb-rec-row">
                                <div class="flex items-center">
                                    <span class="tb-avatar tb-avatar-2 me-3">R</span>
                                    <div>
                                        <div class="font-semibold" style="font-size:0.95rem;">Rina Maharani</div>
                                        <small class="text-tb-muted" style="font-size:0.78rem;">Sistem Informasi · Hybrid · Rabu Sore</small>
                                        <div class="tb-score-bar"><div class="tb-score-fill" style="width:85%;"></div></div>
                                    </div>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-green-100 text-green-700 px-2.5 py-1 skor-badge" style="font-size:0.82rem;">85%</span>
                            </div>
                            <div class="tb-rec-row">
                                <div class="flex items-center">
                                    <span class="tb-avatar tb-avatar-3 me-3">B</span>
                                    <div>
                                        <div class="font-semibold" style="font-size:0.95rem;">Budi Santoso</div>
                                        <small class="text-tb-muted" style="font-size:0.78rem;">Ilmu Komunikasi · Daring · Jumat Malam</small>
                                        <div class="tb-score-bar"><div class="tb-score-fill" style="width:78%;"></div></div>
                                    </div>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-tb-primary-light text-tb-primary px-2.5 py-1 skor-badge" style="font-size:0.82rem;">78%</span>
                            </div>
                            <div class="text-center mt-3 pt-3" style="border-top:1px solid #f1f4f8;">
                                <small class="text-tb-muted inline-flex items-center justify-center gap-1"><x-icon name="info-circle" /> Skor dihitung dengan Cosine Similarity</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== STATS BAND ===== -->
    <section class="tb-stats">
        <div class="mx-auto w-full max-w-7xl px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div>
                    <div class="tb-stat-num">6</div>
                    <div class="tb-stat-label">Fakultas UDINUS</div>
                </div>
                <div>
                    <div class="tb-stat-num">21</div>
                    <div class="tb-stat-label">Program Studi</div>
                </div>
                <div>
                    <div class="tb-stat-num">5</div>
                    <div class="tb-stat-label">Atribut Preferensi</div>
                </div>
                <div>
                    <div class="tb-stat-num">Top 10</div>
                    <div class="tb-stat-label">Rekomendasi Akurat</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== CARA KERJA (1 halaman penuh) ===== -->
    <section class="tb-section-full" id="cara-kerja" style="background:var(--tb-primary-soft);">
        <div class="mx-auto w-full max-w-7xl px-4 py-12">
            <div class="text-center mb-10">
                <span class="tb-eyebrow">Proses Sederhana</span>
                <h2 class="tb-section-title">Tiga Langkah Temukan Teman Belajar</h2>
                <p class="tb-section-sub mt-3 mx-auto" style="max-width:600px;">
                    Tidak perlu pengalaman teknis. Cukup isi profil, lihat rekomendasi, dan kirim permintaan — semuanya dalam satu platform.
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="tb-step">
                    <div class="tb-step-num">1</div>
                    <x-icon name="pencil-square" class="tb-step-icon" />
                    <h5 class="font-bold mb-2" style="font-size:1.1rem;">Isi Profil Preferensi</h5>
                    <p class="text-tb-muted mb-0" style="font-size:0.92rem;">Pilih lima atribut: minat bidang, tujuan belajar, gaya belajar, jadwal luang, dan mode belajar Anda. Hanya butuh 2 menit.</p>
                </div>
                <div class="tb-step">
                    <div class="tb-step-num">2</div>
                    <x-icon name="stars" class="tb-step-icon" />
                    <h5 class="font-bold mb-2" style="font-size:1.1rem;">Lihat Rekomendasi</h5>
                    <p class="text-tb-muted mb-0" style="font-size:0.92rem;">Sistem mencocokkan profil Anda dengan mahasiswa lain menggunakan <strong>Content-Based Filtering</strong> dan <strong>Cosine Similarity</strong>.</p>
                </div>
                <div class="tb-step">
                    <div class="tb-step-num">3</div>
                    <x-icon name="send-check" class="tb-step-icon" />
                    <h5 class="font-bold mb-2" style="font-size:1.1rem;">Kirim Permintaan</h5>
                    <p class="text-tb-muted mb-0" style="font-size:0.92rem;">Pilih kandidat yang cocok, kirim permintaan belajar. Setelah diterima, kontak WhatsApp &amp; Instagram otomatis terbuka.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FITUR (1 halaman penuh) ===== -->
    <section class="tb-section-full" id="fitur" style="background:#fff;">
        <div class="mx-auto w-full max-w-7xl px-4 py-8">
            <div class="text-center mb-6">
                <span class="tb-eyebrow">Keunggulan Platform</span>
                <h2 class="tb-section-title">Kenapa Teman Belajar Udinus?</h2>
                <p class="tb-section-sub mt-2 mx-auto" style="max-width:600px;">
                    Dirancang khusus untuk mahasiswa UDINUS dengan algoritma rekomendasi yang dapat dijelaskan dan transparan.
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="tb-feature f-navy">
                    <div class="tb-feature-icon"><x-icon name="cpu" /></div>
                    <h5>Content-Based Filtering</h5>
                    <span class="tb-feature-badge">5 Atribut Preferensi</span>
                    <p>Algoritma mencocokkan profil Anda dengan mahasiswa lain berdasarkan lima atribut preferensi menggunakan One-Hot Encoding — bukan tebakan acak.</p>
                </div>
                <div class="tb-feature f-orange">
                    <div class="tb-feature-icon"><x-icon name="graph-up-arrow" /></div>
                    <h5>Cosine Similarity</h5>
                    <span class="tb-feature-badge">Skor 0–100%</span>
                    <p>Skor kecocokan dihitung matematis dengan rumus cosinus sudut antar vektor preferensi. Semakin tinggi persen, semakin mirip profilnya.</p>
                </div>
                <div class="tb-feature f-green">
                    <div class="tb-feature-icon"><x-icon name="funnel" /></div>
                    <h5>Filter Prodi &amp; Fakultas</h5>
                    <span class="tb-feature-badge">Post-Top-N</span>
                    <p>Ingin seprodi atau lintas fakultas? Filter fleksibel diterapkan setelah rekomendasi dihitung — tidak mengganggu akurasi algoritma.</p>
                </div>
                <div class="tb-feature f-purple">
                    <div class="tb-feature-icon"><x-icon name="people" /></div>
                    <h5>Kolaborasi Lintas Disiplin</h5>
                    <span class="tb-feature-badge">6 Fakultas</span>
                    <p>Jangan terjebak di lingkaran pertemanan lama. Temukan partner belajar dari program studi lain untuk perspektif baru.</p>
                </div>
                <div class="tb-feature f-blue">
                    <div class="tb-feature-icon"><x-icon name="shield-check" /></div>
                    <h5>Kontak Terlindungi</h5>
                    <span class="tb-feature-badge">WA &amp; IG Aman</span>
                    <p>WhatsApp &amp; Instagram Anda hanya terlihat setelah permintaan belajar diterima. Tidak ada spam, tidak ada gangguan.</p>
                </div>
                <div class="tb-feature f-red">
                    <div class="tb-feature-icon"><x-icon name="phone" /></div>
                    <h5>Responsif &amp; Aksesibel</h5>
                    <span class="tb-feature-badge">Tanpa Instal</span>
                    <p>Buka di browser apa saja — laptop, tablet, atau HP. Tidak perlu instal aplikasi. Cukup login dengan email UDINUS Anda.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== UNTUK SIAPA (1 halaman penuh) ===== -->
    <section class="tb-section-full" id="untuk-siapa" style="background:var(--tb-primary-soft);">
        <div class="mx-auto w-full max-w-7xl px-4 py-12">
            <div class="text-center mb-10">
                <span class="tb-eyebrow">Komunitas Kampus</span>
                <h2 class="tb-section-title">Untuk Seluruh Mahasiswa UDINUS</h2>
                <p class="tb-section-sub mt-3 mx-auto" style="max-width:600px;">
                    Dari Teknik Informatika hingga Sastra Jepang — semua mahasiswa UDINUS bisa menemukan partner belajar ideal di sini.
                </p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 max-w-3xl mx-auto">
                <div class="tb-audience-card">
                    <div class="tb-audience-icon"><x-icon name="code-slash" /></div>
                    <div class="fak">Ilmu Komputer</div>
                    <small>FIK</small>
                </div>
                <div class="tb-audience-card">
                    <div class="tb-audience-icon"><x-icon name="briefcase" /></div>
                    <div class="fak">Ekonomi &amp; Bisnis</div>
                    <small>FEB</small>
                </div>
                <div class="tb-audience-card">
                    <div class="tb-audience-icon"><x-icon name="translate" /></div>
                    <div class="fak">Ilmu Budaya</div>
                    <small>FIB</small>
                </div>
                <div class="tb-audience-card">
                    <div class="tb-audience-icon"><x-icon name="heart-pulse" /></div>
                    <div class="fak">Kesehatan</div>
                    <small>FKES</small>
                </div>
                <div class="tb-audience-card">
                    <div class="tb-audience-icon"><x-icon name="gear-wide-connected" /></div>
                    <div class="fak">Teknik</div>
                    <small>FT</small>
                </div>
                <div class="tb-audience-card">
                    <div class="tb-audience-icon"><x-icon name="clipboard2-pulse" /></div>
                    <div class="fak">Kedokteran</div>
                    <small>FK</small>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== CTA (1 halaman penuh) ===== -->
    <section class="tb-section-full" style="background:#fff;">
        <div class="mx-auto w-full max-w-7xl px-4 py-12">
            <div class="tb-cta">
                <div style="position:relative;z-index:2;">
                    <h2 class="tb-cta-title">Siap Menemukan Partner Belajar Ideal?</h2>
                    <p class="mb-6 mt-3" style="opacity:0.88;font-size:1.1rem;">
                        Gabung sekarang — gratis, cukup gunakan email mahasiswa UDINUS Anda.
                    </p>
                    <div class="flex flex-wrap justify-center gap-3">
                        @guest
                            <a href="{{ route('register') }}" class="btn-hero-primary">
                                <x-icon name="person-plus" /> Daftar Sekarang
                            </a>
                            <a href="{{ route('login') }}" class="btn-hero-outline">
                                <x-icon name="box-arrow-in-right" /> Sudah Punya Akun?
                            </a>
                        @else
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="btn-hero-primary">
                                    <x-icon name="speedometer2" /> Buka Dashboard
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="btn-hero-primary">
                                    <x-icon name="house" /> Ke Dashboard Saya
                                </a>
                            @endif
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer class="tb-footer">
        <div class="mx-auto w-full max-w-7xl px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pb-4">

                <!-- Kolom 1: Brand + deskripsi -->
                <div>
                    <div class="flex items-center mb-3">
                        <span class="tb-footer-logo">
                            <img src="{{ asset('img/logo.png') }}" alt="Teman Belajar Udinus" width="34" height="34">
                        </span>
                        <span class="tb-footer-brand ms-2">Teman Belajar Udinus</span>
                    </div>
                    <p style="font-size:0.88rem;line-height:1.6;margin:0;max-width:280px;">
                        Platform rekomendasi teman belajar untuk mahasiswa UDINUS, berbasis Content-Based Filtering &amp; Cosine Similarity.
                    </p>
                </div>

                <!-- Kolom 2: Info / kontak -->
                <div>
                    <h6>Informasi</h6>
                    <div class="tb-footer-info">
                        <x-icon name="mortarboard-fill" />
                        <span>Universitas Dian Nuswantoro (UDINUS), Semarang</span>
                    </div>
                    <div class="tb-footer-info">
                        <x-icon name="info-circle" />
                        <span>Hanya untuk mahasiswa aktif UDINUS (email @mhs.dinus.ac.id)</span>
                    </div>
                    <div class="tb-footer-info">
                        <x-icon name="shield-check" />
                        <span>Kontak WhatsApp &amp; Instagram terlindungi, dibuka setelah permintaan diterima</span>
                    </div>
                </div>

            </div>

            <div class="tb-footer-bottom flex flex-col md:flex-row items-center justify-between gap-2">
                <span>&copy; 2026 Teman Belajar Udinus</span>
                <span style="opacity:0.8;">Created by : Muhammad Rifki Kurniawan</span>
            </div>
        </div>
    </footer>

    <!-- ===== MICRO-INTERACTION SCRIPT ===== -->
    <script>
        // Navbar scroll effect
        (function() {
            const nav = document.getElementById('tbNav');
            if (!nav) return;
            const onScroll = () => {
                if (window.scrollY > 20) nav.classList.add('scrolled');
                else nav.classList.remove('scrolled');
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        })();

        // Active link highlight saat scroll
        (function() {
            const links = Array.from(document.querySelectorAll('.tb-nav-menu .tb-nav-link'));
            const sections = links.map(l => document.querySelector(l.getAttribute('href'))).filter(Boolean);
            if (!sections.length) return;
            const onScroll = () => {
                // posisi tengah viewport sebagai acuan
                const pos = window.scrollY + (window.innerHeight * 0.4);
                // cari section yang posisinya sudah dilewati; jika belum ada (masih di hero/beranda), tidak ada yang aktif
                let current = null;
                sections.forEach(s => { if (s.offsetTop <= pos) current = s; });
                links.forEach(l => {
                    l.classList.toggle('active', current && l.getAttribute('href') === '#' + current.id);
                });
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        })();

        // Smooth scroll untuk anchor link
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', function(e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>
