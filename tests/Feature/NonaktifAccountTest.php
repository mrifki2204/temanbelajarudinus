<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\SimilarityScore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NonaktifAccountTest extends TestCase
{
    use RefreshDatabase;

    private function makeMahasiswa(array $attrs = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => 'mahasiswa',
            'status' => 'aktif',
        ], $attrs));

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

    public function test_login_ditolak_jika_akun_nonaktif(): void
    {
        $user = $this->makeMahasiswa(['status' => 'nonaktif', 'password' => 'password']);

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_session_nonaktif_di_kick_dari_dashboard(): void
    {
        $user = $this->makeMahasiswa();

        $this->actingAs($user);
        $this->assertAuthenticated();

        // Simulasikan admin menonaktifkan di tengah session
        $user->update(['status' => 'nonaktif']);

        $response = $this->get(route('dashboard'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_toggle_nonaktif_hapus_skor_dan_session(): void
    {
        // Seed opsi agar ProfileObserver bisa hitung skor saat profil dibuat
        foreach (['Programming' => 'minat', 'Mengerjakan Tugas' => 'tujuan', 'Diskusi' => 'gaya', 'Senin Pagi' => 'jadwal', 'Tatap Muka' => 'mode'] as $nilai => $tipe) {
            \App\Models\OpsiPreferensi::firstOrCreate(['tipe' => $tipe, 'nilai' => $nilai]);
        }

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $mhs = $this->makeMahasiswa();
        $other = $this->makeMahasiswa();

        // Observer sudah menulis skor forward+reverse saat profil dibuat.
        // Pastikan pair skor ada (jika belum, upsert).
        SimilarityScore::updateOrCreate(
            ['user_id' => $mhs->id, 'kandidat_id' => $other->id],
            ['skor' => 0.9]
        );
        SimilarityScore::updateOrCreate(
            ['user_id' => $other->id, 'kandidat_id' => $mhs->id],
            ['skor' => 0.9]
        );

        $this->assertTrue(SimilarityScore::where('user_id', $mhs->id)->orWhere('kandidat_id', $mhs->id)->exists());

        // Fake session row
        DB::table('sessions')->insert([
            'id' => 'test-session-'.$mhs->id,
            'user_id' => $mhs->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'x',
            'last_activity' => time(),
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.mahasiswa.toggle-status', $mhs));

        $response->assertRedirect();
        $this->assertSame('nonaktif', $mhs->fresh()->status);
        $this->assertDatabaseMissing('similarity_scores', ['user_id' => $mhs->id]);
        $this->assertDatabaseMissing('similarity_scores', ['kandidat_id' => $mhs->id]);
        $this->assertDatabaseMissing('sessions', ['user_id' => $mhs->id]);
    }

    public function test_toggle_aktif_kembali_hitung_forward_dan_reverse(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $mhs = $this->makeMahasiswa(['status' => 'nonaktif']);
        $other = $this->makeMahasiswa();

        // Pastikan opsi preferensi ada agar CBF bisa build vector
        foreach (['Programming' => 'minat', 'Mengerjakan Tugas' => 'tujuan', 'Diskusi' => 'gaya', 'Senin Pagi' => 'jadwal', 'Tatap Muka' => 'mode'] as $nilai => $tipe) {
            \App\Models\OpsiPreferensi::firstOrCreate(['tipe' => $tipe, 'nilai' => $nilai]);
        }

        $response = $this->actingAs($admin)
            ->patch(route('admin.mahasiswa.toggle-status', $mhs));

        $response->assertRedirect();
        $this->assertSame('aktif', $mhs->fresh()->status);

        // Forward: mhs → other
        $this->assertTrue(
            SimilarityScore::where('user_id', $mhs->id)->where('kandidat_id', $other->id)->exists()
        );
        // Reverse: other → mhs
        $this->assertTrue(
            SimilarityScore::where('user_id', $other->id)->where('kandidat_id', $mhs->id)->exists()
        );
    }

    public function test_admin_tidak_bisa_akses_profil_mahasiswa(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);

        $response = $this->actingAs($admin)->get(route('profil.edit'));

        $response->assertForbidden();
    }
}
