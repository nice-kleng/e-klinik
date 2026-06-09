<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpjs_patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('no_kartu', 50)->index();
            $table->string('nama')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('jk', 10)->nullable();
            $table->string('no_ktp', 30)->nullable();
            $table->text('alamat')->nullable();
            $table->string('hak_kelas')->nullable();
            $table->string('jenis_peserta')->nullable();
            $table->string('status_peserta')->nullable();
            $table->string('asuransi_kesehatan')->nullable();
            $table->string('create_date')->nullable();
            $table->string('update_date')->nullable();
            $table->timestamp('sync_at')->nullable();
            $table->timestamps();

            $table->index('no_ktp');
            $table->index('nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpjs_patients');
    }
};
