<?php

use App\Http\Controllers\Admin\AktivitasController;
use App\Http\Controllers\Admin\AuthenticatedSessionController as AdminAuthSession;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FakultasController;
use App\Http\Controllers\Admin\MahasiswaController;
use App\Http\Controllers\Admin\OpsiPreferensiController;
use App\Http\Controllers\Admin\ProdiController;
use App\Http\Controllers\Mahasiswa\AktivitasController as MahasiswaAktivitasController;
use App\Http\Controllers\Mahasiswa\DashboardController;
use App\Http\Controllers\Mahasiswa\PermintaanBelajarController;
use App\Http\Controllers\Mahasiswa\ProfilController;
use App\Http\Controllers\Mahasiswa\RekomendasiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil preferensi (mahasiswa)
    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
});

// Route mahasiswa
Route::middleware(['auth', 'role:mahasiswa'])->group(function () {
    // Rekomendasi
    Route::get('/rekomendasi', [RekomendasiController::class, 'index'])->name('rekomendasi.index');
    Route::get('/rekomendasi/{kandidatId}', [RekomendasiController::class, 'show'])->name('rekomendasi.show');

    // Permintaan belajar
    Route::get('/permintaan', [PermintaanBelajarController::class, 'index'])->name('permintaan.index');
    Route::post('/permintaan', [PermintaanBelajarController::class, 'store'])->name('permintaan.store');
    Route::patch('/permintaan/{permintaan}/accept', [PermintaanBelajarController::class, 'accept'])->name('permintaan.accept');
    Route::patch('/permintaan/{permintaan}/reject', [PermintaanBelajarController::class, 'reject'])->name('permintaan.reject');
    Route::delete('/permintaan/{permintaan}', [PermintaanBelajarController::class, 'cancel'])->name('permintaan.destroy');

    // Setting (keamanan, hapus akun)
    Route::get('/setting', [ProfilController::class, 'setting'])->name('setting.index');
    Route::put('/setting/password', [ProfilController::class, 'updatePassword'])->name('profil.password.update');
    Route::delete('/setting', [ProfilController::class, 'destroy'])->name('profil.destroy');

    // Log aktivitas mahasiswa (riwayat sendiri)
    Route::get('/aktivitas', [MahasiswaAktivitasController::class, 'index'])->name('aktivitas.index');
});

// Login admin (jalur terpisah, khusus role admin)
Route::get('/admin/login', [AdminAuthSession::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminAuthSession::class, 'store'])->middleware('guest');
Route::post('/admin/logout', [AdminAuthSession::class, 'destroy'])->middleware('auth')->name('admin.logout');

// Route admin
// Custom binding: parameter {mahasiswa} hanya resolve user ber-role mahasiswa,
// sehingga controller tak perlu mengecek role tiap method.
Route::bind('mahasiswa', function (string $value) {
    return \App\Models\User::where('id', $value)->where('role', 'mahasiswa')->firstOrFail();
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Fakultas CRUD
    Route::resource('fakultas', FakultasController::class)
        ->except(['show'])
        ->parameters(['fakultas' => 'fakultas']);

    // Prodi CRUD
    Route::resource('prodi', ProdiController::class)
        ->except(['show'])
        ->parameters(['prodi' => 'prodi']);

    // Opsi preferensi CRUD
    Route::resource('opsi', OpsiPreferensiController::class)
        ->except(['show'])
        ->parameters(['opsi' => 'opsi']);

    // Mahasiswa (lihat, edit, toggle status, hapus permanen; tidak ada create akun)
    Route::get('/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
    Route::get('/mahasiswa/{mahasiswa}', [MahasiswaController::class, 'show'])->name('mahasiswa.show');
    Route::get('/mahasiswa/{mahasiswa}/edit', [MahasiswaController::class, 'edit'])->name('mahasiswa.edit');
    Route::put('/mahasiswa/{mahasiswa}', [MahasiswaController::class, 'update'])->name('mahasiswa.update');
    Route::patch('/mahasiswa/{mahasiswa}/toggle-status', [MahasiswaController::class, 'toggleStatus'])->name('mahasiswa.toggle-status');
    Route::delete('/mahasiswa/{mahasiswa}', [MahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');

    // Log aktivitas admin
    Route::get('/aktivitas', [AktivitasController::class, 'index'])->name('aktivitas.index');
});

require __DIR__.'/auth.php';
