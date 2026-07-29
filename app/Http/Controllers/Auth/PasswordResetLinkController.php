<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Selalu response sukses yang sama — cegah enumerasi email terdaftar.
        // Link hanya dikirim jika user aktif + role mahasiswa (bukan admin/nonaktif).
        $user = User::where('email', $request->string('email')->lower()->value())->first();

        if ($user
            && $user->status === 'aktif'
            && $user->role === 'mahasiswa'
        ) {
            Password::sendResetLink($request->only('email'));
        }

        return back()->with(
            'status',
            __('Jika email terdaftar dan aktif, tautan reset kata sandi telah dikirim.')
        );
    }
}
