<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AktivitasController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::with('user')->latest();

        // Pencarian teks pada deskripsi
        if ($search = $request->input('q')) {
            $escapedSearch = $this->escapeLike($search);
            $query->where('description', 'like', "%{$escapedSearch}%");
        }

        // Filter grup aksi (prefix), mis. 'fakultas', 'mahasiswa', 'permintaan'
        if ($kelompok = $request->input('kelompok')) {
            $query->where('action', 'like', "{$kelompok}.%");
        }

        // Filter user tertentu
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        $aktivitas = $query->paginate(25)->withQueryString();

        // Daftar user untuk filter (hanya yang pernah ada log)
        $userList = User::whereIn('id', ActivityLog::whereNotNull('user_id')->pluck('user_id')->unique())
            ->orderBy('nama')
            ->pluck('nama', 'id');

        // Kelompok aksi untuk filter
        $kelompokList = [
            'fakultas' => 'Master Fakultas',
            'prodi' => 'Master Prodi',
            'opsi' => 'Item Preferensi',
            'mahasiswa' => 'Mahasiswa',
            'profil' => 'Profil Preferensi',
            'permintaan' => 'Permintaan Belajar',
        ];

        return view('admin.aktivitas.index', compact('aktivitas', 'userList', 'kelompokList'));
    }
}
