<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\StudyRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeMahasiswa(string $nama = 'Mahasiswa'): User
    {
        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif', 'nama' => $nama]);
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

    public function test_pengirim_bisa_mengirim_permintaan(): void
    {
        [$pengirim, $penerima] = [$this->makeMahasiswa('Pengirim'), $this->makeMahasiswa('Penerima')];

        $response = $this->actingAs($pengirim)
            ->post(route('permintaan.store'), ['penerima_id' => $penerima->id]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('study_requests', [
            'pengirim_id' => $pengirim->id,
            'penerima_id' => $penerima->id,
            'status' => 'pending',
        ]);
    }

    public function test_penerima_bisa_menerima_permintaan(): void
    {
        [$pengirim, $penerima] = [$this->makeMahasiswa('Pengirim'), $this->makeMahasiswa('Penerima')];
        $permintaan = StudyRequest::create([
            'pengirim_id' => $pengirim->id,
            'penerima_id' => $penerima->id,
            'status' => 'pending',
            'waktu_kirim' => now(),
        ]);

        $response = $this->actingAs($penerima)
            ->patch(route('permintaan.accept', $permintaan));

        $response->assertSessionHas('success');
        $this->assertSame('accepted', $permintaan->fresh()->status);
    }

    public function test_penerima_bisa_menolak_permintaan(): void
    {
        [$pengirim, $penerima] = [$this->makeMahasiswa('Pengirim'), $this->makeMahasiswa('Penerima')];
        $permintaan = StudyRequest::create([
            'pengirim_id' => $pengirim->id,
            'penerima_id' => $penerima->id,
            'status' => 'pending',
            'waktu_kirim' => now(),
        ]);

        $response = $this->actingAs($penerima)
            ->patch(route('permintaan.reject', $permintaan));

        $response->assertSessionHas('success');
        $this->assertSame('rejected', $permintaan->fresh()->status);
    }

    public function test_pengirim_bisa_membatalkan_permintaan_pending(): void
    {
        [$pengirim, $penerima] = [$this->makeMahasiswa('Pengirim'), $this->makeMahasiswa('Penerima')];
        $permintaan = StudyRequest::create([
            'pengirim_id' => $pengirim->id,
            'penerima_id' => $penerima->id,
            'status' => 'pending',
            'waktu_kirim' => now(),
        ]);

        $response = $this->actingAs($pengirim)
            ->delete(route('permintaan.destroy', $permintaan));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('study_requests', ['id' => $permintaan->id]);
    }

    public function test_permintaan_yang_sudah_diproses_tidak_bisa_diterima_lagi(): void
    {
        [$pengirim, $penerima] = [$this->makeMahasiswa('Pengirim'), $this->makeMahasiswa('Penerima')];
        $permintaan = StudyRequest::create([
            'pengirim_id' => $pengirim->id,
            'penerima_id' => $penerima->id,
            'status' => 'rejected',
            'waktu_kirim' => now(),
        ]);

        $response = $this->actingAs($penerima)
            ->patch(route('permintaan.accept', $permintaan));

        $response->assertSessionHas('error');
        $this->assertSame('rejected', $permintaan->fresh()->status);
    }

    public function test_pihak_lain_tidak_bisa_memproses_permintaan(): void
    {
        [$pengirim, $penerima, $lain] = [
            $this->makeMahasiswa('Pengirim'),
            $this->makeMahasiswa('Penerima'),
            $this->makeMahasiswa('Lain'),
        ];
        $permintaan = StudyRequest::create([
            'pengirim_id' => $pengirim->id,
            'penerima_id' => $penerima->id,
            'status' => 'pending',
            'waktu_kirim' => now(),
        ]);

        $response = $this->actingAs($lain)
            ->patch(route('permintaan.accept', $permintaan));

        $response->assertForbidden();
        $this->assertSame('pending', $permintaan->fresh()->status);
    }

    public function test_duplikat_permintaan_aktif_ditolak(): void
    {
        [$pengirim, $penerima] = [$this->makeMahasiswa('Pengirim'), $this->makeMahasiswa('Penerima')];
        StudyRequest::create([
            'pengirim_id' => $pengirim->id,
            'penerima_id' => $penerima->id,
            'status' => 'pending',
            'waktu_kirim' => now(),
        ]);

        $response = $this->actingAs($pengirim)
            ->post(route('permintaan.store'), ['penerima_id' => $penerima->id]);

        $response->assertSessionHasErrors('penerima_id');
        $this->assertSame(1, StudyRequest::where('pengirim_id', $pengirim->id)->count());
    }
}
