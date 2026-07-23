<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertSee('Login Administrator');
        $response->assertSee('Panel Admin');
    }

    public function test_admin_can_login_via_admin_route(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_mahasiswa_cannot_login_via_admin_route(): void
    {
        $mhs = User::factory()->create([
            'role' => 'mahasiswa',
            'status' => 'aktif',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => $mhs->email,
            'password' => 'password',
        ]);

        // Mahasiswa ditolak & tidak ter-auth
        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_logged_in_admin_redirected_from_login_screen(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $response = $this->actingAs($admin)->get('/admin/login');
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }
}
