<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\StudyRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // Statistik & distribusi boleh stale 2 menit (TTL pendek, tidak perlu
        // invalidasi eksplisit). Data fresh (mahasiswa & aktivitas terbaru)
        // tidak di-cache.
        [$stats, $distribusiFakultas, $distribusiProdi] = Cache::remember(
            'admin.dashboard.stats',
            now()->addMinutes(2),
            fn () => [
                [
                    'total_mahasiswa' => User::where('role', 'mahasiswa')->count(),
                    'total_admin' => User::where('role', 'admin')->count(),
                    'mahasiswa_aktif' => User::where('role', 'mahasiswa')->where('status', 'aktif')->count(),
                    'mahasiswa_nonaktif' => User::where('role', 'mahasiswa')->where('status', 'nonaktif')->count(),
                    'profil_lengkap' => User::where('role', 'mahasiswa')->whereHas('profile')->count(),
                    'total_permintaan' => StudyRequest::count(),
                    'permintaan_pending' => StudyRequest::where('status', 'pending')->count(),
                    'permintaan_accepted' => StudyRequest::where('status', 'accepted')->count(),
                    'fakultas_count' => Fakultas::count(),
                    'prodi_count' => Prodi::count(),
                ],
                User::where('role', 'mahasiswa')
                    ->whereNotNull('fakultas_id')
                    ->join('fakultas', 'users.fakultas_id', '=', 'fakultas.id')
                    ->selectRaw('fakultas.nama as fakultas, COUNT(*) as jumlah')
                    ->groupBy('fakultas.nama')
                    ->orderBy('jumlah', 'desc')
                    ->get(),
                User::where('role', 'mahasiswa')
                    ->whereNotNull('prodi_id')
                    ->join('prodi', 'users.prodi_id', '=', 'prodi.id')
                    ->join('fakultas', 'prodi.fakultas_id', '=', 'fakultas.id')
                    ->selectRaw('prodi.nama as program_studi, fakultas.nama as fakultas, COUNT(*) as jumlah')
                    ->groupBy('prodi.nama', 'fakultas.nama')
                    ->orderBy('jumlah', 'desc')
                    ->limit(10)
                    ->get(),
            ],
        );

        $mahasiswaTerbaru = User::where('role', 'mahasiswa')
            ->with(['prodi', 'fakultas'])
            ->latest()
            ->limit(5)
            ->get();

        $aktivitasTerbaru = ActivityLog::with('user')->latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'distribusiFakultas', 'distribusiProdi', 'mahasiswaTerbaru', 'aktivitasTerbaru'));
    }
}
