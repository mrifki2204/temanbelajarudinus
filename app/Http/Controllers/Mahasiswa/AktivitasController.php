<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AktivitasController extends Controller
{
    /**
     * Tampilkan riwayat aktivitas milik mahasiswa yang login saja.
     */
    public function index(Request $request): View
    {
        $query = ActivityLog::with('user')
            ->where('user_id', $request->user()->id)
            ->latest();

        // Pencarian teks pada deskripsi
        if ($search = $request->input('q')) {
            $escapedSearch = $this->escapeLike($search);
            $query->where('description', 'like', "%{$escapedSearch}%");
        }

        // Filter kelompok aksi (prefix)
        if ($kelompok = $request->input('kelompok')) {
            $query->where('action', 'like', "{$kelompok}.%");
        }

        $aktivitas = $query->paginate(15)->withQueryString();

        // Kelompok aksi yang relevan untuk mahasiswa
        $kelompokList = [
            'profil' => 'Profil Preferensi',
            'permintaan' => 'Permintaan Belajar',
            'mahasiswa' => 'Akun',
        ];

        return view('mahasiswa.aktivitas.index', compact('aktivitas', 'kelompokList'));
    }
}
