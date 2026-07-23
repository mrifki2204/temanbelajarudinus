<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi users.fakultas (string nama) → fakultas_id (FK)
 * dan users.program_studi (string nama) → prodi_id (FK).
 *
 * Mapping data by nama. User dengan nama fakultas/prodi yang tidak persis
 * cocok → id diset NULL (kolom nullable). Setelah mapping, kolom string
 * lama di-drop dan ditambahkan index pada kolom FK baru + role/status/created_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('fakultas_id')->nullable()->after('fakultas');
            $table->foreignId('prodi_id')->nullable()->after('program_studi');
        });

        // Mapping nama → id
        $fakultasMap = DB::table('fakultas')->pluck('id', 'nama');
        $prodiMap = DB::table('prodi')->pluck('id', 'nama');

        DB::table('users')->orderBy('id')->chunk(200, function ($users) use ($fakultasMap, $prodiMap) {
            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update([
                    'fakultas_id' => $fakultasMap[$user->fakultas] ?? null,
                    'prodi_id' => $prodiMap[$user->program_studi] ?? null,
                ]);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['fakultas', 'program_studi']);
            $table->foreign('fakultas_id')->references('id')->on('fakultas')->nullOnDelete();
            $table->foreign('prodi_id')->references('id')->on('prodi')->nullOnDelete();
            $table->index('fakultas_id');
            $table->index('prodi_id');
            $table->index(['role', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['fakultas_id']);
            $table->dropForeign(['prodi_id']);
            $table->dropIndex(['fakultas_id']);
            $table->dropIndex(['prodi_id']);
            $table->dropIndex(['role', 'status']);
            $table->dropIndex(['created_at']);
            $table->string('fakultas')->nullable()->after('role');
            $table->string('program_studi')->nullable()->after('fakultas');
        });

        // Reverse mapping id → nama
        $fakultasMap = DB::table('fakultas')->pluck('nama', 'id');
        $prodiMap = DB::table('prodi')->pluck('nama', 'id');

        DB::table('users')->orderBy('id')->chunk(200, function ($users) use ($fakultasMap, $prodiMap) {
            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update([
                    'fakultas' => $fakultasMap[$user->fakultas_id] ?? null,
                    'program_studi' => $prodiMap[$user->prodi_id] ?? null,
                ]);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['fakultas_id', 'prodi_id']);
        });
    }
};
