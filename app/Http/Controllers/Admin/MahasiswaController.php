<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateMahasiswaRequest;
use App\Models\ActivityLog;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\SimilarityScore;
use App\Models\StudyRequest;
use App\Models\User;
use App\Services\ContentBasedFilteringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::where('role', 'mahasiswa')->with(['profile', 'fakultas', 'prodi']);

        if ($search = $request->input('q')) {
            $escapedSearch = $this->escapeLike($search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('nama', 'like', "%{$escapedSearch}%")
                    ->orWhere('nim', 'like', "%{$escapedSearch}%")
                    ->orWhere('email', 'like', "%{$escapedSearch}%");
            });
        }

        if ($fakultasId = $request->input('fakultas_id')) {
            $query->where('fakultas_id', $fakultasId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $mahasiswaList = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $fakultasList = Fakultas::orderBy('nama')->pluck('nama', 'id');

        return view('admin.mahasiswa.index', compact('mahasiswaList', 'fakultasList'));
    }

    public function show(User $mahasiswa): View
    {
        $mahasiswa->load(['profile', 'fakultas', 'prodi']);
        $permintaanTerkirim = StudyRequest::where('pengirim_id', $mahasiswa->id)->count();
        $permintaanDiterima = StudyRequest::where('penerima_id', $mahasiswa->id)->count();
        $skorDiberikan = SimilarityScore::where('user_id', $mahasiswa->id)->count();

        return view('admin.mahasiswa.show', compact(
            'mahasiswa',
            'permintaanTerkirim',
            'permintaanDiterima',
            'skorDiberikan',
        ));
    }

    public function edit(User $mahasiswa): View
    {
        $mahasiswa->load('profile');
        $fakultasList = Fakultas::orderBy('nama')->get();
        $prodiList = Prodi::with('fakultas')->orderBy('nama')->get();

        return view('admin.mahasiswa.edit', compact('mahasiswa', 'fakultasList', 'prodiList'));
    }

    public function update(UpdateMahasiswaRequest $request, User $mahasiswa): RedirectResponse
    {
        $validated = $request->validated();

        // Field User (tabel users) vs Profile (tabel profiles) dipisah agar
        // mass-assignment tidak mencoba menulis kolom yang tidak ada.
        $mahasiswa->update([
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'nim' => $validated['nim'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'fakultas_id' => $validated['fakultas_id'],
            'prodi_id' => $validated['prodi_id'],
            'semester' => $validated['semester'],
            'angkatan' => $validated['angkatan'],
        ]);

        // Simpan kontak (WhatsApp & Instagram) ke profil
        $mahasiswa->profile()->updateOrCreate(
            ['user_id' => $mahasiswa->id],
            [
                'whatsapp' => $validated['whatsapp'] ?? null,
                'instagram' => $validated['instagram'] ?? null,
            ]
        );

        ActivityLog::record(
            'mahasiswa.update',
            "Admin memperbarui data mahasiswa {$mahasiswa->nama}.",
            $mahasiswa,
        );

        return redirect()->route('admin.mahasiswa.show', $mahasiswa)
            ->with('success', "Data {$mahasiswa->nama} berhasil diperbarui.");
    }

    /**
     * Toggle status akun mahasiswa: aktif ↔ nonaktif.
     *
     * Saat menonaktifkan: hapus seluruh similarity_scores terkait user ini
     * (baik sebagai user_id maupun kandidat_id) agar tidak muncul di rekomendasi
     * mahasiswa lain hingga user diaktifkan kembali.
     *
     * Saat mengaktifkan kembali: recalc skor user ini (forward) lewat observer
     * tidak terpicu karena profil tidak berubah, jadi panggil service langsung.
     */
    public function toggleStatus(Request $request, User $mahasiswa): RedirectResponse
    {
        $newStatus = $mahasiswa->status === 'aktif' ? 'nonaktif' : 'aktif';
        $mahasiswa->update(['status' => $newStatus]);

        if ($newStatus === 'nonaktif') {
            // Hapus skor terkait user ini (sebagai pencari & sebagai kandidat)
            SimilarityScore::where('user_id', $mahasiswa->id)->delete();
            SimilarityScore::where('kandidat_id', $mahasiswa->id)->delete();
        } else {
            // Aktifkan kembali: hitung ulang skor user ini terhadap seluruh kandidat aktif.
            // Skor reverse (user lain → user ini) akan diisi saat masing-masing user lain
            // mengedit profil; tapi untuk segera tersedia, kita recalc reverse di sini juga.
            $cbf = app(ContentBasedFilteringService::class);
            $cbf->calculateForUser($mahasiswa);
        }

        $pesan = $newStatus === 'aktif'
            ? "Akun {$mahasiswa->nama} diaktifkan kembali. Mahasiswa kini dapat login."
            : "Akun {$mahasiswa->nama} dinonaktifkan. Mahasiswa tidak dapat login hingga diaktifkan kembali.";

        ActivityLog::record(
            'mahasiswa.toggle',
            $newStatus === 'aktif'
                ? "Admin mengaktifkan kembali akun {$mahasiswa->nama}."
                : "Admin menonaktifkan akun {$mahasiswa->nama}.",
            $mahasiswa,
            ['status' => $newStatus],
        );

        return redirect()->back()->with('success', $pesan);
    }

    /**
     * Hapus permanen akun mahasiswa beserta data terkait.
     *
     * Bisa menghapus akun aktif maupun nonaktif. Konfirmasi ganda ada di sisi UI
     * (modal JS). Hapus akun akan cascade menghapus profil, skor similaritas,
     * dan permintaan belajar terkait.
     */
    public function destroy(User $mahasiswa): RedirectResponse
    {
        $nama = $mahasiswa->nama;

        // Hapus user; relasi (SimilarityScore, StudyRequest, Profile) ter-cascade
        // oleh FK cascadeOnDelete. ProfileObserver::deleted membersihkan skor.
        $mahasiswa->delete();

        ActivityLog::record(
            'mahasiswa.delete',
            "Akun mahasiswa {$nama} dihapus permanen beserta data terkait.",
        );

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', "Akun {$nama} berhasil dihapus permanen.");
    }
}
