<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opsi_preferensi', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['minat', 'tujuan', 'gaya', 'jadwal', 'mode']);
            $table->string('nilai');
            $table->timestamps();

            $table->unique(['tipe', 'nilai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opsi_preferensi');
    }
};
