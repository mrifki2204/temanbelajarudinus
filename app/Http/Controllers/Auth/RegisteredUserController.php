<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mahasiswa\RegistrationRequest;
use App\Models\ActivityLog;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $fakultasList = Fakultas::orderBy('nama')->get();
        $prodiList = Prodi::with('fakultas')->orderBy('nama')->get();

        return view('auth.register', compact('fakultasList', 'prodiList'));
    }

    public function store(RegistrationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // role/status di-hardcode server-side — tidak dari input request.
        // password plain: cast 'hashed' di model yang meng-hash.
        $user = User::create([
            'nama' => $validated['nama'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'nim' => $validated['nim'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'mahasiswa',
            'status' => 'aktif',
            'fakultas_id' => $validated['fakultas_id'],
            'prodi_id' => $validated['prodi_id'],
            'semester' => $validated['semester'],
            'angkatan' => $validated['angkatan'],
        ]);

        event(new Registered($user));

        Auth::login($user);

        ActivityLog::record(
            'mahasiswa.register',
            "Mahasiswa baru terdaftar: {$user->nama} ({$user->nim}).",
            $user,
        );

        // Mahasiswa baru wajib isi profil preferensi dulu
        return redirect()->route('profil.edit');
    }
}
