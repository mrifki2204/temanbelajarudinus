<?php

namespace Tests\Feature;

use App\Models\Fakultas;
use App\Models\Profile;
use App\Models\Prodi;
use App\Models\StudyRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    private ?int $fakultasId = null;
    private ?int $prodiId = null;

    private function seedOnce(): void
    {
        if ($this->fakultasId) {
            return;
        }
        $f = Fakultas::create(['nama' => 'Fakultas Ilmu Komputer', 'kode' => 'FIK']);
        $p = Prodi::create(['fakultas_id' => $f->id, 'nama' => 'Teknik Informatika', 'kode' => 'IF', 'jenjang' => 'S1']);
        $this->fakultasId = $f->id;
        $this->prodiId = $p->id;
    }

    private function mhs(string $nama = 'Mhs'): User
    {
        $this->seedOnce();
        $u = User::factory()->create([
            'role' => 'mahasiswa', 'status' => 'aktif', 'nama' => $nama,
            'fakultas_id' => $this->fakultasId, 'prodi_id' => $this->prodiId,
        ]);
        Profile::create([
            'user_id' => $u->id, 'minat' => ['Programming'], 'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi', 'jadwal' => ['Senin Pagi'], 'mode' => 'Tatap Muka',
            'whatsapp' => '08', 'instagram' => '@t',
        ]);

        return $u->fresh('profile');
    }

    public function test_profil_edit_render(): void
    {
        $this->actingAs($this->mhs('Ari'))->get(route('profil.edit'))->assertOk();
    }

    public function test_permintaan_index_render(): void
    {
        $u = $this->mhs('Ari');
        $k = $this->mhs('Budi');
        StudyRequest::create(['pengirim_id' => $u->id, 'penerima_id' => $k->id, 'status' => 'accepted', 'waktu_kirim' => now()]);
        $this->actingAs($u)->get(route('permintaan.index'))->assertOk();
    }

    public function test_rekomendasi_show_render(): void
    {
        $u = $this->mhs('Ari');
        $k = $this->mhs('Budi');
        $this->actingAs($u)->get(route('rekomendasi.show', $k->id))->assertOk();
    }

    public function test_admin_mahasiswa_show_render(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $m = $this->mhs();
        $this->actingAs($admin)->get(route('admin.mahasiswa.show', $m))->assertOk();
    }

    public function test_admin_mahasiswa_edit_render(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $m = $this->mhs();
        $this->actingAs($admin)->get(route('admin.mahasiswa.edit', $m))->assertOk();
    }

    public function test_setting_render(): void
    {
        $this->actingAs($this->mhs())->get(route('setting.index'))->assertOk();
    }

    public function test_aktivitas_mahasiswa_render(): void
    {
        $this->actingAs($this->mhs())->get(route('aktivitas.index'))->assertOk();
    }

    public function test_admin_aktivitas_render(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $this->actingAs($admin)->get(route('admin.aktivitas.index'))->assertOk();
    }
}
