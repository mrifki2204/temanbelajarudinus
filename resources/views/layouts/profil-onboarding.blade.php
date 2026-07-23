<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Teman Belajar Udinus') }} · Lengkapi Profil</title>
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
        a { color: var(--tb-primary); }

        .tb-onboard-wrap {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .tb-onboard-body {
            flex: 1;
            padding: 2rem 1.25rem 3rem;
            display: flex;
            justify-content: center;
        }

        .tb-onboard-card {
            width: 100%;
            max-width: 720px;
            background: white;
            border-radius: 1.25rem;
            border: 1px solid var(--tb-primary-light);
            box-shadow: 0 20px 50px rgba(11, 37, 91, 0.08), 0 4px 12px rgba(0,0,0,0.03);
            padding: 2rem 2.25rem;
            position: relative;
        }
        .tb-onboard-card::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: var(--tb-accent);
            border-radius: 1.25rem 1.25rem 0 0;
        }
        @media (max-width: 575.98px) {
            .tb-onboard-card { padding: 1.5rem 1.25rem; border-radius: 1rem; }
        }

        .tb-onboard-title {
            font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em;
            color: var(--tb-ink); margin-bottom: 0.3rem; text-align: center;
        }
        .tb-onboard-subtitle {
            color: var(--tb-muted); margin-bottom: 1.75rem; font-size: 0.9rem; text-align: center;
        }

        .tb-section-label {
            display: flex; align-items: center; gap: 0.5rem;
            font-size: 0.82rem; font-weight: 700; color: var(--tb-primary);
            text-transform: uppercase; letter-spacing: 0.04em;
            margin-bottom: 1rem; margin-top: 0.5rem;
        }
        .tb-section-label::after {
            content: ""; flex: 1; height: 1px; background: var(--tb-primary-light);
        }
        .tb-form-label { font-weight: 600; font-size: 0.82rem; color: var(--tb-ink); margin-bottom: 0.35rem; }
        .tb-form-control {
            height: 44px; border-radius: 0.5rem; border: 1.5px solid #e2e8f0;
            font-size: 0.9rem; transition: all 0.18s ease;
        }
        .tb-form-control:focus {
            border-color: var(--tb-primary);
            box-shadow: 0 0 0 3px rgba(11, 37, 91, 0.10);
        }
        .tb-check-card {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.5rem 0.75rem; background: white;
            border: 1.5px solid #e2e8f0; border-radius: 0.5rem;
            cursor: pointer; transition: all 0.18s ease; font-size: 0.85rem;
        }
        .tb-check-card:hover { border-color: var(--tb-primary); background: var(--tb-primary-soft); }
        .tb-check-card input { accent-color: var(--tb-primary); margin: 0; }
        .tb-check-card:has(input:checked) {
            background: var(--tb-primary-light); border-color: var(--tb-primary);
            color: var(--tb-primary); font-weight: 600;
        }
        .tb-jadwal-table { border-radius: 0.6rem; overflow: hidden; }
        .tb-jadwal-table th {
            background: var(--tb-primary); color: white;
            font-size: 0.78rem; font-weight: 600; text-align: center;
            border: none; padding: 0.6rem 0.4rem;
        }
        .tb-jadwal-table td {
            border: 1px solid var(--tb-primary-light); padding: 0.5rem;
            text-align: center; font-size: 0.82rem;
        }
        .tb-jadwal-table td:first-child {
            font-weight: 600; background: var(--tb-primary-soft);
            color: var(--tb-primary); text-align: left; padding-left: 0.75rem;
        }
        .tb-jadwal-table input { accent-color: var(--tb-accent); width: 1.1rem; height: 1.1rem; cursor: pointer; }
        .tb-btn-save {
            height: 48px; font-weight: 600; font-size: 0.95rem; border-radius: 0.5rem;
            background: var(--tb-primary); border: 2px solid var(--tb-primary); color: white;
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .tb-btn-save:hover {
            background: transparent; border-color: var(--tb-accent);
            color: var(--tb-accent); box-shadow: 0 0 0 4px rgba(255, 167, 58, 0.15);
        }
        .tb-contact-note {
            background: var(--tb-accent-light); border: 1px solid rgba(255, 167, 58, 0.25);
            border-radius: 0.5rem; padding: 0.75rem 1rem;
            font-size: 0.82rem; color: var(--tb-accent-dark); margin-bottom: 1rem;
        }
    </style>
    @include('layouts.partials.theme')
</head>
<body>
    <div class="tb-onboard-wrap">
        <main class="tb-onboard-body">
            <div class="tb-onboard-card">
                @include('layouts.partials.flash')

                <div style="display:flex;justify-content:center;margin-bottom:1rem;">
                    <img src="{{ asset('img/logo.png') }}" alt="Teman Belajar Udinus" style="width:88px;height:88px;display:block;">
                </div>

                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
