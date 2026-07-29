<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pastikan user terautentikasi berstatus aktif.
 * Berjalan di stack web agar route auth-only (dashboard, profil, password)
 * juga memblokir akun nonaktif — bukan hanya route role:*.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status !== 'aktif') {
            Auth::logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()
                ->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Hubungi admin untuk informasi lebih lanjut.');
        }

        return $next($request);
    }
}
