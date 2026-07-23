<?php

namespace Tests\Unit;

use App\Models\OpsiPreferensi;
use App\Models\Profile;
use App\Models\StudyRequest;
use App\Models\User;
use App\Services\ContentBasedFilteringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit test untuk ContentBasedFilteringService — jantung algoritma skripsi.
 *
 * Menguji:
 * - cosineSimilarity: identik, tidak overlap, zero vector
 * - buildFeatureVector: encoding multi-label vs one-hot
 * - isProfileLengkap: 5 atribut wajib
 * - calculateForUser: batch upsert skor
 * - getTopN: urutan, filter, kandidat pending/accepted tetap muncul dengan badge status
 */
class ContentBasedFilteringServiceTest extends TestCase
{
    use RefreshDatabase;

    private ContentBasedFilteringService $cbf;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cbf = app(ContentBasedFilteringService::class);
        $this->seedOpsiPreferensi();
    }

    /**
     * Seed opsi preferensi yang dipakai seluruh test.
     * Nilai harus konsisten dengan seeder/OpsiPreferensiSeeder.
     */
    private function seedOpsiPreferensi(): void
    {
        $opsi = [
            ['tipe' => 'minat', 'nilai' => 'Programming'],
            ['tipe' => 'minat', 'nilai' => 'Desain'],
            ['tipe' => 'minat', 'nilai' => 'Data'],
            ['tipe' => 'tujuan', 'nilai' => 'Mengerjakan Tugas'],
            ['tipe' => 'tujuan', 'nilai' => 'Persiapan Ujian'],
            ['tipe' => 'gaya', 'nilai' => 'Diskusi'],
            ['tipe' => 'gaya', 'nilai' => 'Mandiri'],
            ['tipe' => 'jadwal', 'nilai' => 'Senin Pagi'],
            ['tipe' => 'jadwal', 'nilai' => 'Senin Sore'],
            ['tipe' => 'jadwal', 'nilai' => 'Selasa Pagi'],
            ['tipe' => 'mode', 'nilai' => 'Tatap Muka'],
            ['tipe' => 'mode', 'nilai' => 'Daring'],
        ];

        foreach ($opsi as $o) {
            OpsiPreferensi::create($o);
        }
    }

    /** Buat user mahasiswa aktif + profil lengkap dengan atribut tertentu. */
    private function makeMahasiswa(array $preferensi, array $userAttr = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => 'mahasiswa',
            'status' => 'aktif',
        ], $userAttr));

        Profile::create(array_merge([
            'user_id' => $user->id,
            'whatsapp' => '081234567890',
            'instagram' => '@test',
        ], $preferensi));

        return $user->fresh('profile');
    }

    // ------------------------------------------------------------------
    // cosineSimilarity
    // ------------------------------------------------------------------

    #[Test]
    public function cosine_identik_bernilai_satu(): void
    {
        $p = $this->makeMahasiswa([
            'minat' => ['Programming', 'Desain'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ]);

        $a = $this->cbf->buildFeatureVector($p->profile);
        $b = $this->cbf->buildFeatureVector($p->profile);

        // Toleransi floating-point: cosine identik ≈ 1.0
        $this->assertEqualsWithDelta(1.0, $this->cbf->cosineSimilarity($a, $b), 1e-9);
    }

    #[Test]
    public function cosine_tidak_overlap_bernilai_nol(): void
    {
        $u1 = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ]);
        $u2 = $this->makeMahasiswa([
            'minat' => ['Data'],
            'tujuan' => 'Persiapan Ujian',
            'gaya' => 'Mandiri',
            'jadwal' => ['Selasa Pagi'],
            'mode' => 'Daring',
        ]);

        $a = $this->cbf->buildFeatureVector($u1->profile);
        $b = $this->cbf->buildFeatureVector($u2->profile);

        $this->assertSame(0.0, $this->cbf->cosineSimilarity($a, $b));
    }

    #[Test]
    public function cosine_zero_vector_aman_tidak_divide_by_zero(): void
    {
        // Vektor kosong (semua 0) tidak boleh menyebabkan division by zero.
        $empty = array_fill_keys(['minat:Programming', 'mode:Tatap Muka'], 0);

        $this->assertSame(0.0, $this->cbf->cosineSimilarity($empty, $empty));
    }

    #[Test]
    public function cosine_sebagian_overlap_bernilai_antara_nol_dan_satu(): void
    {
        $u1 = $this->makeMahasiswa([
            'minat' => ['Programming', 'Desain'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ]);
        // u2 hanya beda minat kedua & jadwal — mayoritas overlap
        $u2 = $this->makeMahasiswa([
            'minat' => ['Programming', 'Data'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi', 'Senin Sore'],
            'mode' => 'Tatap Muka',
        ]);

        $a = $this->cbf->buildFeatureVector($u1->profile);
        $b = $this->cbf->buildFeatureVector($u2->profile);
        $skor = $this->cbf->cosineSimilarity($a, $b);

        $this->assertGreaterThan(0.0, $skor);
        $this->assertLessThan(1.0, $skor);
    }

    // ------------------------------------------------------------------
    // buildFeatureVector
    // ------------------------------------------------------------------

    #[Test]
    public function build_vector_mengencode_multi_label_dan_one_hot(): void
    {
        $u = $this->makeMahasiswa([
            'minat' => ['Programming', 'Desain'], // multi-label: 2 dimensi aktif
            'tujuan' => 'Mengerjakan Tugas',      // one-hot: 1 dimensi
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi', 'Senin Sore'], // multi-label: 2 dimensi
            'mode' => 'Tatap Muka',
        ]);

        $v = $this->cbf->buildFeatureVector($u->profile);

        // Multi-label: kedua minat aktif
        $this->assertSame(1, $v['minat:Programming']);
        $this->assertSame(1, $v['minat:Desain']);
        $this->assertSame(0, $v['minat:Data']);

        // One-hot: hanya satu tujuan aktif
        $this->assertSame(1, $v['tujuan:Mengerjakan Tugas']);
        $this->assertSame(0, $v['tujuan:Persiapan Ujian']);

        // Multi-label jadwal
        $this->assertSame(1, $v['jadwal:Senin Pagi']);
        $this->assertSame(1, $v['jadwal:Senin Sore']);
        $this->assertSame(0, $v['jadwal:Selasa Pagi']);
    }

    // ------------------------------------------------------------------
    // isProfileLengkap
    // ------------------------------------------------------------------

    #[Test]
    public function profil_lengkap_didetect_benar(): void
    {
        $u = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ]);

        $this->assertTrue($this->cbf->isProfileLengkap($u->profile));
    }

    #[Test]
    public function profil_kurang_satu_atribut_tidak_lengkap(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif']);
        $profile = Profile::create([
            'user_id' => $user->id,
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => null, // kurang mode
            'whatsapp' => '08',
            'instagram' => '@',
        ]);

        $this->assertFalse($this->cbf->isProfileLengkap($profile));
    }

    // ------------------------------------------------------------------
    // calculateForUser
    // ------------------------------------------------------------------

    #[Test]
    public function calculate_for_user_menyimpan_skor_ke_dan_dari_kandidat(): void
    {
        $target = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ]);
        $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ]);

        // calculateForUser: forward (target → kandidat)
        $count = $this->cbf->calculateForUser($target);

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('similarity_scores', [
            'user_id' => $target->id,
        ]);
    }

    #[Test]
    public function calculate_for_user_profil_belum_lengkap_menghapus_skor_lama(): void
    {
        $target = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ]);
        $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ]);

        $this->cbf->calculateForUser($target);
        $this->assertNotEmpty($target->fresh()->similarityScores);

        // Profil jadi tidak lengkap
        $target->profile->update(['mode' => null]);
        $count = $this->cbf->calculateForUser($target->fresh());

        $this->assertSame(0, $count);
        $this->assertSame(0, $target->fresh()->similarityScores()->count());
    }

    // ------------------------------------------------------------------
    // getTopN + exclusion (Bug #1)
    // ------------------------------------------------------------------

    #[Test]
    public function get_top_n_urut_skor_tertinggi_dulu(): void
    {
        $target = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ]);

        // kandidatA = identik (skor 1.0)
        $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ], ['nama' => 'Kandidat Identik']);
        // kandidatB = sebagian overlap (skor < 1)
        $this->makeMahasiswa([
            'minat' => ['Programming', 'Data'],
            'tujuan' => 'Persiapan Ujian',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Daring',
        ], ['nama' => 'Kandidat Parsial']);

        $this->cbf->calculateForUser($target);

        $top = $this->cbf->getTopN($target, 10);

        $this->assertCount(2, $top);
        $this->assertSame(1.0, $top->first()->skor);
        // Urutan menurun
        $this->assertGreaterThanOrEqual($top->last()->skor, $top->first()->skor);
    }

    #[Test]
    public function get_top_n_filter_prodi_hanya_menampilkan_prodi_tersebut(): void
    {
        $fak = \App\Models\Fakultas::create(['nama' => 'Fakultas Ilmu Komputer', 'kode' => 'FIK']);
        $prodiTI = \App\Models\Prodi::create(['fakultas_id' => $fak->id, 'nama' => 'Teknik Informatika', 'kode' => 'IF', 'jenjang' => 'S1']);
        $prodiSI = \App\Models\Prodi::create(['fakultas_id' => $fak->id, 'nama' => 'Sistem Informasi', 'kode' => 'SI', 'jenjang' => 'S1']);

        $target = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ], ['prodi_id' => $prodiTI->id, 'fakultas_id' => $fak->id]);

        $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ], ['prodi_id' => $prodiTI->id, 'fakultas_id' => $fak->id, 'nama' => 'Se-Prodi']);
        $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ], ['prodi_id' => $prodiSI->id, 'fakultas_id' => $fak->id, 'nama' => 'Lain Prodi']);

        $this->cbf->calculateForUser($target);

        $filtered = $this->cbf->getTopN($target, 10, ['prodi_id' => $prodiTI->id]);

        $this->assertCount(1, $filtered);
        $this->assertSame('Se-Prodi', $filtered->first()->kandidat->nama);
    }

    #[Test]
    public function get_top_n_memfilter_kandidat_berdasarkan_jenis_kelamin_tanpa_mengganggu_cbf(): void
    {
        // Jenis kelamin HANYA filter post-Top-N, tidak ikut feature vector / perhitungan CBF.
        $target = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ]);

        $kL = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ], ['nama' => 'Kandidat L', 'jenis_kelamin' => 'L']);
        $kP = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ], ['nama' => 'Kandidat P', 'jenis_kelamin' => 'P']);

        $this->cbf->calculateForUser($target);

        $filteredL = $this->cbf->getTopN($target, 10, ['jenis_kelamin' => 'L']);
        $filteredP = $this->cbf->getTopN($target, 10, ['jenis_kelamin' => 'P']);

        $this->assertContains('Kandidat L', $filteredL->pluck('kandidat.nama')->toArray());
        $this->assertNotContains('Kandidat P', $filteredL->pluck('kandidat.nama')->toArray());

        $this->assertContains('Kandidat P', $filteredP->pluck('kandidat.nama')->toArray());
        $this->assertNotContains('Kandidat L', $filteredP->pluck('kandidat.nama')->toArray());
    }

    #[Test]
    public function get_top_n_tetap_menampilkan_kandidat_dengan_permintaan_pending(): void
    {
        $target = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ]);

        $kandidatPending = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ], ['nama' => 'Kandidat Pending']);
        $kandidatBebas = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ], ['nama' => 'Kandidat Bebas']);

        $this->cbf->calculateForUser($target);

        // Buat permintaan pending ke kandidatPending
        StudyRequest::create([
            'pengirim_id' => $target->id,
            'penerima_id' => $kandidatPending->id,
            'status' => 'pending',
            'waktu_kirim' => now(),
        ]);

        $top = $this->cbf->getTopN($target, 10);
        $namaKandidat = $top->pluck('kandidat.nama')->toArray();

        // Perilaku baru: kandidat pending TETAP muncul (status ditampilkan di kartu).
        $this->assertContains('Kandidat Pending', $namaKandidat);
        $this->assertContains('Kandidat Bebas', $namaKandidat);
    }

    #[Test]
    public function get_top_n_tetap_menampilkan_kandidat_dengan_permintaan_accepted(): void
    {
        $target = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ]);

        $kandidatAccepted = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ], ['nama' => 'Kandidat Accepted']);

        $this->cbf->calculateForUser($target);

        // Permintaan dari arah sebaliknya (kandidatAccepted → target), status accepted
        StudyRequest::create([
            'pengirim_id' => $kandidatAccepted->id,
            'penerima_id' => $target->id,
            'status' => 'accepted',
            'waktu_kirim' => now(),
        ]);

        $top = $this->cbf->getTopN($target, 10);

        // Perilaku baru: kandidat accepted TETAP muncul (status "Teman" ditampilkan).
        $this->assertContains('Kandidat Accepted', $top->pluck('kandidat.nama')->toArray());
    }

    #[Test]
    public function get_hubungan_kandidat_mengembalikan_status_sesuai_arah_dan_status(): void
    {
        $target = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ]);

        $kPending = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ], ['nama' => 'Pending']);
        $kAccepted = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ], ['nama' => 'Accepted']);
        $kRejected = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ], ['nama' => 'Rejected']);

        // Permintaan keluar: pending & rejected
        StudyRequest::create(['pengirim_id' => $target->id, 'penerima_id' => $kPending->id, 'status' => 'pending', 'waktu_kirim' => now()]);
        StudyRequest::create(['pengirim_id' => $target->id, 'penerima_id' => $kRejected->id, 'status' => 'rejected', 'waktu_kirim' => now()]);
        // Permintaan masuk: accepted (dari arah lawan)
        StudyRequest::create(['pengirim_id' => $kAccepted->id, 'penerima_id' => $target->id, 'status' => 'accepted', 'waktu_kirim' => now()]);

        $map = $this->cbf->getHubunganKandidat($target);

        $this->assertSame('pending', $map[$kPending->id] ?? null);
        $this->assertSame('accepted', $map[$kAccepted->id] ?? null);
        $this->assertSame('rejected', $map[$kRejected->id] ?? null);
    }

    #[Test]
    public function get_top_n_tidak_mengecualikan_kandidat_dengan_permintaan_rejected(): void
    {
        $target = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ]);

        $kandidatRejected = $this->makeMahasiswa([
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
        ], ['nama' => 'Kandidat Rejected']);

        $this->cbf->calculateForUser($target);

        StudyRequest::create([
            'pengirim_id' => $target->id,
            'penerima_id' => $kandidatRejected->id,
            'status' => 'rejected',
            'waktu_kirim' => now(),
        ]);

        $top = $this->cbf->getTopN($target, 10);

        // rejected BUKAN pasangan aktif → tetap muncul (bisa kirim ulang)
        $this->assertContains('Kandidat Rejected', $top->pluck('kandidat.nama')->toArray());
    }
}
