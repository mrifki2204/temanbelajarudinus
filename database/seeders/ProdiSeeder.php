<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use App\Models\Prodi;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // FIK
            ['kode' => 'A11', 'nama' => 'Teknik Informatika', 'jenjang' => 'S1', 'fakultas_kode' => 'FIK'],
            ['kode' => 'A12', 'nama' => 'Sistem Informasi', 'jenjang' => 'S1', 'fakultas_kode' => 'FIK'],
            ['kode' => 'A14', 'nama' => 'Desain Komunikasi Visual', 'jenjang' => 'S1', 'fakultas_kode' => 'FIK'],
            ['kode' => 'A15', 'nama' => 'Ilmu Komunikasi', 'jenjang' => 'S1', 'fakultas_kode' => 'FIK'],
            ['kode' => 'A22', 'nama' => 'Film dan Televisi', 'jenjang' => 'D4', 'fakultas_kode' => 'FIK'],
            ['kode' => 'A32', 'nama' => 'Animasi', 'jenjang' => 'D4', 'fakultas_kode' => 'FIK'],
            ['kode' => 'A31', 'nama' => 'Teknik Informatika', 'jenjang' => 'D3', 'fakultas_kode' => 'FIK'],
            // FEB
            ['kode' => 'A41', 'nama' => 'Akuntansi', 'jenjang' => 'S1', 'fakultas_kode' => 'FEB'],
            ['kode' => 'A42', 'nama' => 'Manajemen', 'jenjang' => 'S1', 'fakultas_kode' => 'FEB'],
            ['kode' => 'A43', 'nama' => 'Bisnis Digital', 'jenjang' => 'S1', 'fakultas_kode' => 'FEB'],
            // FIB
            ['kode' => 'B11', 'nama' => 'Bahasa Inggris', 'jenjang' => 'S1', 'fakultas_kode' => 'FIB'],
            ['kode' => 'B12', 'nama' => 'Sastra Jepang', 'jenjang' => 'S1', 'fakultas_kode' => 'FIB'],
            ['kode' => 'B21', 'nama' => 'Pengelolaan Perhotelan', 'jenjang' => 'D4', 'fakultas_kode' => 'FIB'],
            // FKES
            ['kode' => 'C11', 'nama' => 'Kesehatan Masyarakat', 'jenjang' => 'S1', 'fakultas_kode' => 'FKES'],
            ['kode' => 'C12', 'nama' => 'Kesehatan Lingkungan', 'jenjang' => 'S1', 'fakultas_kode' => 'FKES'],
            ['kode' => 'C31', 'nama' => 'Rekam Medis & Informasi Kesehatan', 'jenjang' => 'D3', 'fakultas_kode' => 'FKES'],
            ['kode' => 'C32', 'nama' => 'Manajemen Informasi Kesehatan', 'jenjang' => 'D4', 'fakultas_kode' => 'FKES'],
            // FT
            ['kode' => 'D11', 'nama' => 'Teknik Industri', 'jenjang' => 'S1', 'fakultas_kode' => 'FT'],
            ['kode' => 'D12', 'nama' => 'Teknik Elektro', 'jenjang' => 'S1', 'fakultas_kode' => 'FT'],
            ['kode' => 'D13', 'nama' => 'Teknik Biomedis', 'jenjang' => 'S1', 'fakultas_kode' => 'FT'],
            // FK
            ['kode' => 'E11', 'nama' => 'Kedokteran', 'jenjang' => 'S1', 'fakultas_kode' => 'FK'],
        ];

        foreach ($data as $item) {
            $fakultas = Fakultas::where('kode', $item['fakultas_kode'])->first();
            if (! $fakultas) {
                continue;
            }
            Prodi::updateOrCreate(
                ['kode' => $item['kode']],
                [
                    'nama' => $item['nama'],
                    'jenjang' => $item['jenjang'],
                    'fakultas_id' => $fakultas->id,
                ]
            );
        }
    }
}
