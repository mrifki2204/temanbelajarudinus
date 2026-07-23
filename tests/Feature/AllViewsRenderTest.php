<?php

namespace Tests\Feature;

use App\Models\OpsiPreferensi;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Smoke test: pastikan semua halaman yang diredesain render 200 tanpa error.
 */
class AllViewsRenderTest extends TestCase
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
                OpsiPreferensi::updateOrCreate(['tipe' => $tipe, 'nilai' => $nilai], ['tipe' => $tipe, 'nilai' => $nilai]);
            }
        }
        // Seed fakultas & prodi minimal untuk filter
        $fak = \App\Models\Fakultas::create(['kode' => 'FTIK', 'nama' => 'Fakultas Ilmu Komputer']);
        \App\Models\Prodi::create(['fakultas_id' => $fak->id, 'kode' => 'IF', 'nama' => 'Teknik Informatika', 'jenjang' => 'S1']);
    }

    private function profilLengkap(): array
    {
        return [
            'minat' => ['Coding & Programming'], 'tujuan' => 'Ngerjain Tugas', 'gaya' => 'Diskusi Bareng',
            'jadwal' => ['Senin Pagi (06-11)'], 'mode' => 'Tatap Muka', 'whatsapp' => '08123', 'instagram' => '@test',
        ];
    }

    private function makeMahasiswa(array $attr = []): User
    {
        $u = User::factory()->create(array_merge([
            'role' => 'mahasiswa', 'status' => 'aktif',
            'nim' => 'A12345', 'semester' => 3, 'angkatan' => 2023,
        ], $attr));
        Profile::create(array_merge(['user_id' => $u->id], $this->profilLengkap()));
        return $u->fresh('profile');
    }

    #[Test]
    public function semua_halaman_mahasiswa_render_200(): void
    {
        $user = $this->makeMahasiswa();
        $kandidat = $this->makeMahasiswa(['nama' => 'Partner', 'email' => 'p@example.com', 'nim' => 'B67890']);

        $routes = [
            route('dashboard'),
            route('profil.edit'),
            route('rekomendasi.index'),
            route('rekomendasi.show', $kandidat->id),
            route('permintaan.index'),
            route('setting.index'),
            route('aktivitas.index'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($user)->get($url)->assertStatus(200, "Gagal render: {$url}");
        }
    }

    #[Test]
    public function rekomendasi_show_tidak_pakai_hero_gradient(): void
    {
        $user = $this->makeMahasiswa();
        $kandidat = $this->makeMahasiswa(['nama' => 'Partner', 'email' => 'p@example.com', 'nim' => 'B67890']);

        $res = $this->actingAs($user)->get(route('rekomendasi.show', $kandidat->id));
        $res->assertStatus(200);
        // Hero gradient berat sudah dihapus — tidak boleh ada class .tb-hero
        $this->assertStringNotContainsString('tb-hero"', $res->content());
    }

    #[Test]
    public function semua_halaman_admin_render_200(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif', 'nama' => 'Admin']);

        $routes = [
            route('admin.dashboard'),
            route('admin.mahasiswa.index'),
            route('admin.fakultas.index'),
            route('admin.fakultas.create'),
            route('admin.prodi.index'),
            route('admin.prodi.create'),
            route('admin.opsi.index'),
            route('admin.opsi.create'),
            route('admin.opsi.index', ['tipe' => 'jadwal']),
            route('admin.opsi.create', ['tipe' => 'jadwal']),
            route('admin.opsi.index', ['tipe' => 'minat']),
            route('admin.aktivitas.index'),
        ];

        // Edit mahasiswa butuh record mhs yang ada
        $mhs = User::where('role', 'mahasiswa')->first();
        if ($mhs) {
            $routes[] = route('admin.mahasiswa.edit', $mhs);
        }

        foreach ($routes as $url) {
            $this->actingAs($admin)->get($url)->assertStatus(200, "Gagal render: {$url}");
        }
    }

    #[Test]
    public function admin_mahasiswa_show_render_200(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $mhs = $this->makeMahasiswa();

        $this->actingAs($admin)->get(route('admin.mahasiswa.show', $mhs->id))->assertStatus(200);
    }
}
