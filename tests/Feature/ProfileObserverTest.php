<?php

namespace Tests\Feature;

use App\Models\OpsiPreferensi;
use App\Models\Profile;
use App\Models\StudyRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature test untuk ProfileObserver + PermintaanStoreRequest.
 *
 * Menguji:
 * - Observer: forward recalc (owner → kandidat) saat profil disimpan
 * - Observer: reverse recalc (user lain → owner) saat user baru dibuat
 * - Observer: hapus skor saat profil dihapus / menjadi tidak lengkap
 * - PermintaanStoreRequest: tolak diri sendiri, duplikat, profil belum lengkap
 */
class ProfileObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOpsi();
    }

    private function seedOpsi(): void
    {
        $opsi = [
            ['tipe' => 'minat', 'nilai' => 'Programming'],
            ['tipe' => 'minat', 'nilai' => 'Desain'],
            ['tipe' => 'tujuan', 'nilai' => 'Mengerjakan Tugas'],
            ['tipe' => 'gaya', 'nilai' => 'Diskusi'],
            ['tipe' => 'jadwal', 'nilai' => 'Senin Pagi'],
            ['tipe' => 'mode', 'nilai' => 'Tatap Muka'],
        ];
        foreach ($opsi as $o) {
            OpsiPreferensi::create($o);
        }
    }

    private function profilLengkap(): array
    {
        return [
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
            'whatsapp' => '08123',
            'instagram' => '@test',
        ];
    }

    private function makeMahasiswa(?array $preferensi = null): User
    {
        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif']);
        Profile::create(array_merge(['user_id' => $user->id], $preferensi ?? $this->profilLengkap()));

        return $user->fresh('profile');
    }

    // ------------------------------------------------------------------
    // Observer: forward + reverse
    // ------------------------------------------------------------------

    #[Test]
    public function observer_menyimpan_skor_forward_saat_profil_lengkap_disimpan(): void
    {
        $target = $this->makeMahasiswa();
        $kandidat = $this->makeMahasiswa();

        // Update profil target (memenuhi syarat: ada kandidat lain lengkap) memicu observer
        $target->profile->update(['gaya' => 'Diskusi']);

        $this->assertDatabaseHas('similarity_scores', [
            'user_id' => $target->id,
            'kandidat_id' => $kandidat->id,
        ]);
    }

    #[Test]
    public function observer_menyimpan_skor_reverse_saat_user_baru_dibuat(): void
    {
        // User lama dulu
        $lama = $this->makeMahasiswa();

        // Saat user lama dibuat, belum ada user baru → skornya hanya forward.
        // Sekarang buat user baru (lengkap) → observer user baru harus mengisi
        // skor reverse: lama → baru.
        $baru = $this->makeMahasiswa();

        // Skor reverse (lama → baru) harus terisi saat user baru dibuat.
        $this->assertDatabaseHas('similarity_scores', [
            'user_id' => $lama->id,
            'kandidat_id' => $baru->id,
        ]);
    }

    #[Test]
    public function observer_menghapus_skor_saat_profil_dihapus(): void
    {
        $target = $this->makeMahasiswa();
        $kandidat = $this->makeMahasiswa();

        $target->profile->update(['gaya' => 'Diskusi']); // picu recalc
        $this->assertDatabaseHas('similarity_scores', ['user_id' => $target->id]);

        $target->profile->delete();

        $this->assertDatabaseMissing('similarity_scores', ['user_id' => $target->id]);
        $this->assertDatabaseMissing('similarity_scores', ['kandidat_id' => $target->id]);
    }

    #[Test]
    public function observer_menghapus_skor_saat_profil_jadi_tidak_lengkap(): void
    {
        $target = $this->makeMahasiswa();
        $this->makeMahasiswa();

        $target->profile->update(['gaya' => 'Diskusi']);
        $this->assertDatabaseHas('similarity_scores', ['user_id' => $target->id]);

        // Jadikan profil tidak lengkap (hapus satu atribut wajib)
        $target->profile->update(['mode' => null]);

        $this->assertDatabaseMissing('similarity_scores', ['user_id' => $target->id]);
    }

    #[Test]
    public function observer_tidak_menghitung_skor_untuk_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        Profile::create(array_merge(['user_id' => $admin->id], $this->profilLengkap()));

        // Tidak ada baris similarity_scores dengan user_id atau kandidat_id = admin
        $this->assertDatabaseMissing('similarity_scores', ['user_id' => $admin->id]);
        $this->assertDatabaseMissing('similarity_scores', ['kandidat_id' => $admin->id]);
    }

    #[Test]
    public function observer_tidak_menghitung_skor_untuk_user_nonaktif(): void
    {
        $nonaktif = User::factory()->create(['role' => 'mahasiswa', 'status' => 'nonaktif']);
        Profile::create(array_merge(['user_id' => $nonaktif->id], $this->profilLengkap()));

        $this->assertDatabaseMissing('similarity_scores', ['user_id' => $nonaktif->id]);
        $this->assertDatabaseMissing('similarity_scores', ['kandidat_id' => $nonaktif->id]);
    }

    // ------------------------------------------------------------------
    // PermintaanStoreRequest
    // ------------------------------------------------------------------

    #[Test]
    public function permintaan_ditolak_jika_kirim_ke_diri_sendiri(): void
    {
        $user = $this->makeMahasiswa();

        $response = $this->actingAs($user)->post(route('permintaan.store'), [
            'penerima_id' => $user->id,
        ]);

        $response->assertSessionHasErrors(['penerima_id']);
    }

    #[Test]
    public function permintaan_ditolak_jika_duplikat_aktif(): void
    {
        $pengirim = $this->makeMahasiswa();
        $penerima = $this->makeMahasiswa();

        StudyRequest::create([
            'pengirim_id' => $pengirim->id,
            'penerima_id' => $penerima->id,
            'status' => 'pending',
            'waktu_kirim' => now(),
        ]);

        $response = $this->actingAs($pengirim)->post(route('permintaan.store'), [
            'penerima_id' => $penerima->id,
        ]);

        $response->assertSessionHasErrors(['penerima_id']);
    }

    #[Test]
    public function permintaan_diterima_jika_belum_ada_duplikat(): void
    {
        $pengirim = $this->makeMahasiswa();
        $penerima = $this->makeMahasiswa();

        $response = $this->actingAs($pengirim)->post(route('permintaan.store'), [
            'penerima_id' => $penerima->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('study_requests', [
            'pengirim_id' => $pengirim->id,
            'penerima_id' => $penerima->id,
            'status' => 'pending',
        ]);
    }
}
