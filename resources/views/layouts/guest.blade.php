<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Teman Belajar Udinus') }} @yield('title')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
        }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--tb-primary-soft); color: var(--tb-ink); margin: 0; }
        .text-primary { color: var(--tb-primary) !important; }
        a { color: var(--tb-primary); }

        .tb-auth-wrap { min-height: 100vh; display: flex; flex-direction: column; }

        /* Body — form centered, compact */
        .tb-auth-body {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1.25rem;
            position: relative;
            overflow: hidden;
        }
        .tb-auth-body::before {
            content: "";
            position: absolute;
            top: -60px; right: -60px;
            width: 280px; height: 280px;
            background: rgba(255, 167, 58, 0.08);
            border-radius: 50%;
            filter: blur(6px);
            pointer-events: none;
        }
        .tb-auth-body::after {
            content: "";
            position: absolute;
            bottom: -80px; left: -40px;
            width: 240px; height: 240px;
            background: rgba(11, 37, 91, 0.06);
            border-radius: 50%;
            filter: blur(6px);
            pointer-events: none;
        }

        .tb-auth-card {
            width: 100%;
            max-width: 440px;
            background: white;
            border-radius: 1.25rem;
            border: 1px solid var(--tb-primary-light);
            box-shadow: 0 20px 50px rgba(11, 37, 91, 0.08), 0 4px 12px rgba(0,0,0,0.03);
            padding: 2rem 2.25rem;
            position: relative;
            z-index: 2;
        }
        .tb-auth-card::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: var(--tb-accent);
            border-radius: 1.25rem 1.25rem 0 0;
        }
        @media (max-width: 575.98px) {
            .tb-auth-card { padding: 1.75rem 1.5rem; border-radius: 1rem; }
        }

        .tb-auth-logo-row {
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 0.5rem;
        }
        .tb-auth-logo-row img { width: 56px; height: 56px; }

        .tb-auth-title {
            font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em;
            color: var(--tb-ink); margin-bottom: 0.3rem; text-align: center;
        }
        .tb-auth-subtitle {
            color: var(--tb-muted); margin-bottom: 1.75rem; font-size: 0.88rem; text-align: center;
        }

        /* ===== FORM FIELD (shared login & register) ===== */
        .tb-field { margin-bottom: 0.8rem; }
        .tb-field:last-child { margin-bottom: 0; }
        .tb-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
        @media (max-width: 520px) { .tb-field-row { grid-template-columns: 1fr; } }

        .tb-form-label {
            display: block; font-weight: 600; font-size: 0.78rem;
            color: var(--tb-ink); margin-bottom: 0.3rem;
        }

        .tb-field-input { position: relative; display: flex; align-items: center; }
        .tb-field-input input,
        .tb-field > select {
            width: 100%; height: 44px;
            padding: 0 0.8rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.55rem;
            font-size: 0.88rem;
            background: #fbfcfe;
            color: var(--tb-ink);
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
            outline: none; appearance: none;
            font-family: inherit;
        }
        .tb-field-input input { padding-left: 2.5rem; padding-right: 2.5rem; }
        .tb-field > select { padding-left: 0.8rem; cursor: pointer; padding-right: 2rem; }
        .tb-field > select:disabled { opacity: 0.55; cursor: not-allowed; }
        .tb-field > select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%235a6b7d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.7rem center;
        }
        .tb-field-input input:focus,
        .tb-field > select:focus {
            border-color: var(--tb-primary);
            box-shadow: 0 0 0 3px rgba(11, 37, 91, 0.10);
            background: white;
        }

        .tb-field-icon {
            position: absolute; left: 0.8rem;
            top: 50%; transform: translateY(-50%);
            color: var(--tb-muted);
            z-index: 1; pointer-events: none;
            transition: color 0.18s ease;
        }
        .tb-field-icon svg { width: 1rem; height: 1rem; display: block; }
        .tb-field-input:focus-within .tb-field-icon { color: var(--tb-accent); }

        .tb-field-toggle {
            position: absolute; right: 0.7rem;
            top: 50%; transform: translateY(-50%);
            color: var(--tb-muted);
            cursor: pointer; z-index: 2;
            transition: color 0.18s ease;
            background: none; border: none; padding: 0;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .tb-field-toggle svg { width: 1rem; height: 1rem; display: block; }
        .tb-field-toggle:hover { color: var(--tb-primary); }
        /* Ikon "eye" (password terlihat) tersembunyi default; JS menampilkannya saat toggle aktif */
        .tb-toggle-hide { display: none; }

        .tb-field-input input.is-invalid,
        .tb-field > select.is-invalid {
            border-color: #e03131;
            background: #fff5f5;
        }
        .tb-field-input input.is-invalid:focus,
        .tb-field > select.is-invalid:focus {
            border-color: #e03131;
            box-shadow: 0 0 0 3px rgba(224, 49, 49, 0.10);
        }
        .tb-field-input:has(input.is-invalid) .tb-field-icon { color: #e03131; }
        .tb-field-error {
            font-size: 0.74rem; color: #e03131;
            margin-top: 0.25rem; font-weight: 500;
        }

        /* Radio pill (jenis kelamin) */
        .tb-radio-group { display: flex; gap: 0.55rem; }
        .tb-radio-group.is-invalid { outline: none; }
        .tb-radio-pill {
            flex: 1; position: relative; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center;
            border: 1.5px solid #e2e8f0; border-radius: 0.55rem;
            background: #fbfcfe; font-size: 0.85rem; font-weight: 600;
            color: var(--tb-muted); transition: all 0.18s ease;
            padding: 0.65rem 0.6rem; height: 44px;
        }
        .tb-radio-pill input { position: absolute; opacity: 0; pointer-events: none; }
        .tb-radio-pill span { display: inline-flex; align-items: center; gap: 0.35rem; }
        .tb-radio-pill span svg { width: 0.95rem; height: 0.95rem; }
        .tb-radio-pill:hover { border-color: var(--tb-primary); color: var(--tb-primary); }
        .tb-radio-pill:has(input:checked) {
            border-color: var(--tb-primary); background: var(--tb-primary);
            color: #fff; box-shadow: 0 0 0 3px rgba(11,37,91,0.10);
        }
        .tb-radio-group.is-invalid .tb-radio-pill { border-color: #e03131; }

        .tb-field-hint {
            display: flex; align-items: center; gap: 0.35rem;
            font-size: 0.72rem; color: var(--tb-muted);
            margin-top: 0.28rem; line-height: 1.4;
        }
        .tb-field-hint svg { width: 0.85rem; height: 0.85rem; color: var(--tb-accent-dark); flex-shrink: 0; }
        .tb-field-hint code {
            background: var(--tb-primary-soft); color: var(--tb-primary);
            padding: 0.05rem 0.35rem; border-radius: 0.3rem;
            font-size: 0.7rem; font-weight: 600;
        }

        /* Submit button (shared) */
        .tb-submit-btn {
            width: 100%; height: 46px;
            background: var(--tb-primary);
            border: none; border-radius: 0.55rem;
            color: white; font-weight: 600; font-size: 0.92rem;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 0.8rem;
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
        }
        .tb-submit-btn svg { width: 1.1rem; height: 1.1rem; }
        .tb-submit-btn:hover {
            background: var(--tb-primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(11,37,91,0.22);
        }

        .tb-auth-divider {
            display: flex; align-items: center; gap: 0.75rem;
            margin: 1.25rem 0; color: var(--tb-muted); font-size: 0.72rem;
            text-transform: uppercase; letter-spacing: 0.04em;
        }
        .tb-auth-divider::before, .tb-auth-divider::after {
            content: ""; flex: 1; height: 1px; background: var(--tb-primary-light);
        }

        .tb-auth-alt {
            display: flex; align-items: center; justify-content: center; gap: 0.4rem;
            padding: 0.7rem 0.85rem; background: var(--tb-primary-soft);
            border: 1px solid var(--tb-primary-light);
            border-radius: 0.6rem; font-size: 0.85rem; color: var(--tb-muted); text-align: center;
            transition: all 0.2s ease;
        }
        .tb-auth-alt:hover { border-color: var(--tb-accent); }
        .tb-auth-alt a { font-weight: 700; color: var(--tb-primary); }
        .tb-auth-alt a:hover { color: var(--tb-accent-dark); }

        .tb-auth-back {
            position: absolute; top: 1.25rem; left: 1.25rem; z-index: 5;
        }
        .tb-auth-back a {
            color: var(--tb-muted); text-decoration: none; font-weight: 500; font-size: 0.82rem;
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.45rem 0.75rem; border-radius: 0.5rem;
            background: white; border: 1px solid var(--tb-primary-light);
            transition: all 0.2s ease;
        }
        .tb-auth-back a:hover {
            color: var(--tb-primary); border-color: var(--tb-primary);
            background: var(--tb-primary-soft);
        }

        .tb-auth-footer-bar {
            background: var(--tb-primary); color: rgba(255,255,255,0.6);
            padding: 0.7rem; text-align: center; font-size: 0.78rem;
            position: relative;
        }
        .tb-auth-footer-bar::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0;
            height: 3px; background: var(--tb-accent);
        }

        .tb-auth-check {
            display: flex; align-items: center; gap: 0.45rem;
            color: var(--tb-muted); font-size: 0.82rem;
        }
        .tb-auth-check input { accent-color: var(--tb-primary); }
        .tb-auth-link {
            color: var(--tb-accent-dark); text-decoration: none; font-size: 0.78rem; font-weight: 600;
        }
        .tb-auth-link:hover { color: var(--tb-accent); text-decoration: underline; }
    </style>
</head>
<body>
    <div class="tb-auth-wrap">
        <main class="tb-auth-body">
            <div class="tb-auth-back">
                <a href="{{ route('home') }}"><x-icon name="arrow-left" /> Beranda</a>
            </div>
            <div class="tb-auth-card">
                @include('layouts.partials.flash')
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
