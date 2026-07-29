<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    private function makeMahasiswaWithProfile(): User
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

    public function test_halaman_setting_render_200(): void
    {
        $user = $this->makeMahasiswaWithProfile();

        $response = $this->actingAs($user)->get(route('setting.index'));

        $response->assertOk();
    }

    public function test_mahasiswa_bisa_ubah_password_dengan_password_lama_benar(): void
    {
        $user = $this->makeMahasiswaWithProfile();

        $response = $this->actingAs($user)
            ->from(route('setting.index'))
            ->put(route('profil.password.update'), [
                'current_password' => 'password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ]);

        $response->assertRedirect(route('setting.index'));
        $response->assertSessionHas('success');
        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('new-password-123', $user->fresh()->password)
        );
    }

    public function test_ubah_password_ditolak_jika_password_lama_salah(): void
    {
        $user = $this->makeMahasiswaWithProfile();

        $response = $this->actingAs($user)
            ->from(route('setting.index'))
            ->put(route('profil.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ]);

        $response->assertSessionHasErrorsIn('updatePassword');
        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('password', $user->fresh()->password)
        );
    }

    public function test_endpoint_hapus_akun_sudah_dihapus(): void
    {
        $user = $this->makeMahasiswaWithProfile();

        // Route profil.destroy dihapus bersama UI "Hapus Akun".
        $response = $this->actingAs($user)
            ->delete('/setting', ['password' => 'password']);

        $response->assertStatus(405); // method not allowed / no DELETE route
        $this->assertNotNull($user->fresh());
    }
}
