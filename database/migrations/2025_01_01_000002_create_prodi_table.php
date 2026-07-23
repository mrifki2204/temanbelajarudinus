<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prodi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fakultas_id')->constrained('fakultas')->cascadeOnDelete();
            $table->string('nama');
            $table->string('kode')->unique();
            $table->enum('jenjang', ['D3', 'D4', 'S1']);
            $table->timestamps();

            $table->unique(['fakultas_id', 'nama', 'jenjang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prodi');
    }
};
