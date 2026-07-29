<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mahasiswa\ProfilUpdateRequest;
use App\Models\ActivityLog;
use App\Models\Fakultas;
use App\Models\OpsiPreferensi;
use App\Models\Prodi;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfilController extends Controller
{
    /**
     * Tampilkan halaman profil preferensi + pengaturan akun.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->load('profile');

        $fakultasList = Fakultas::orderBy('nama')->get();
        $prodiList = Prodi::with('fakultas')->orderBy('nama')->get();

        $opsi = [
            'minat' => OpsiPreferensi::where('tipe', 'minat')->orderBy('nilai')->pluck('nilai'),
            'tujuan' => OpsiPreferensi::where('tipe', 'tujuan')->orderBy('nilai')->pluck('nilai'),
            'gaya' => OpsiPreferensi::where('tipe', 'gaya')->orderBy('nilai')->pluck('nilai'),
            'jadwal' => OpsiPreferensi::where('tipe', 'jadwal')->orderBy('nilai')->pluck('nilai'),
            'mode' => OpsiPreferensi::where('tipe', 'mode')->orderBy('nilai')->pluck('nilai'),
        ];

        // Cek apakah profil sudah lengkap (preferensi + kontak)
        $profile = $user->profile;
        $profilLengkap = $profile && $profile->isOnboardingComplete();

        // Jika belum lengkap → tampilkan halaman onboarding (tanpa navbar)
        if (! $profilLengkap) {
            return view('mahasiswa.profil.onboarding', compact('user', 'fakultasList', 'prodiList', 'opsi'));
        }

        // Jika sudah lengkap → tampilkan halaman profil normal (dengan navbar)
        return view('mahasiswa.profil.edit', compact('user', 'fakultasList', 'prodiList', 'opsi'));
    }

    /**
     * Simpan/update profil preferensi belajar + info akademik.
     */
    public function update(ProfilUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validated();

        // Normalisasi instagram
        $instagram = ltrim($validated['instagram'], '@');

        // Update atau buat profil preferensi
        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);
        $profile->fill([
            'minat' => $validated['minat'],
            'tujuan' => $validated['tujuan'],
            'gaya' => $validated['gaya'],
            'jadwal' => $validated['jadwal'],
            'mode' => $validated['mode'],
            'whatsapp' => $validated['whatsapp'],
            'instagram' => $instagram,
        ]);
        $profile->user_id = $user->id;
        // Cek apakah ini onboarding (profil belum lengkap sebelum simpan)
        $wasOnboarding = ! $user->profile || ! $user->profile->isOnboardingComplete();

        $profile->save();

        ActivityLog::record(
            'profil.update',
            $wasOnboarding
                ? "Mahasiswa {$user->nama} mengisi profil preferensi (onboarding)."
                : "Mahasiswa {$user->nama} memperbarui profil preferensi.",
            $user,
        );

        // Jika dari onboarding → redirect ke dashboard
        if ($wasOnboarding) {
            return redirect()->route('dashboard')->with('success', 'Profil preferensi berhasil disimpan. Selamat datang di Teman Belajar Udinus!');
        }

        return redirect()->route('profil.edit')->with('success', 'Profil preferensi berhasil disimpan.');
    }

    /**
     * Tampilkan halaman setting (akun, keamanan, hapus akun).
     */
    public function setting(Request $request): View
    {
        $user = $request->user();
        return view('mahasiswa.setting.index', compact('user'));
    }

    /**
     * Update password akun.
     *
     * @throws ValidationException
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        // Matikan session device lain (cookie dicuri tidak lagi valid).
        Auth::logoutOtherDevices($validated['current_password']);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return redirect()->route('setting.index')->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
