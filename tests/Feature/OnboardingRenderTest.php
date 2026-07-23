<?php

namespace Tests\Feature;

use App\Models\OpsiPreferensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Test render halaman onboarding profil (pasca-register).
 * Memastikan halaman redesain bisa dirender tanpa error & elemen kunci ada.
 */
class OnboardingRenderTest extends TestCase
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
            'minat' => ['Coding & Programming', 'Data & Statistik', 'Desain & Multimedia'],
            'tujuan' => ['Belajar UTS/UAS', 'Ngerjain Tugas'],
            'gaya' => ['Diskusi Bareng', 'Belajar Sendiri'],
            'jadwal' => [
                'Senin Pagi (06-11)', 'Senin Sore (14-18)', 'Selasa Malam (18-23)',
                'Sabtu Siang (11-14)', 'Minggu Siang (11-14)',
            ],
            'mode' => ['Online', 'Tatap Muka', 'Fleksibel'],
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

    #[Test]
    public function mahasiswa_baru_melihat_halaman_onboarding(): void
    {
        $user = User::factory()->create([
            'role' => 'mahasiswa',
            'status' => 'aktif',
            'nama' => 'Budi Santoso',
        ]);

        $response = $this->actingAs($user)->get(route('profil.edit'));

        $response->assertStatus(200);
        $response->assertSee('Lengkapi Preferensimu');
        $response->assertSee('sesuai kondisi aslimu');
        // 6 section terpisah
        $response->assertSee('Minat Bidang Belajar');
        $response->assertSee('Tujuan Belajar');
        $response->assertSee('Gaya Belajar');
        $response->assertSee('Jadwal Luang');
        $response->assertSee('Mode Belajar');
        $response->assertSee('Kontak');
        // Tombol simpan
        $response->assertSee('Simpan & Lihat Rekomendasi');
        // Slot Siang Sabtu & Minggu kini tersedia (sebelumnya —)
        $response->assertSee('Sabtu Siang (11-14)');
        $response->assertSee('Minggu Siang (11-14)');
    }

    #[Test]
    public function form_onboarding_submit_ke_route_update_dengan_field_lengkap(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif']);

        $response = $this->actingAs($user)->from(route('profil.edit'))->put(route('profil.update'), [
            'minat' => ['Coding & Programming'],
            'tujuan' => 'Ngerjain Tugas',
            'gaya' => 'Diskusi Bareng',
            'jadwal' => ['Senin Pagi (06-11)'],
            'mode' => 'Tatap Muka',
            'whatsapp' => '081234567890',
            'instagram' => '@budi',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'tujuan' => 'Ngerjain Tugas',
            'mode' => 'Tatap Muka',
        ]);
    }
}
