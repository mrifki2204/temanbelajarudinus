<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif']);

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_tidak_enumerasi_email_tidak_terdaftar(): void
    {
        Notification::fake();

        $response = $this->from('/forgot-password')
            ->post('/forgot-password', ['email' => 'tidakada@mhs.dinus.ac.id']);

        // Response sama (sukses generik) — tidak bocorkan apakah email ada
        $response->assertSessionHas('status');
        $response->assertSessionDoesntHaveErrors('email');
        Notification::assertNothingSent();
    }

    public function test_reset_password_tidak_dikirim_ke_admin_atau_nonaktif(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $nonaktif = User::factory()->create(['role' => 'mahasiswa', 'status' => 'nonaktif']);

        $this->post('/forgot-password', ['email' => $admin->email]);
        $this->post('/forgot-password', ['email' => $nonaktif->email]);

        Notification::assertNothingSent();
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif']);

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif']);

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }
}
