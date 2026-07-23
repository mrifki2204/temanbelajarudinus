<?php

namespace Tests\Feature;

use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
    }

    // === FAKULTAS ===

    public function test_admin_bisa_menambah_fakultas(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.fakultas.store'), [
            'nama' => 'Fakultas Uji Coba',
            'kode' => 'FUC',
        ]);

        $response->assertRedirect(route('admin.fakultas.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('fakultas', ['nama' => 'Fakultas Uji Coba', 'kode' => 'FUC']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'fakultas.create']);
    }

    public function test_tambah_fakultas_dengan_kode_duplikat_ditolak(): void
    {
        $admin = $this->makeAdmin();
        Fakultas::create(['nama' => 'Fakultas A', 'kode' => 'FA']);

        $response = $this->actingAs($admin)->post(route('admin.fakultas.store'), [
            'nama' => 'Fakultas B',
            'kode' => 'FA',
        ]);

        $response->assertSessionHasErrors('kode');
    }

    public function test_admin_bisa_menghapus_fakultas_tanpa_prodi(): void
    {
        $admin = $this->makeAdmin();
        $fakultas = Fakultas::create(['nama' => 'Fakultas Hapus', 'kode' => 'FH']);

        $response = $this->actingAs($admin)->delete(route('admin.fakultas.destroy', $fakultas));

        $response->assertRedirect(route('admin.fakultas.index'));
        $this->assertDatabaseMissing('fakultas', ['id' => $fakultas->id]);
    }

    public function test_hapus_fakultas_dengan_prodi_ditolak(): void
    {
        $admin = $this->makeAdmin();
        $fakultas = Fakultas::create(['nama' => 'Fakultas Prodi', 'kode' => 'FP']);
        Prodi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Prodi X', 'kode' => 'PX', 'jenjang' => 'S1']);

        $response = $this->actingAs($admin)->delete(route('admin.fakultas.destroy', $fakultas));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('fakultas', ['id' => $fakultas->id]);
    }

    // === PRODI ===

    public function test_admin_bisa_menambah_prodi(): void
    {
        $admin = $this->makeAdmin();
        $fakultas = Fakultas::create(['nama' => 'Fakultas Prodi', 'kode' => 'FP']);

        $response = $this->actingAs($admin)->post(route('admin.prodi.store'), [
            'fakultas_id' => $fakultas->id,
            'nama' => 'Prodi Baru',
            'kode' => 'PBN',
            'jenjang' => 'S1',
        ]);

        $response->assertRedirect(route('admin.prodi.index'));
        $this->assertDatabaseHas('prodi', ['nama' => 'Prodi Baru', 'kode' => 'PBN']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'prodi.create']);
    }

    public function test_hapus_prodi_dengan_mahasiswa_ditolak(): void
    {
        $admin = $this->makeAdmin();
        $fakultas = Fakultas::create(['nama' => 'Fakultas Prodi', 'kode' => 'FP']);
        $prodi = Prodi::create(['fakultas_id' => $fakultas->id, 'nama' => 'Prodi Terpakai', 'kode' => 'PT', 'jenjang' => 'S1']);
        User::factory()->create(['prodi_id' => $prodi->id, 'fakultas_id' => $fakultas->id]);

        $response = $this->actingAs($admin)->delete(route('admin.prodi.destroy', $prodi));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('prodi', ['id' => $prodi->id]);
    }
}
