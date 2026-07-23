<?php

namespace Tests\Feature\Auth;

use App\Models\Fakultas;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        // Seed fakultas & prodi untuk dropdown
        $fakultas = Fakultas::create(['nama' => 'Ilmu Komputer', 'kode' => 'FIK']);
        $prodi = Prodi::create(['nama' => 'Teknik Informatika', 'kode' => 'IF', 'jenjang' => 'S1', 'fakultas_id' => $fakultas->id]);

        $response = $this->post('/register', [
            'nama' => 'Test User',
            'jenis_kelamin' => 'L',
            'nim' => 'A11.2021.13840',
            'email' => '111202113840@mhs.dinus.ac.id',
            'password' => 'password',
            'password_confirmation' => 'password',
            'fakultas_id' => $fakultas->id,
            'prodi_id' => $prodi->id,
            'semester' => 5,
            'angkatan' => 2021,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('profil.edit', absolute: false));
    }

    public function test_nim_format_validated(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Ilmu Komputer', 'kode' => 'FIK']);
        $prodi = Prodi::create(['nama' => 'Teknik Informatika', 'kode' => 'IF', 'jenjang' => 'S1', 'fakultas_id' => $fakultas->id]);

        // NIM tanpa titik harus ditolak
        $response = $this->post('/register', [
            'nama' => 'Test User',
            'jenis_kelamin' => 'L',
            'nim' => 'A11202113840',
            'email' => '111202113841@mhs.dinus.ac.id',
            'password' => 'password',
            'password_confirmation' => 'password',
            'fakultas_id' => $fakultas->id,
            'prodi_id' => $prodi->id,
            'semester' => 5,
            'angkatan' => 2021,
        ]);

        $response->assertSessionHasErrors('nim');
        $this->assertGuest();
    }

    public function test_email_domain_validated(): void
    {
        $fakultas = Fakultas::create(['nama' => 'Ilmu Komputer', 'kode' => 'FIK']);
        $prodi = Prodi::create(['nama' => 'Teknik Informatika', 'kode' => 'IF', 'jenjang' => 'S1', 'fakultas_id' => $fakultas->id]);

        // Email non-UDINUS harus ditolak
        $response = $this->post('/register', [
            'nama' => 'Test User',
            'jenis_kelamin' => 'L',
            'nim' => 'A11.2021.13842',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'fakultas_id' => $fakultas->id,
            'prodi_id' => $prodi->id,
            'semester' => 5,
            'angkatan' => 2021,
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
