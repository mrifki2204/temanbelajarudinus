<?php

namespace Database\Factories;

use App\Models\Fakultas;
use App\Models\Prodi;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fakultas = Fakultas::inRandomOrder()->first();
        $prodi = $fakultas
            ? Prodi::where('fakultas_id', $fakultas->id)->inRandomOrder()->first()
            : Prodi::inRandomOrder()->first();

        return [
            'nama' => fake()->name(),
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'email' => fake()->unique()->safeEmail(),
            'nim' => null,
            'role' => 'mahasiswa',
            'status' => 'aktif',
            'fakultas_id' => $fakultas?->id,
            'prodi_id' => $prodi?->id,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
