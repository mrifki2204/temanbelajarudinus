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

        // Penting di belakang reverse proxy (Nginx) + HTTPS (Let's Encrypt):
        // agar URL & asset yang di-generate pakai skema https yang benar.
        // trustProxies('*') = percaya semua proxy (VPS single, di belakang Nginx).
        $middleware->trustProxies(at: '*');
        // TrustHosts: percaya host dari APP_URL saja.
        // JANGAN pakai ['*'] — menyebabkan error regex
        // "preg_match(): quantifier does not follow a repeatable item".
        $middleware->trustHosts(at: [parse_url(env('APP_URL'), PHP_URL_HOST)]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
