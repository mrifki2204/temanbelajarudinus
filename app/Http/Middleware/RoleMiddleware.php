<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Batasi akses berdasarkan role.
     * Pakai: ->middleware('role:admin') atau ->middleware('role:mahasiswa,admin')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Status nonaktif ditangani EnsureUserIsActive (global web stack).
        // Di sini hanya cek role.

        if (! in_array($user->role, $roles, true)) {
            // Mahasiswa yang mencoba akses area admin → arahkan ke dashboard mahasiswa
            if ($user->role === 'mahasiswa') {
                return redirect()->route('dashboard');
            }
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
