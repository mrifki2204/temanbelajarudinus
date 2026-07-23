<?php

namespace Database\Seeders;

use App\Models\OpsiPreferensi;
use Illuminate\Database\Seeder;

class OpsiPreferensiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'minat' => [
                'Coding & Programming',
                'Data & Statistik',
                'Jaringan & Cyber Security',
                'Desain & Multimedia',
                'Bisnis & Marketing',
                'Bahasa & Sastra',
                'Kesehatan & Medis',
                'Teknik & Industri',
                'Sains (MIPA)',
                'Akademik Umum',
                'Soft Skills',
            ],
            'tujuan' => [
                'Belajar UTS/UAS',
                'Ngerjain Tugas',
                'Proyek Kelompok',
                'Skripsi/TA',
                'Belajar Materi Kuliah',
                'Persiapan Magang/MSIB',
                'Persiapan Lomba',
                'Belajar Bahasa Asing',
                'Membangun Portfolio',
                'Persiapan Sertifikasi',
            ],
            'gaya' => [
                'Diskusi Bareng',
                'Belajar Sendiri',
                'Visual & Praktik',
                'Praktik Langsung',
                'Belajar Terbimbing',
                'Baca & Rangkum',
                'Problem Solving',
                'Saling Mengajar',
            ],
            'jadwal' => [
                'Senin Pagi (06-11)', 'Senin Siang (11-14)', 'Senin Sore (14-18)', 'Senin Malam (18-23)',
                'Selasa Pagi (06-11)', 'Selasa Siang (11-14)', 'Selasa Sore (14-18)', 'Selasa Malam (18-23)',
                'Rabu Pagi (06-11)', 'Rabu Siang (11-14)', 'Rabu Sore (14-18)', 'Rabu Malam (18-23)',
                'Kamis Pagi (06-11)', 'Kamis Siang (11-14)', 'Kamis Sore (14-18)', 'Kamis Malam (18-23)',
                'Jumat Pagi (06-11)', 'Jumat Siang (11-14)', 'Jumat Sore (14-18)', 'Jumat Malam (18-23)',
                'Sabtu Pagi (06-11)', 'Sabtu Siang (11-14)', 'Sabtu Sore (14-18)', 'Sabtu Malam (18-23)',
                'Minggu Pagi (06-11)', 'Minggu Siang (11-14)', 'Minggu Sore (14-18)', 'Minggu Malam (18-23)',
            ],
            'mode' => ['Online', 'Tatap Muka', 'Fleksibel'],
        ];

        foreach ($data as $tipe => $nilaiList) {
            foreach ($nilaiList as $nilai) {
                OpsiPreferensi::updateOrCreate(
                    ['tipe' => $tipe, 'nilai' => $nilai],
                    ['tipe' => $tipe, 'nilai' => $nilai]
                );
            }
        }
    }
}
