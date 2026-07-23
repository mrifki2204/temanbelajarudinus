<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Cek status akun
        if ($user->status !== 'aktif') {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan.');
        }

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
