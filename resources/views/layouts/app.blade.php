<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Teman Belajar Udinus') }} @yield('title')</title>
    @include('layouts.partials.favicon')
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

        .tb-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: 220px; z-index: 1040;
            display: flex; flex-direction: column;
            transition: transform 0.2s ease;
            background: white;
            border-right: 1px solid var(--tb-primary-light);
        }

        /* Brand header — logo di tengah */
        .tb-sidebar-brand {
            display: flex; align-items: center; justify-content: center;
            padding: 1.1rem 0.75rem;
            text-decoration: none;
            border-bottom: 1px solid var(--tb-primary-light);
        }
        .tb-brand-logo {
            width: 64px; height: 64px; border-radius: 0.55rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .tb-brand-logo img { width: 60px; height: 60px; object-fit: contain; }

        /* Navigation — pill style */
        .tb-sidebar-nav { flex: 1; padding: 0.85rem 0.75rem; overflow-y: auto; }
        .tb-sidebar-nav::-webkit-scrollbar { width: 4px; }
        .tb-sidebar-nav::-webkit-scrollbar-thumb { background: var(--tb-primary-light); border-radius: 2px; }
        .tb-sidebar-nav::-webkit-scrollbar-track { background: transparent; }

        .tb-sidebar-section {
            font-size: 0.62rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.06em; color: var(--tb-muted);
            padding: 0.5rem 0.75rem 0.4rem;
        }
        .tb-sidebar-section:first-child { padding-top: 0; }

        .tb-sidebar-link {
            display: flex; align-items: center; gap: 0.7rem;
            padding: 0.5rem 0.8rem; border-radius: 0.55rem;
            color: var(--tb-muted); text-decoration: none;
            font-size: 0.84rem; font-weight: 500;
            transition: background 0.15s ease, color 0.15s ease;
            margin-bottom: 0.2rem;
        }
        .tb-sidebar-link > svg:not(.tb-sidebar-chevron) { width: 1.05rem; height: 1.05rem; flex-shrink: 0; }
        .tb-sidebar-link.active > svg:not(.tb-sidebar-chevron) { color: white; }
        .tb-sidebar-link-text { flex: 1; min-width: 0; }
        .tb-sidebar-link:hover {
            background: var(--tb-primary-soft); color: var(--tb-ink);
        }
        .tb-sidebar-link:focus-visible {
            outline: 2px solid var(--tb-primary); outline-offset: 2px;
        }

        /* Active state — pill background navy */
        .tb-sidebar-link.active {
            background: var(--tb-primary);
            color: white; font-weight: 600;
        }

        .tb-sidebar-badge {
            margin-left: auto; background: #dc3545; color: white;
            font-size: 0.62rem; font-weight: 600; padding: 0.1rem 0.4rem; border-radius: 0.4rem;
            line-height: 1; min-width: 18px; text-align: center;
        }
        .tb-sidebar-link.active .tb-sidebar-badge { background: white; color: var(--tb-primary); }

        /* Dropdown group (Opsi Preferensi) */
        .tb-sidebar-group { margin-bottom: 0.2rem; }
        .tb-sidebar-toggle { width: 100%; cursor: pointer; border: none; background: none; padding: 0.5rem 0.8rem; }
        .tb-sidebar-chevron { width: 0.85rem; height: 0.85rem; margin-left: auto; transition: transform 0.2s ease; flex-shrink: 0; color: var(--tb-muted); }
        .tb-sidebar-toggle[aria-expanded="true"] .tb-sidebar-chevron { transform: rotate(180deg); }
        .tb-sidebar-sub {
            display: flex; flex-direction: column; gap: 0.1rem;
            margin: 0.15rem 0 0.2rem 1.35rem;
            padding-left: 0.55rem; border-left: 2px solid var(--tb-primary-light);
        }
        .tb-sidebar-sub-link {
            display: block; padding: 0.3rem 0.55rem; border-radius: 0.4rem;
            color: var(--tb-muted); text-decoration: none;
            font-size: 0.76rem; font-weight: 500; text-transform: capitalize;
            transition: background 0.15s ease, color 0.15s ease;
        }
        .tb-sidebar-sub-link:hover { background: var(--tb-primary-soft); color: var(--tb-ink); }
        .tb-sidebar-sub-link.active { background: var(--tb-primary-light); color: var(--tb-primary); font-weight: 600; }
        .tb-sub-trans { transition: opacity 0.15s ease, transform 0.15s ease; }
        .tb-sub-start { opacity: 0; transform: translateY(-4px); }
        .tb-sub-end { opacity: 1; transform: translateY(0); }
        [x-cloak] { display: none !important; }

        /* Footer user card */
        .tb-sidebar-footer {
            padding: 0.75rem;
            border-top: 1px solid var(--tb-primary-light);
            background: var(--tb-primary-soft);
        }
        .tb-sidebar-user {
            display: flex; align-items: center; gap: 0.65rem;
            padding: 0.55rem 0.65rem; border-radius: 0.55rem;
            background: white;
            border: 1px solid var(--tb-primary-light);
            margin-bottom: 0.5rem;
        }
        .tb-sidebar-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--tb-primary);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.74rem; color: white;
            flex-shrink: 0;
        }
        .tb-sidebar-user-info { flex: 1; min-width: 0; }
        .tb-sidebar-user-info .name {
            font-size: 0.78rem; font-weight: 600; color: var(--tb-ink);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .tb-sidebar-user-info .role {
            font-size: 0.66rem; color: var(--tb-muted);
            text-transform: capitalize; margin-top: 1px;
        }
        .tb-sidebar-logout {
            width: 100%;
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.55rem 0.8rem;
            border: 1px solid var(--tb-primary-light); border-radius: 0.55rem;
            background: white;
            color: var(--tb-muted); font-size: 0.82rem; font-weight: 600;
            cursor: pointer;
            transition: color 0.15s ease, background 0.15s ease, border-color 0.15s ease;
        }
        .tb-sidebar-logout i { font-size: 0.92rem; }
        .tb-sidebar-logout:hover {
            color: #dc3545; background: rgba(220,53,69,0.06); border-color: rgba(220,53,69,0.25);
        }
        .tb-sidebar-logout:focus-visible {
            outline: 2px solid var(--tb-primary); outline-offset: 1px;
        }

        .tb-main {
            margin-left: 220px; min-height: 100vh;
            display: flex; flex-direction: column;
        }
        .tb-main-content { flex: 1; padding: 1.5rem 1.5rem 3rem; }

        .tb-mobile-toggle {
            display: none; position: fixed; top: 0.6rem; left: 0.6rem; z-index: 1050;
            background: white; border: 1px solid var(--tb-primary-light);
            border-radius: 0.45rem; padding: 0.4rem 0.55rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08); color: var(--tb-primary);
            cursor: pointer;
        }
        .tb-mobile-toggle:hover { background: var(--tb-primary-soft); }
        .tb-mobile-toggle svg { width: 1.15rem; height: 1.15rem; display: block; }
        .tb-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.4); z-index: 1035;
        }
        .tb-overlay.show { display: block; }
        @media (max-width: 991.98px) {
            .tb-sidebar { transform: translateX(-100%); }
            .tb-sidebar.show { transform: translateX(0); box-shadow: 2px 0 12px rgba(0,0,0,0.12); }
            .tb-main { margin-left: 0; }
            .tb-mobile-toggle { display: block; }
            .tb-main-content { padding-top: 3rem; }
        }
    </style>
    @include('layouts.partials.theme')
</head>
<body>
    <button class="tb-mobile-toggle" onclick="document.querySelector('.tb-sidebar').classList.toggle('show');document.querySelector('.tb-overlay').classList.toggle('show')" aria-label="Buka menu navigasi">
        <x-icon name="list" />
    </button>
    <div class="tb-overlay" onclick="document.querySelector('.tb-sidebar').classList.remove('show');this.classList.remove('show')"></div>

    <div class="tb-sidebar">
        @include('layouts.navigation')
    </div>

    <div class="tb-main">
        <main class="tb-main-content">
            @include('layouts.partials.flash')
            @yield('content')
        </main>
    </div>

    {{-- Instant search: form dengan class .tb-instant-search auto-submit saat input/select berubah (debounced) --}}
    <script>
    (function () {
        function initInstantSearch() {
            document.querySelectorAll('form.tb-instant-search:not([data-instant-bound])').forEach(function (form) {
                form.setAttribute('data-instant-bound', '1');
                var timer = null;
                var inputs = form.querySelectorAll('input[name]:not([type="hidden"]):not([type="submit"]), select[name]');

                function submit() {
                    // Hapus parameter kosong agar URL bersih
                    inputs.forEach(function (el) {
                        if (!el.value) el.name = '';
                    });
                    form.submit();
                }

                inputs.forEach(function (el) {
                    var evt = el.tagName === 'SELECT' ? 'change' : 'input';
                    el.addEventListener(evt, function () {
                        clearTimeout(timer);
                        // Select langsung submit (tidak perlu debounce),
                        // teks debounced agar tidak request per ketukan.
                        timer = setTimeout(submit, el.tagName === 'SELECT' ? 0 : 350);
                    });
                });
            });
        }
        document.addEventListener('DOMContentLoaded', initInstantSearch);
        document.addEventListener('alpine:initialized', initInstantSearch);
    })();
    </script>

    @stack('scripts')
</body>
</html>
