<?php
namespace Tests\Feature;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MahasiswaIndexActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_mahasiswa_render_dengan_akun_aktif_dan_nonaktif(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $f = Fakultas::create(['nama' => 'Fakultas Ilmu Komputer', 'kode' => 'FIK']);
        $p = Prodi::create(['fakultas_id' => $f->id, 'nama' => 'Teknik Informatika', 'kode' => 'IF', 'jenjang' => 'S1']);

        User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif', 'nama' => 'Aktif User', 'fakultas_id' => $f->id, 'prodi_id' => $p->id]);
        User::factory()->create(['role' => 'mahasiswa', 'status' => 'nonaktif', 'nama' => 'Nonaktif User', 'fakultas_id' => $f->id, 'prodi_id' => $p->id]);

        $response = $this->actingAs($admin)->get(route('admin.mahasiswa.index'));

        $response->assertOk();
        // Tidak ada tombol disabled — semua aksi aktif
        $response->assertDontSee('disabled', false);
        // Label aksi lengkap
        $response->assertSee('Lihat detail');
        $response->assertSee('Nonaktifkan');
        $response->assertSee('Aktifkan');
        $response->assertSee('Hapus permanen');
    }

    public function test_admin_bisa_hapus_akun_aktif_langsung(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $f = Fakultas::create(['nama' => 'Fakultas Ilmu Komputer', 'kode' => 'FIK']);
        $p = Prodi::create(['fakultas_id' => $f->id, 'nama' => 'Teknik Informatika', 'kode' => 'IF', 'jenjang' => 'S1']);
        $mhs = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif', 'fakultas_id' => $f->id, 'prodi_id' => $p->id]);

        $response = $this->actingAs($admin)->delete(route('admin.mahasiswa.destroy', $mhs));

        $response->assertRedirect(route('admin.mahasiswa.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $mhs->id]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'mahasiswa.delete']);
    }

    public function test_admin_bisa_hapus_akun_nonaktif(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $f = Fakultas::create(['nama' => 'Fakultas Ilmu Komputer', 'kode' => 'FIK']);
        $p = Prodi::create(['fakultas_id' => $f->id, 'nama' => 'Teknik Informatika', 'kode' => 'IF', 'jenjang' => 'S1']);
        $mhs = User::factory()->create(['role' => 'mahasiswa', 'status' => 'nonaktif', 'fakultas_id' => $f->id, 'prodi_id' => $p->id]);

        $response = $this->actingAs($admin)->delete(route('admin.mahasiswa.destroy', $mhs));

        $response->assertRedirect(route('admin.mahasiswa.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $mhs->id]);
    }
}
