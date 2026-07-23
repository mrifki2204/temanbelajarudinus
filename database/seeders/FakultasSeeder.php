<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use Illuminate\Database\Seeder;

class FakultasSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'FIK', 'nama' => 'Fakultas Ilmu Komputer'],
            ['kode' => 'FEB', 'nama' => 'Fakultas Ekonomi dan Bisnis'],
            ['kode' => 'FIB', 'nama' => 'Fakultas Ilmu Budaya'],
            ['kode' => 'FKES', 'nama' => 'Fakultas Kesehatan'],
            ['kode' => 'FT', 'nama' => 'Fakultas Teknik'],
            ['kode' => 'FK', 'nama' => 'Fakultas Kedokteran'],
        ];

        foreach ($data as $item) {
            Fakultas::updateOrCreate(['kode' => $item['kode']], ['nama' => $item['nama']]);
        }
    }
}
