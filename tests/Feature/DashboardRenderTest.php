<?php

namespace Tests\Feature;

use App\Models\OpsiPreferensi;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Test render halaman dashboard setelah redesain.
 * Menguji 3 skenario: mahasiswa profil lengkap, profil belum lengkap, dan admin.
 */
class DashboardRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOpsi();
    }

    private function seedOpsi(): void
    {
        $data = [
            'minat' => ['Coding & Programming', 'Data & Statistik'],
            'tujuan' => ['Ngerjain Tugas'],
            'gaya' => ['Diskusi Bareng'],
            'jadwal' => ['Senin Pagi (06-11)'],
            'mode' => ['Tatap Muka'],
        ];
        foreach ($data as $tipe => $nilaiList) {
            foreach ($nilaiList as $nilai) {
                OpsiPreferensi::updateOrCreate(
                    ['tipe' => $tipe, 'nilai' => $nilai],
                    ['tipe' => $tipe, 'nilai' => $nilai]
                );
            }
        }
    }

    private function profilLengkap(): array
    {
        return [
            'minat' => ['Coding & Programming'],
            'tujuan' => 'Ngerjain Tugas',
            'gaya' => 'Diskusi Bareng',
            'jadwal' => ['Senin Pagi (06-11)'],
            'mode' => 'Tatap Muka',
            'whatsapp' => '08123',
            'instagram' => '@test',
        ];
    }

    #[Test]
    public function mahasiswa_profil_lengkap_melihat_dashboard_penuh(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif', 'nama' => 'Ari Wijaya']);
        Profile::create(array_merge(['user_id' => $user->id], $this->profilLengkap()));

        // Buat 1 kandidat lain agar rekomendasi tidak kosong
        $kandidat = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif', 'nama' => 'Partner']);
        Profile::create(array_merge(['user_id' => $kandidat->id], $this->profilLengkap()));

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        // Greeting (sapaan kontekstual + nama)
        $response->assertSee('Ari!');
        $response->assertSee('Profil Lengkap');
        // Stats (tanpa Aksi Cepat — sudah dihapus)
        $response->assertDontSee('Aksi Cepat');
        $response->assertSee('Kandidat');
        $response->assertSee('Permintaan Masuk');
        // Rekomendasi read-only + tombol lihat semua
        $response->assertSee('Rekomendasi untukmu');
        $response->assertSee('Lihat semua');
        // Profil saya
        $response->assertSee('Profil Saya');
        $response->assertSee('Ari Wijaya');
    }

    #[Test]
    public function mahasiswa_profil_belum_lengkap_melihat_empty_state_rekomendasi(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif', 'nama' => 'Budi']);
        // Tidak buat profile → profil belum lengkap

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Lengkapi Profil'); // chip status warn
        $response->assertSee('Lengkapi profil dulu'); // empty state rekomendasi
        $response->assertSee('Isi preferensi belajar-mu');
    }

    #[Test]
    public function admin_melihat_panel_admin_tidak_crash(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif', 'nama' => 'Admin']);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Panel Admin');
        $response->assertSee('Buka Dashboard Admin');
        // Tidak boleh muncul section mahasiswa
        $response->assertDontSee('Rekomendasi untukmu');
        $response->assertDontSee('Profil Saya');
    }

    #[Test]
    public function statistik_permintaan_masuk_menampilkan_angka_pending(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif']);
        Profile::create(array_merge(['user_id' => $user->id], $this->profilLengkap()));

        // Buat 2 permintaan pending masuk
        $pengirim1 = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif']);
        $pengirim2 = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif']);
        \App\Models\StudyRequest::create(['pengirim_id' => $pengirim1->id, 'penerima_id' => $user->id, 'status' => 'pending', 'waktu_kirim' => now()]);
        \App\Models\StudyRequest::create(['pengirim_id' => $pengirim2->id, 'penerima_id' => $user->id, 'status' => 'pending', 'waktu_kirim' => now()]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Permintaan Masuk'); // label stat
    }
}
