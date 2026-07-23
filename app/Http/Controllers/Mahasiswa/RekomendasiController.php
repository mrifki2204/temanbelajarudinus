<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\StudyRequest;
use App\Services\ContentBasedFilteringService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RekomendasiController extends Controller
{
    public function __construct(
        protected ContentBasedFilteringService $cbfService
    ) {}

    /**
     * Tampilkan daftar rekomendasi Top-10 dengan filter opsional.
     * Filter diterapkan post-Top-N (tidak mengganggu perhitungan CBF).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Jika profil belum lengkap, redirect ke halaman profil
        if (! $user->profile || ! $this->cbfService->isProfileLengkap($user->profile)) {
            return view('mahasiswa.rekomendasi.belum-lengkap');
        }

        // Hitung ulang similarity jika belum ada skor untuk user ini
        // (mis. user lama sebelum fitur CBF aktif, atau opsi preferensi baru ditambah admin)
        if (! $user->similarityScores()->exists()) {
            $this->cbfService->calculateForUser($user);
        }

        // Ambil filter dari query string (id)
        $filterFakultas = $request->input('fakultas_id');
        $filterProdi = $request->input('prodi_id');
        $filterGender = $request->input('jenis_kelamin');

        $filter = [];
        if ($filterFakultas) {
            $filter['fakultas_id'] = $filterFakultas;
        }
        if ($filterProdi) {
            $filter['prodi_id'] = $filterProdi;
        }
        if ($filterGender) {
            $filter['jenis_kelamin'] = $filterGender;
        }

        $rekomendasi = $this->cbfService->getTopN($user, 10, $filter);

        // Total kandidat tersedia (untuk info & cakupan filter).
        $totalKandidat = \App\Models\SimilarityScore::where('user_id', $user->id)
            ->where('skor', '>', 0)
            ->count();

        // Status hubungan per kandidat (pending/accepted/rejected) untuk badge di kartu.
        $hubungan = $this->cbfService->getHubunganKandidat($user);

        // Opsi untuk dropdown filter (prodi difilter berdasarkan fakultas terpilih)
        $fakultasList = Fakultas::orderBy('nama')->get();
        $prodiQuery = Prodi::query()->with('fakultas')->orderBy('nama');
        if ($filterFakultas) {
            $prodiQuery->where('fakultas_id', $filterFakultas);
        }
        $prodiList = $prodiQuery->get();

        return view('mahasiswa.rekomendasi.index', compact(
            'user',
            'rekomendasi',
            'totalKandidat',
            'hubungan',
            'fakultasList',
            'prodiList',
            'filterFakultas',
            'filterProdi',
            'filterGender',
        ));
    }

    /**
     * Tampilkan detail kandidat + tombol kirim permintaan belajar.
     *
     * Kontak (WA/IG) TIDAK ditampilkan di sini — hanya terlihat setelah permintaan diterima.
     */
    public function show(Request $request, int $kandidatId): View
    {
        $user = $request->user();

        $kandidat = \App\Models\User::with(['profile', 'fakultas', 'prodi'])
            ->where('role', 'mahasiswa')
            ->where('status', 'aktif')
            ->where('id', '!=', $user->id)
            ->findOrFail($kandidatId);

        // Ambil skor similaritas user → kandidat (jika ada)
        $skor = \App\Models\SimilarityScore::where('user_id', $user->id)
            ->where('kandidat_id', $kandidatId)
            ->value('skor');

        // Cek status permintaan yang sudah ada ke kandidat ini
        $permintaanTerkirim = StudyRequest::where('pengirim_id', $user->id)
            ->where('penerima_id', $kandidatId)
            ->latest()
            ->first();

        // Cek apakah kandidat ini sudah punya hubungan accepted dengan user
        $sudahTerhubung = StudyRequest::where('status', 'accepted')
            ->where(function ($q) use ($user, $kandidatId) {
                $q->where(function ($q2) use ($user, $kandidatId) {
                    $q2->where('pengirim_id', $user->id)->where('penerima_id', $kandidatId);
                })->orWhere(function ($q2) use ($user, $kandidatId) {
                    $q2->where('pengirim_id', $kandidatId)->where('penerima_id', $user->id);
                });
            })
            ->exists();

        // Jika sudah terhubung (accepted), tampilkan kontak
        $tampilkanKontak = $sudahTerhubung;

        return view('mahasiswa.rekomendasi.show', compact(
            'user',
            'kandidat',
            'skor',
            'permintaanTerkirim',
            'sudahTerhubung',
            'tampilkanKontak',
        ));
    }
}
