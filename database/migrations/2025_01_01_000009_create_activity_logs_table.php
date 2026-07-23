<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            // User yang melakukan aksi. Nullable + nullOnDelete agar log tetap ada
            // sebagai audit trail walau user-nya dihapus (mis. hapus akun sendiri).
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // Identitas aksi, mis. 'fakultas.create', 'mahasiswa.delete'
            $table->string('action', 80)->index();
            // Kalimat keterangan manusiawi, mis. "Admin menambahkan fakultas FTIK"
            $table->string('description');
            // Subjek (polymorph) opsional: model yang terkait aksi
            $table->nullableMorphs('subject');
            // Data tambahan (sebelum/sesudah, id terkait, dll.)
            $table->json('properties')->nullable();
            // Konteks request
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
