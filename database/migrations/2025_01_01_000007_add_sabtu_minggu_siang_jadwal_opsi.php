<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambahkan slot "Sabtu Siang (11-14)" dan "Minggu Siang (11-14)" ke opsi_preferensi.
     * Sebelumnya Sabtu & Minggu hanya punya 3 slot (Pagi/Sore/Malam) — ditambahkan
     * slot Siang agar seluruh 7 hari konsisten punya 4 slot (Pagi/Siang/Sore/Malam).
     */
    public function up(): void
    {
        $slots = [
            ['tipe' => 'jadwal', 'nilai' => 'Sabtu Siang (11-14)'],
            ['tipe' => 'jadwal', 'nilai' => 'Minggu Siang (11-14)'],
        ];

        $now = now();
        foreach ($slots as $slot) {
            // Gunakan insertOrIgnore untuk idempoten (tidak error kalau sudah ada)
            DB::table('opsi_preferensi')->insertOrIgnore([
                'tipe' => $slot['tipe'],
                'nilai' => $slot['nilai'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('opsi_preferensi')
            ->where('tipe', 'jadwal')
            ->whereIn('nilai', ['Sabtu Siang (11-14)', 'Minggu Siang (11-14)'])
            ->delete();
    }
};
