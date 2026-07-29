<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Blokir akun nonaktif di SEMUA request terautentikasi (bukan hanya role:*).
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        // Invalidate session device lain saat password hash berubah
        // (bekerja bersama Auth::logoutOtherDevices).
        $middleware->authenticateSessions();

        // Trust reverse proxy di depan app (Nginx / Cloudflare).
        // Default: 127.0.0.1 + ::1 (Nginx local/VPS same-host).
        // Production di belakang Cloudflare: set TRUSTED_PROXIES di .env
        // ke IP internal Nginx + (opsional) range CF, dipisah koma.
        // JANGAN pakai '*' — X-Forwarded-For spoofable → bypass rate-limit login.
        $trustedProxies = env('TRUSTED_PROXIES', '127.0.0.1,::1');
        $proxyList = array_values(array_filter(array_map('trim', explode(',', (string) $trustedProxies))));
        $middleware->trustProxies(
            at: $proxyList === [] ? ['127.0.0.1', '::1'] : $proxyList,
        );

        // TrustHosts: percaya host dari APP_URL saja.
        // JANGAN pakai ['*'] — menyebabkan error regex
        // "preg_match(): quantifier does not follow a repeatable item".
        $appHost = parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST);
        $middleware->trustHosts(at: array_values(array_filter([$appHost])));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
