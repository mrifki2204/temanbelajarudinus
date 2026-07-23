<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@udinus.ac.id'],
            [
                'nama' => 'Admin Udinus',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'aktif',
                'nim' => null,
                'fakultas_id' => null,
                'prodi_id' => null,
                'semester' => null,
                'angkatan' => null,
            ]
        );
    }
}
