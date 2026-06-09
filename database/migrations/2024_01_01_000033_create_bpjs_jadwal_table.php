<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpjs_jadwal', function (Blueprint $table) {
            $table->id();
            $table->string('kode_poli', 50);
            $table->string('nama_poli');
            $table->string('kode_dokter', 50);
            $table->string('nama_dokter');
            $table->tinyInteger('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('kuota');
            $table->timestamps();

            $table->index(['kode_poli', 'hari']);
            $table->index(['kode_dokter', 'hari']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpjs_jadwal');
    }
};
