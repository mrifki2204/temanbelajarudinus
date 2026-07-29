<?php

namespace App\Services;

use App\Models\OpsiPreferensi;
use App\Models\Profile;
use App\Models\SimilarityScore;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Content-Based Filtering Service untuk rekomendasi teman belajar.
 *
 * Algoritma:
 * 1. buildFeatureVector(): gabungkan 5 atribut preferensi menjadi satu vektor biner.
 *    - minat & jadwal: binary vector (multi-label)
 *    - tujuan, gaya, mode: one-hot encoding (single kategorikal)
 * 2. cosineSimilarity(): hitung cos(θ) = (A·B) / (||A||·||B||) dengan bobot seragam.
 * 3. calculateForUser(): hitung similaritas user target terhadap seluruh kandidat,
 *    batch upsert ke tabel similarity_scores.
 *
 * Sesuai Bab 4 skripsi — uniform weight pada 5 atribut preferensi.
 */
class ContentBasedFilteringService
{
    /**
     * Kandidat vektor dimensi: union seluruh opsi preferensi.
     * Di-cache per request.
     */
    protected ?Collection $dimensions = null;

    /**
     * Bangun vektor fitur dari profil preferensi.
     * Mengembalikan array asosiatif [dimensi => 0|1] untuk seluruh dimensi.
     *
     * @return array<string, int>
     */
    public function buildFeatureVector(Profile $profile): array
    {
        $dimensions = $this->getDimensions();
        $vector = array_fill_keys($dimensions->all(), 0);

        // minat (multi-label / binary vector)
        foreach (($profile->minat ?? []) as $nilai) {
            $key = "minat:{$nilai}";
            if (array_key_exists($key, $vector)) {
                $vector[$key] = 1;
            }
        }

        // jadwal (multi-label / binary vector)
        foreach (($profile->jadwal ?? []) as $nilai) {
            $key = "jadwal:{$nilai}";
            if (array_key_exists($key, $vector)) {
                $vector[$key] = 1;
            }
        }

        // tujuan (one-hot encoding, single value)
        if ($profile->tujuan) {
            $key = "tujuan:{$profile->tujuan}";
            if (array_key_exists($key, $vector)) {
                $vector[$key] = 1;
            }
        }

        // gaya (one-hot encoding, single value)
        if ($profile->gaya) {
            $key = "gaya:{$profile->gaya}";
            if (array_key_exists($key, $vector)) {
                $vector[$key] = 1;
            }
        }

        // mode (one-hot encoding, single value)
        if ($profile->mode) {
            $key = "mode:{$profile->mode}";
            if (array_key_exists($key, $vector)) {
                $vector[$key] = 1;
            }
        }

        return $vector;
    }

    /**
     * Hitung Cosine Similarity antara dua vektor biner.
     * cos(θ) = (A·B) / (||A|| · ||B||)
     *
     * @param  array<string, int>  $a
     * @param  array<string, int>  $b
     * @return float Rentang 0..1 (vektor biner tidak mungkin negatif)
     */
    public function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        foreach ($a as $key => $valueA) {
            $valueB = $b[$key] ?? 0;
            $dotProduct += $valueA * $valueB;
            $normA += $valueA * $valueA;
            $normB += $valueB * $valueB;
        }

        $denominator = sqrt($normA) * sqrt($normB);
        if ($denominator == 0) {
            return 0.0;
        }

        return (float) ($dotProduct / $denominator);
    }

    /**
     * Hitung & simpan similaritas user target terhadap seluruh kandidat.
     * Kandidat = user mahasiswa lain yang berstatus aktif & profil lengkap.
     * Batch upsert ke similarity_scores.
     *
     * @return int Jumlah pasangan skor yang dihitung.
     */
    public function calculateForUser(User $target): int
    {
        $targetProfile = $target->profile;
        if (! $targetProfile || ! $this->isProfileLengkap($targetProfile)) {
            // Profil target belum lengkap → hapus skor lama, tidak ada kandidat
            SimilarityScore::where('user_id', $target->id)->delete();

            return 0;
        }

        $targetVector = $this->buildFeatureVector($targetProfile);

        // Ambil seluruh kandidat (mahasiswa aktif lain dengan profil lengkap)
        $kandidatList = User::where('role', 'mahasiswa')
            ->where('status', 'aktif')
            ->where('id', '!=', $target->id)
            ->whereHas('profile')
            ->with('profile')
            ->get();

        $rows = [];
        $now = now();

        foreach ($kandidatList as $kandidat) {
            $kandidatProfile = $kandidat->profile;
            if (! $this->isProfileLengkap($kandidatProfile)) {
                continue;
            }

            $kandidatVector = $this->buildFeatureVector($kandidatProfile);
            $skor = $this->cosineSimilarity($targetVector, $kandidatVector);

            $rows[] = [
                'user_id' => $target->id,
                'kandidat_id' => $kandidat->id,
                'skor' => $skor,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Batch upsert: hapus skor lama user target, insert skor baru
        SimilarityScore::where('user_id', $target->id)->delete();

        if (! empty($rows)) {
            // Chunk untuk hindari memory issue pada dataset besar
            foreach (array_chunk($rows, 500) as $chunk) {
                SimilarityScore::insert($chunk);
            }
        }

        // Simpan feature_vector di profil target sebagai cache
        $targetProfile->feature_vector = $targetVector;
        $targetProfile->saveQuietly();

        return count($rows);
    }

    /**
     * Ambil Top-N rekomendasi untuk user target dari similarity_scores.
     *
     * @param  int  $n  Jumlah rekomendasi (default 10).
     * @param  array  $filter  Filter post-Top-N: ['fakultas_id' => ?, 'prodi_id' => ?]
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTopN(User $target, int $n = 10, array $filter = [])
    {
        // Catatan: kandidat pending/accepted/rejected TETAP muncul di rekomendasi.
        // Status hubungan ditampilkan di kartu/detail (badge) agar pengguna tahu
        // kondisinya; rejected boleh kirim ulang.

        $query = SimilarityScore::with(['kandidat.profile', 'kandidat.fakultas', 'kandidat.prodi'])
            ->where('user_id', $target->id)
            ->where('skor', '>', 0);

        // Ambil pool lebih besar sebelum filter fakultas/prodi/jenis_kelamin agar Top-N tetap terpenuhi.
        // Saat filter aktif, perluas pool karena kandidat yang lolos filter mengecil.
        $hasFilter = ! empty($filter['fakultas_id']) || ! empty($filter['prodi_id']) || ! empty($filter['jenis_kelamin']);
        $poolMultiplier = $hasFilter ? 10 : 5;
        $query->orderByDesc('skor')
            ->limit($n * $poolMultiplier);

        $results = $query->get();

        // Filter post-Top-N (tidak mengganggu CBF)
        if (! empty($filter['fakultas_id'])) {
            $results = $results->filter(function ($score) use ($filter) {
                return $score->kandidat && (string) $score->kandidat->fakultas_id === (string) $filter['fakultas_id'];
            });
        }

        if (! empty($filter['prodi_id'])) {
            $results = $results->filter(function ($score) use ($filter) {
                return $score->kandidat && (string) $score->kandidat->prodi_id === (string) $filter['prodi_id'];
            });
        }

        // Jenis kelamin — HANYA filter, tidak ikut perhitungan CBF.
        if (! empty($filter['jenis_kelamin'])) {
            $results = $results->filter(function ($score) use ($filter) {
                return $score->kandidat && $score->kandidat->jenis_kelamin === $filter['jenis_kelamin'];
            });
        }

        return $results->take($n)->values();
    }

    /**
     * Ambil map status hubungan antara user target dan kandidat lain.
     * Key = kandidat_id, value = 'pending' | 'accepted' | 'rejected' | null.
     *
     * Status yang dilihat dari sudut pandang user target:
     * - pending   : ada permintaan (keluar/masuk) yang belum diproses
     * - accepted  : sudah terhubung sebagai teman belajar
     * - rejected  : permintaan pernah ditolak
     *
     * @return array<int, string|null>
     */
    public function getHubunganKandidat(User $target): array
    {
        $map = [];

        $sent = $target->sentRequests()->whereIn('status', ['pending', 'accepted', 'rejected'])->get();
        foreach ($sent as $req) {
            $map[$req->penerima_id] = $req->status;
        }

        $received = $target->receivedRequests()->whereIn('status', ['pending', 'accepted', 'rejected'])->get();
        foreach ($received as $req) {
            // Jangan timpa accepted dengan pending/rejected dari arah lawan.
            $existing = $map[$req->pengirim_id] ?? null;
            if ($existing === 'accepted') {
                continue;
            }
            $map[$req->pengirim_id] = $req->status;
        }

        return $map;
    }

    /**
     * Cek apakah profil preferensi lengkap (5 atribut semua terisi).
     */
    public function isProfileLengkap(Profile $profile): bool
    {
        return $profile->isPreferensiLengkap();
    }

    /**
     * Hitung ulang skor reverse: seluruh user lain (profil lengkap) → $owner.
     * Dipakai ProfileObserver + re-activate akun admin.
     */
    public function recalcReverseScores(User $owner): void
    {
        $ownerProfile = $owner->fresh('profile')?->profile;
        if (! $ownerProfile || ! $this->isProfileLengkap($ownerProfile)) {
            return;
        }

        $ownerVector = $this->buildFeatureVector($ownerProfile);

        $otherUsers = User::where('role', 'mahasiswa')
            ->where('status', 'aktif')
            ->where('id', '!=', $owner->id)
            ->whereHas('profile')
            ->with('profile')
            ->get();

        foreach ($otherUsers as $otherUser) {
            if (! $this->isProfileLengkap($otherUser->profile)) {
                continue;
            }

            $otherVector = $this->buildFeatureVector($otherUser->profile);
            $skor = $this->cosineSimilarity($otherVector, $ownerVector);

            SimilarityScore::updateOrCreate(
                ['user_id' => $otherUser->id, 'kandidat_id' => $owner->id],
                ['skor' => $skor]
            );
        }
    }

    /**
     * Ambil seluruh dimensi vektor (union opsi preferensi).
     * Format key: "{tipe}:{nilai}" — e.g. "minat:Programming", "jadwal:Senin Pagi".
     *
     * @return Collection<string>
     */
    protected function getDimensions(): Collection
    {
        if ($this->dimensions === null) {
            $this->dimensions = Cache::remember('cbf.dimensions', now()->addMinutes(10), function () {
                return OpsiPreferensi::select('tipe', 'nilai')
                    ->get()
                    ->map(fn ($opsi) => "{$opsi->tipe}:{$opsi->nilai}")
                    ->unique()
                    ->values();
            });
        }

        return $this->dimensions;
    }
}
