<?php

namespace Tests\Feature;

use App\Models\Fakultas;
use App\Models\Profile;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
    }

    private function makeMahasiswa(): User
    {
        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif']);
        Profile::create([
            'user_id' => $user->id,
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
            'whatsapp' => '08123',
            'instagram' => '@test',
        ]);

        return $user->fresh('profile');
    }

    public function test_mahasiswa_tidak_bisa_akses_area_admin(): void
    {
        $mahasiswa = $this->makeMahasiswa();

        // Akses dashboard admin → redirect ke dashboard mahasiswa (bukan 403)
        $response = $this->actingAs($mahasiswa)->get(route('admin.dashboard'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_admin_tidak_bisa_akses_area_mahasiswa(): void
    {
        $admin = $this->makeAdmin();

        // Akses rekomendasi (khusus mahasiswa) → 403
        $response = $this->actingAs($admin)->get(route('rekomendasi.index'));

        $response->assertForbidden();
    }

    public function test_tanpa_login_tidak_bisa_akses_admin(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_bisa_akses_dashboard_admin(): void
    {
        // Seed fakultas/prodi agar dashboard render distribusi
        Fakultas::create(['nama' => 'Fakultas Ilmu Komputer', 'kode' => 'FIK']);
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
    }
}
