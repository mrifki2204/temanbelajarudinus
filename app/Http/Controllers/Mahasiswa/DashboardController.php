<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard mahasiswa: sambutan + rekomendasi Top-3.
     *
     * Catatan: seluruh komputasi (profil lengkap, rekomendasi Top-3, statistik
     * permintaan) dilakukan di view dari auth()->user() agar konsisten dengan
     * implementasi existing. Lihat resources/views/mahasiswa/dashboard.blade.php.
     */
    public function index(Request $request): View
    {
        return view('mahasiswa.dashboard');
    }
}
