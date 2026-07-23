<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Default: profil lengkap (5 atribut terisi) dengan nilai yang valid
     * untuk opsi preferensi yang umum dipakai di test.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'minat' => ['Programming'],
            'tujuan' => 'Mengerjakan Tugas',
            'gaya' => 'Diskusi',
            'jadwal' => ['Senin Pagi'],
            'mode' => 'Tatap Muka',
            'whatsapp' => '08'.fake()->numerify('##########'),
            'instagram' => '@'.fake()->userName(),
            'feature_vector' => null,
        ];
    }
}
